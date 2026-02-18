<?php
/**
 * CYN Tourism — MissionController
 * Manages driver/guide/vehicle assignments for tours, transfers, and hotel services.
 */
class MissionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $filters = [
            'search'  => $_GET['search'] ?? '',
            'status'  => $_GET['status'] ?? '',
            'type'    => $_GET['type'] ?? '',
            'date'    => $_GET['date'] ?? '',
        ];

        $where = "1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (m.guest_name LIKE ? OR m.pickup_location LIKE ? OR m.dropoff_location LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if (!empty($filters['status'])) {
            $where .= " AND m.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where .= " AND m.mission_type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['date'])) {
            $where .= " AND m.mission_date = ?";
            $params[] = $filters['date'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM missions m WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT m.*,
            d.first_name AS driver_first, d.last_name AS driver_last,
            g.first_name AS guide_first, g.last_name AS guide_last,
            v.plate_number, v.make AS vehicle_make, v.model AS vehicle_model
            FROM missions m
            LEFT JOIN drivers d ON m.driver_id = d.id
            LEFT JOIN tour_guides g ON m.guide_id = g.id
            LEFT JOIN vehicles v ON m.vehicle_id = v.id
            WHERE $where
            ORDER BY m.mission_date DESC, m.start_time ASC
            LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('missions/index', [
            'missions'   => $missions,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('missions') ?: 'Missions',
            'activePage' => 'missions',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Fleet.php';

        $this->view('missions/form', [
            'mission'    => null,
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'pageTitle'  => __('new_mission') ?: 'New Mission',
            'activePage' => 'missions',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM missions")->fetchColumn();

        $stmt = $db->prepare("INSERT INTO missions
            (id, mission_type, reference_id, driver_id, guide_id, vehicle_id,
             mission_date, start_time, end_time,
             pickup_location, dropoff_location, pax_count,
             guest_name, guest_passport, status, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (empty($_POST['mission_type']) || empty($_POST['mission_date'])) {
             header('Location: ' . url('missions/create') . '?error=missing_fields');
             exit;
        }

        $stmt->execute([
            $nextId,
            $_POST['mission_type'] ?? 'transfer',
            (int)($_POST['reference_id'] ?? 0),
            $this->postNullableId('driver_id'),
            $this->postNullableId('guide_id'),
            $this->postNullableId('vehicle_id'),
            $_POST['mission_date'] ?? date('Y-m-d'),
            $_POST['start_time'] ?: null,
            $_POST['end_time'] ?: null,
            trim($_POST['pickup_location'] ?? ''),
            trim($_POST['dropoff_location'] ?? ''),
            (int)($_POST['pax_count'] ?? 0),
            trim($_POST['guest_name'] ?? ''),
            trim($_POST['guest_passport'] ?? ''),
            $_POST['status'] ?? 'pending',
            trim($_POST['notes'] ?? ''),
            $_SESSION['user_id'] ?? null,
        ]);

        header('Location: ' . url('missions') . '?saved=1');
        exit;
    }

    public function show(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT m.*,
            d.first_name AS driver_first, d.last_name AS driver_last, d.phone AS driver_phone,
            g.first_name AS guide_first, g.last_name AS guide_last, g.phone AS guide_phone,
            v.plate_number, v.make AS vehicle_make, v.model AS vehicle_model, v.capacity AS vehicle_capacity
            FROM missions m
            LEFT JOIN drivers d ON m.driver_id = d.id
            LEFT JOIN tour_guides g ON m.guide_id = g.id
            LEFT JOIN vehicles v ON m.vehicle_id = v.id
            WHERE m.id = ?");
        $stmt->execute([$id]);
        $mission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mission) {
            header('Location: ' . url('missions'));
            exit;
        }

        $this->view('missions/show', [
            'm'          => $mission,
            'pageTitle'  => __('mission_detail') ?: 'Mission #' . $id,
            'activePage' => 'missions',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM missions WHERE id = ?");
        $stmt->execute([$id]);
        $mission = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mission) { header('Location: ' . url('missions')); exit; }

        $this->view('missions/form', [
            'mission'    => $mission,
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'pageTitle'  => __('edit_mission') ?: 'Edit Mission #' . $id,
            'activePage' => 'missions',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('missions')); exit; }

        $stmt = $db->prepare("UPDATE missions SET
            mission_type = ?, reference_id = ?, driver_id = ?, guide_id = ?, vehicle_id = ?,
            mission_date = ?, start_time = ?, end_time = ?,
            pickup_location = ?, dropoff_location = ?, pax_count = ?,
            guest_name = ?, guest_passport = ?, status = ?, notes = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $_POST['mission_type'] ?? 'transfer',
            (int)($_POST['reference_id'] ?? 0),
            $this->postNullableId('driver_id'),
            $this->postNullableId('guide_id'),
            $this->postNullableId('vehicle_id'),
            $_POST['mission_date'] ?? date('Y-m-d'),
            $_POST['start_time'] ?: null,
            $_POST['end_time'] ?: null,
            trim($_POST['pickup_location'] ?? ''),
            trim($_POST['dropoff_location'] ?? ''),
            (int)($_POST['pax_count'] ?? 0),
            trim($_POST['guest_name'] ?? ''),
            trim($_POST['guest_passport'] ?? ''),
            $_POST['status'] ?? 'pending',
            trim($_POST['notes'] ?? ''),
            $id,
        ]);

        header('Location: ' . url('missions/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM missions WHERE id = ?")->execute([$id]);
        }
        header('Location: ' . url('missions') . '?deleted=1');
        exit;
    }

    /**
     * Calendar page view (FullCalendar)
     */
    public function calendar(): void
    {
        $this->requireAuth();
        $this->view('missions/calendar', [
            'pageTitle'  => __('missions') ?: 'Missions' . ' — ' . (__('calendar_view') ?: 'Calendar'),
            'activePage' => 'missions',
        ]);
    }

    /**
     * API: Get missions for calendar view (JSON)
     * GET /missions/calendar-data?start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    public function calendarData(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');

        $stmt = $db->prepare("SELECT m.*,
            CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) AS driver_name,
            CONCAT(COALESCE(g.first_name,''), ' ', COALESCE(g.last_name,'')) AS guide_name,
            v.plate_number
            FROM missions m
            LEFT JOIN drivers d ON m.driver_id = d.id
            LEFT JOIN tour_guides g ON m.guide_id = g.id
            LEFT JOIN vehicles v ON m.vehicle_id = v.id
            WHERE m.mission_date BETWEEN ? AND ?
            ORDER BY m.mission_date, m.start_time");
        $stmt->execute([$start, $end]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        $colors = [
            'tour'          => '#3b82f6',
            'transfer'      => '#10b981',
            'hotel_service' => '#f59e0b',
        ];
        $statusColors = [
            'pending'     => '#94a3b8',
            'assigned'    => '#3b82f6',
            'in_progress' => '#f59e0b',
            'completed'   => '#10b981',
            'cancelled'   => '#ef4444',
        ];

        foreach ($rows as $r) {
            $title = ucfirst($r['mission_type']) . ': ' . ($r['guest_name'] ?: 'N/A');
            if (!empty(trim($r['driver_name'] ?? ''))) {
                $title .= ' | ' . trim($r['driver_name']);
            }

            $events[] = [
                'id'              => (int)$r['id'],
                'title'           => $title,
                'start'           => $r['mission_date'] . ($r['start_time'] ? 'T' . $r['start_time'] : ''),
                'end'             => $r['mission_date'] . ($r['end_time'] ? 'T' . $r['end_time'] : ''),
                'backgroundColor' => $statusColors[$r['status']] ?? ($colors[$r['mission_type']] ?? '#6b7280'),
                'borderColor'     => $statusColors[$r['status']] ?? ($colors[$r['mission_type']] ?? '#6b7280'),
                'url'             => url('missions/show') . '?id=' . $r['id'],
                'extendedProps'   => [
                    'type'      => $r['mission_type'],
                    'status'    => $r['status'],
                    'driver'    => trim($r['driver_name'] ?? ''),
                    'guide'     => trim($r['guide_name'] ?? ''),
                    'vehicle'   => $r['plate_number'] ?? '',
                    'pax'       => (int)$r['pax_count'],
                ],
            ];
        }

        $this->json($events);
    }

    /**
     * Quick-create mission from existing booking
     * POST /missions/quick-create  { type: tour|transfer|hotel_service, reference_id: int }
     */
    public function quickCreate(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $type = $_POST['mission_type'] ?? '';
        $refId = (int)($_POST['reference_id'] ?? 0);
        if (!in_array($type, ['tour', 'transfer', 'hotel_service']) || !$refId) {
            $this->json(['error' => 'Invalid type or reference'], 400);
        }

        $missionData = $this->prefillFromBooking($db, $type, $refId);

        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM missions")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO missions
            (id, mission_type, reference_id, mission_date, start_time, end_time,
             pickup_location, dropoff_location, pax_count, guest_name, guest_passport,
             driver_id, guide_id, vehicle_id, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([
            $nextId,
            $type,
            $refId,
            $missionData['date'],
            $missionData['start_time'],
            $missionData['end_time'],
            $missionData['pickup'],
            $missionData['dropoff'],
            $missionData['pax'],
            $missionData['guest_name'],
            $missionData['passport'],
            $missionData['driver_id'],
            $missionData['guide_id'],
            $missionData['vehicle_id'],
            $_SESSION['user_id'] ?? null,
        ]);

        $this->json(['success' => true, 'mission_id' => $nextId, 'redirect' => url('missions/show') . '?id=' . $nextId]);
    }

    /**
     * Pre-fill mission data from existing booking (tour, transfer, hotel voucher)
     */
    private function prefillFromBooking(PDO $db, string $type, int $refId): array
    {
        $data = [
            'date' => date('Y-m-d'), 'start_time' => null, 'end_time' => null,
            'pickup' => '', 'dropoff' => '', 'pax' => 0,
            'guest_name' => '', 'passport' => '',
            'driver_id' => null, 'guide_id' => null, 'vehicle_id' => null,
        ];

        if ($type === 'tour') {
            $s = $db->prepare("SELECT * FROM tours WHERE id = ?");
            $s->execute([$refId]);
            $t = $s->fetch(PDO::FETCH_ASSOC);
            if ($t) {
                $data['date'] = $t['tour_date'] ?? date('Y-m-d');
                $data['start_time'] = $t['start_time'] ?: null;
                $data['end_time'] = $t['end_time'] ?: null;
                $data['pickup'] = $t['pickup_location'] ?? '';
                $data['dropoff'] = $t['dropoff_location'] ?? '';
                $data['pax'] = (int)$t['total_pax'];
                $data['guest_name'] = $t['guest_name'] ?? $t['company_name'] ?? '';
                $data['passport'] = $t['passenger_passport'] ?? '';
                $data['driver_id'] = !empty($t['driver_id']) ? (int)$t['driver_id'] : null;
                $data['guide_id'] = !empty($t['guide_id']) ? (int)$t['guide_id'] : null;
                $data['vehicle_id'] = !empty($t['vehicle_id']) ? (int)$t['vehicle_id'] : null;
            }
        } elseif ($type === 'transfer') {
            $s = $db->prepare("SELECT * FROM vouchers WHERE id = ?");
            $s->execute([$refId]);
            $v = $s->fetch(PDO::FETCH_ASSOC);
            if ($v) {
                $data['date'] = $v['pickup_date'] ?? date('Y-m-d');
                $data['start_time'] = $v['pickup_time'] ?: null;
                $data['pickup'] = $v['pickup_location'] ?? '';
                $data['dropoff'] = $v['dropoff_location'] ?? '';
                $data['pax'] = (int)$v['total_pax'];
                $data['guest_name'] = $v['guest_name'] ?? $v['company_name'] ?? '';
                $data['passport'] = $v['passenger_passport'] ?? '';
                $data['driver_id'] = !empty($v['driver_id']) ? (int)$v['driver_id'] : null;
                $data['guide_id'] = !empty($v['guide_id']) ? (int)$v['guide_id'] : null;
                $data['vehicle_id'] = !empty($v['vehicle_id']) ? (int)$v['vehicle_id'] : null;
            }
        } elseif ($type === 'hotel_service') {
            $s = $db->prepare("SELECT * FROM hotel_vouchers WHERE id = ?");
            $s->execute([$refId]);
            $h = $s->fetch(PDO::FETCH_ASSOC);
            if ($h) {
                $data['date'] = $h['check_in'] ?? date('Y-m-d');
                $data['pickup'] = $h['hotel_name'] ?? '';
                $data['pax'] = (int)$h['total_pax'];
                $data['guest_name'] = $h['guest_name'] ?? '';
                $data['passport'] = $h['passenger_passport'] ?? '';
            }
        }

        return $data;
    }
}
