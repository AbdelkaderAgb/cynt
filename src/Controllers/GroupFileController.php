<?php
/**
 * CYN Tourism — GroupFileController
 * Manages group travel dossiers linking hotel vouchers, tours, and transfers.
 */
class GroupFileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        $where = "1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (gf.file_number LIKE ? OR gf.group_name LIKE ? OR gf.leader_name LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if (!empty($filters['status'])) {
            $where .= " AND gf.status = ?";
            $params[] = $filters['status'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM group_files gf WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT gf.*, p.company_name AS partner_name
            FROM group_files gf
            LEFT JOIN partners p ON gf.partner_id = p.id
            WHERE $where
            ORDER BY gf.arrival_date DESC
            LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('group_files/index', [
            'groups'     => $groups,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('group_files') ?: 'Group Files',
            'activePage' => 'group-files',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('group_files/form', [
            'group'      => null,
            'items'      => [],
            'partners'   => $partners,
            'pageTitle'  => __('new_group_file') ?: 'New Group File',
            'activePage' => 'group-files',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $fileNumber = 'GF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM group_files")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO group_files
            (id, file_number, group_name, partner_id, leader_name, leader_phone,
             arrival_date, departure_date, total_pax, adults, children, infants,
             status, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'planning', ?, ?)");
        $stmt->execute([
            $nextId,
            $fileNumber,
            trim($_POST['group_name'] ?? ''),
            $this->postNullableId('partner_id'),
            trim($_POST['leader_name'] ?? ''),
            trim($_POST['leader_phone'] ?? ''),
            $_POST['arrival_date'] ?: null,
            $_POST['departure_date'] ?: null,
            (int)($_POST['total_pax'] ?? 0),
            (int)($_POST['adults'] ?? 0),
            (int)($_POST['children'] ?? 0),
            (int)($_POST['infants'] ?? 0),
            trim($_POST['notes'] ?? ''),
            $_SESSION['user_id'] ?? null,
        ]);

        $this->saveItems($db, $nextId, json_decode($_POST['items_json'] ?? '[]', true) ?: []);

        header('Location: ' . url('group-files/show') . '?id=' . $nextId . '&saved=1');
        exit;
    }

    public function show(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT gf.*, p.company_name AS partner_name
            FROM group_files gf LEFT JOIN partners p ON gf.partner_id = p.id WHERE gf.id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) { header('Location: ' . url('group-files')); exit; }

        // Load linked items with details
        $items = $this->loadItemsWithDetails($db, $id);

        $this->view('group_files/show', [
            'g'          => $group,
            'items'      => $items,
            'pageTitle'  => 'Group: ' . $group['group_name'],
            'activePage' => 'group-files',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM group_files WHERE id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) { header('Location: ' . url('group-files')); exit; }

        $items = $db->prepare("SELECT * FROM group_file_items WHERE group_file_id = ? ORDER BY day_number, sort_order");
        $items->execute([$id]);
        $items = $items->fetchAll(PDO::FETCH_ASSOC);

        $partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('group_files/form', [
            'group'      => $group,
            'items'      => $items,
            'partners'   => $partners,
            'pageTitle'  => 'Edit: ' . $group['group_name'],
            'activePage' => 'group-files',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('group-files')); exit; }

        $stmt = $db->prepare("UPDATE group_files SET
            group_name = ?, partner_id = ?, leader_name = ?, leader_phone = ?,
            arrival_date = ?, departure_date = ?, total_pax = ?, adults = ?, children = ?, infants = ?,
            status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            trim($_POST['group_name'] ?? ''),
            $this->postNullableId('partner_id'),
            trim($_POST['leader_name'] ?? ''),
            trim($_POST['leader_phone'] ?? ''),
            $_POST['arrival_date'] ?: null,
            $_POST['departure_date'] ?: null,
            (int)($_POST['total_pax'] ?? 0),
            (int)($_POST['adults'] ?? 0),
            (int)($_POST['children'] ?? 0),
            (int)($_POST['infants'] ?? 0),
            $_POST['status'] ?? 'planning',
            trim($_POST['notes'] ?? ''),
            $id,
        ]);

        $this->saveItems($db, $id, json_decode($_POST['items_json'] ?? '[]', true) ?: []);

        header('Location: ' . url('group-files/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM group_file_items WHERE group_file_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM group_files WHERE id = ?")->execute([$id]);
        }
        header('Location: ' . url('group-files') . '?deleted=1');
        exit;
    }

    public function pdf(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT gf.*, p.company_name AS partner_name
            FROM group_files gf LEFT JOIN partners p ON gf.partner_id = p.id WHERE gf.id = ?");
        $stmt->execute([$id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$group) { header('Location: ' . url('group-files')); exit; }

        $items = $this->loadItemsWithDetails($db, $id);

        $this->viewStandalone('group_files/pdf', [
            'g'     => $group,
            'items' => $items,
        ]);
    }

    private function saveItems(PDO $db, int $groupId, array $items): void
    {
        $db->prepare("DELETE FROM group_file_items WHERE group_file_id = ?")->execute([$groupId]);
        $sortOrder = 0;
        foreach ($items as $item) {
            $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM group_file_items")->fetchColumn();
            $stmt = $db->prepare("INSERT INTO group_file_items
                (id, group_file_id, item_type, reference_id, day_number, sort_order, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nextItemId,
                $groupId,
                $item['item_type'] ?? 'hotel_voucher',
                (int)($item['reference_id'] ?? 0),
                (int)($item['day_number'] ?? 1),
                $sortOrder++,
                trim($item['notes'] ?? ''),
            ]);
        }
    }

    private function loadItemsWithDetails(PDO $db, int $groupId): array
    {
        $stmt = $db->prepare("SELECT * FROM group_file_items WHERE group_file_id = ? ORDER BY day_number, sort_order");
        $stmt->execute([$groupId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];

        foreach ($rows as $r) {
            $detail = ['label' => '', 'date' => '', 'status' => ''];
            $type = $r['item_type'];
            $refId = (int)$r['reference_id'];

            if ($type === 'hotel_voucher') {
                $s = $db->prepare("SELECT voucher_no, guest_name, hotel_name, check_in, check_out, nights, status FROM hotel_vouchers WHERE id = ?");
                $s->execute([$refId]);
                $h = $s->fetch(PDO::FETCH_ASSOC);
                if ($h) {
                    $detail['label'] = $h['voucher_no'] . ' — ' . $h['hotel_name'] . ' — ' . $h['guest_name'];
                    $detail['date'] = ($h['check_in'] ? date('d M', strtotime($h['check_in'])) : '') . ' → ' . ($h['check_out'] ? date('d M', strtotime($h['check_out'])) : '') . ' (' . $h['nights'] . 'N)';
                    $detail['status'] = $h['status'];
                }
            } elseif ($type === 'tour') {
                $s = $db->prepare("SELECT tour_name, tour_code, tour_date, total_pax, status FROM tours WHERE id = ?");
                $s->execute([$refId]);
                $t = $s->fetch(PDO::FETCH_ASSOC);
                if ($t) {
                    $detail['label'] = ($t['tour_name'] ?: $t['tour_code']) . ' — ' . $t['total_pax'] . ' pax';
                    $detail['date'] = $t['tour_date'] ? date('d M Y', strtotime($t['tour_date'])) : '';
                    $detail['status'] = $t['status'];
                }
            } elseif ($type === 'transfer') {
                $s = $db->prepare("SELECT voucher_no, pickup_location, dropoff_location, pickup_date, pickup_time, status FROM vouchers WHERE id = ?");
                $s->execute([$refId]);
                $v = $s->fetch(PDO::FETCH_ASSOC);
                if ($v) {
                    $detail['label'] = ($v['voucher_no'] ?: 'Transfer') . ' — ' . $v['pickup_location'] . ' → ' . $v['dropoff_location'];
                    $detail['date'] = ($v['pickup_date'] ? date('d M Y', strtotime($v['pickup_date'])) : '') . ' ' . ($v['pickup_time'] ? date('H:i', strtotime($v['pickup_time'])) : '');
                    $detail['status'] = $v['status'];
                }
            }

            $r['detail'] = $detail;
            $items[] = $r;
        }

        return $items;
    }
}
