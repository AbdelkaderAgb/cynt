<?php
/**
 * CYN Tourism — HotelController
 */
class HotelController extends Controller
{
    public function voucher(): void
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
            $where .= " AND (voucher_no LIKE ? OR guest_name LIKE ? OR hotel_name LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = [$s, $s, $s];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM hotel_vouchers WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT * FROM hotel_vouchers WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $vouchers = $stmt->fetchAll();

        $this->view('hotels/voucher', [
            'vouchers'   => $vouchers,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('hotel_voucher') ?: 'Hotel Vouchers',
            'activePage' => 'hotel-voucher',
        ]);
    }

    public function voucherStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $adults = (int)($_POST['adults'] ?? 0);
        $children = (int)($_POST['children'] ?? 0);
        $infants = (int)($_POST['infants'] ?? 0);
        $roomCount = (int)($_POST['room_count'] ?? 1);
        $roomType = (string)($_POST['room_type'] ?? '');
        $nights = (int)($_POST['nights'] ?? 1);
        $checkIn = $_POST['check_in'] ?? date('Y-m-d');
        $checkOut = date('Y-m-d', strtotime($checkIn . " + $nights days"));

        $capacityError = $this->validateVoucherCapacity($db, $adults, $children, $roomCount, $roomType, (int)($_POST['hotel_id'] ?? 0), trim($_POST['hotel_name'] ?? ''));
        if ($capacityError !== null) {
            header('Location: ' . url('hotel-voucher') . '?error=capacity&message=' . urlencode($capacityError));
            exit;
        }

        // Build guest_name from first customer or company_name
        $customers = $_POST['customers'] ?? '[]';
        $customerList = json_decode($customers, true) ?: [];
        $guestName = '';
        if (!empty($customerList) && !empty($customerList[0]['name'])) {
            $guestName = $customerList[0]['title'] . ' ' . $customerList[0]['name'];
        }
        if (empty($guestName)) {
            $guestName = trim($_POST['company_name'] ?? 'Guest');
        }

        $passengerPassport = trim($_POST['passenger_passport'] ?? '');

        $voucherNo = 'HV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $additionalServices = $this->parseAdditionalServices($_POST['additional_services'] ?? '');

        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM hotel_vouchers")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO hotel_vouchers
            (id, voucher_no, guest_name, passenger_passport, hotel_name, company_name, address, telephone,
             room_type, room_count, board_type, transfer_type,
             check_in, check_out, nights, total_pax, adults, children, infants,
             price_per_night, total_price, currency, customers,
             special_requests, additional_services, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nextId,
            $voucherNo,
            $guestName,
            $passengerPassport,
            trim($_POST['hotel_name'] ?? ''),
            trim($_POST['company_name'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            $roomType,
            $roomCount,
            $_POST['board_type'] ?? 'bed_breakfast',
            $_POST['transfer_type'] ?? 'without',
            $checkIn,
            $checkOut,
            $nights,
            $adults + $children + $infants,
            $adults,
            $children,
            $infants,
            (float)($_POST['price_per_night'] ?? 0),
            (float)($_POST['total_price'] ?? 0),
            $_POST['currency'] ?? 'USD',
            $customers,
            trim($_POST['special_requests'] ?? ''),
            $additionalServices,
            'pending',
        ]);

        $this->saveVoucherServices($db, $nextId, $_POST['linked_services'] ?? '');
        header('Location: ' . url('hotel-voucher') . '?saved=1');
        exit;
    }

    public function voucherShow(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM hotel_vouchers WHERE id = ?");
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();
        if (!$voucher) { header('Location: ' . url('hotel-voucher')); exit; }

        $guestProgram = self::resolveGuestProgram($id);
        $linkedServices = $this->loadLinkedServicesForDisplay($db, $id);

        $this->view('hotels/voucher_show', [
            'v'             => $voucher,
            'guestProgram'  => $guestProgram,
            'linkedServices'=> $linkedServices,
            'pageTitle'     => 'Voucher: ' . $voucher['voucher_no'],
            'activePage'    => 'hotel-voucher',
        ]);
    }

    public function voucherEdit(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM hotel_vouchers WHERE id = ?");
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();
        if (!$voucher) { header('Location: ' . url('hotel-voucher')); exit; }

        $roomTypes = ['standard'=>'Standard','superior'=>'Superior','deluxe'=>'Deluxe','suite'=>'Suite','family'=>'Family','economy'=>'Economy'];
        $boardTypes = ['room_only'=>'Room Only','bed_breakfast'=>'Bed & Breakfast','half_board'=>'Half Board','full_board'=>'Full Board','all_inclusive'=>'All Inclusive'];
        $transferTypes = ['without'=>'Without Transfer','with_transfer'=>'With Transfer','airport_transfer'=>'Airport Transfer'];
        $linkedServicesForEdit = $this->loadLinkedServicesForDisplay($db, $id);

        $this->view('hotels/voucher_edit', [
            'v'                     => $voucher,
            'roomTypes'             => $roomTypes,
            'boardTypes'            => $boardTypes,
            'transferTypes'         => $transferTypes,
            'linkedServicesForEdit' => $linkedServicesForEdit,
            'pageTitle'             => 'Edit: ' . $voucher['voucher_no'],
            'activePage'            => 'hotel-voucher',
        ]);
    }

    public function voucherUpdate(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('hotel-voucher')); exit; }

        $adults = (int)($_POST['adults'] ?? 0);
        $children = (int)($_POST['children'] ?? 0);
        $infants = (int)($_POST['infants'] ?? 0);
        $roomCount = (int)($_POST['room_count'] ?? 1);
        $roomType = (string)($_POST['room_type'] ?? '');
        $nights = (int)($_POST['nights'] ?? 1);
        $checkIn = $_POST['check_in'] ?? date('Y-m-d');
        $checkOut = date('Y-m-d', strtotime($checkIn . " + $nights days"));

        $capacityError = $this->validateVoucherCapacity($db, $adults, $children, $roomCount, $roomType, (int)($_POST['hotel_id'] ?? 0), trim($_POST['hotel_name'] ?? ''));
        if ($capacityError !== null) {
            header('Location: ' . url('hotel-voucher/edit') . '?id=' . $id . '&error=capacity&message=' . urlencode($capacityError));
            exit;
        }

        $customers = $_POST['customers'] ?? '[]';
        $customerList = json_decode($customers, true) ?: [];
        $guestName = '';
        if (!empty($customerList) && !empty($customerList[0]['name'])) {
            $guestName = ($customerList[0]['title'] ?? '') . ' ' . $customerList[0]['name'];
        }
        if (empty(trim($guestName))) {
            $guestName = trim($_POST['company_name'] ?? 'Guest');
        }

        $additionalServices = $this->parseAdditionalServices($_POST['additional_services'] ?? '');
        $passengerPassport = trim($_POST['passenger_passport'] ?? '');

        $stmt = $db->prepare("UPDATE hotel_vouchers SET
            guest_name = ?, passenger_passport = ?, hotel_name = ?, company_name = ?, address = ?, telephone = ?,
            room_type = ?, room_count = ?, board_type = ?, transfer_type = ?,
            check_in = ?, check_out = ?, nights = ?, total_pax = ?, adults = ?, children = ?, infants = ?,
            price_per_night = ?, total_price = ?, currency = ?, customers = ?,
            special_requests = ?, additional_services = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $guestName,
            $passengerPassport,
            trim($_POST['hotel_name'] ?? ''),
            trim($_POST['company_name'] ?? ''),
            trim($_POST['address'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            $roomType,
            $roomCount,
            $_POST['board_type'] ?? 'bed_breakfast',
            $_POST['transfer_type'] ?? 'without',
            $checkIn,
            $checkOut,
            $nights,
            $adults + $children + $infants,
            $adults,
            $children,
            $infants,
            (float)($_POST['price_per_night'] ?? 0),
            (float)($_POST['total_price'] ?? 0),
            $_POST['currency'] ?? 'USD',
            $customers,
            trim($_POST['special_requests'] ?? ''),
            $additionalServices,
            $_POST['status'] ?? 'pending',
            $id,
        ]);

        $this->saveVoucherServices($db, $id, $_POST['linked_services'] ?? '');
        header('Location: ' . url('hotel-voucher/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    /**
     * Load linked services for a voucher as array of {type, id, label} for display/edit form.
     * @return array<int, array{type: string, id: int, label: string}>
     */
    private function loadLinkedServicesForDisplay(PDO $db, int $voucherId): array
    {
        $stmt = $db->prepare("SELECT service_type, reference_id FROM voucher_services WHERE voucher_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$voucherId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $type = $r['service_type'];
            $refId = (int)$r['reference_id'];
            $label = '';
            if ($type === 'tour') {
                $s = $db->prepare("SELECT tour_name, tour_code, tour_date, total_pax FROM tours WHERE id = ?");
                $s->execute([$refId]);
                $t = $s->fetch(PDO::FETCH_ASSOC);
                if ($t) $label = ($t['tour_name'] ?: $t['tour_code']) . ' — ' . ($t['tour_date'] ? date('d M Y', strtotime($t['tour_date'])) : '') . ' — ' . ($t['total_pax'] ?? 0) . ' pax';
            } else {
                $s = $db->prepare("SELECT voucher_no, pickup_location, dropoff_location, pickup_date, pickup_time FROM vouchers WHERE id = ?");
                $s->execute([$refId]);
                $v = $s->fetch(PDO::FETCH_ASSOC);
                if ($v) $label = ($v['voucher_no'] ?: 'Transfer') . ' — ' . ($v['pickup_location'] ?? '') . ' → ' . ($v['dropoff_location'] ?? '') . ' — ' . ($v['pickup_date'] ? date('d M Y', strtotime($v['pickup_date'])) : '') . ' ' . ($v['pickup_time'] ? date('H:i', strtotime($v['pickup_time'])) : '');
            }
            $out[] = ['type' => $type, 'id' => $refId, 'label' => $label ?: $type . ' #' . $refId];
        }
        return $out;
    }

    /**
     * Save linked services (tours/transfers) for a hotel voucher. Expects JSON array: [{"type":"tour","id":7},{"type":"transfer","id":12}]
     */
    /**
     * Resolve hotel id by name (exact match).
     */
    private function resolveHotelIdByName(PDO $db, string $name): int
    {
        $name = trim($name);
        if ($name === '') return 0;
        $stmt = $db->prepare("SELECT id FROM hotels WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : 0;
    }

    /**
     * Get room capacity for (hotel_id, room_type). Returns null if not found (skip validation).
     */
    private function getRoomCapacity(PDO $db, int $hotelId, string $roomType): ?int
    {
        $roomType = trim($roomType);
        if ($roomType === '') return null;
        if ($hotelId > 0) {
            $stmt = $db->prepare("SELECT capacity FROM hotel_rooms WHERE hotel_id = ? AND room_type = ? LIMIT 1");
            $stmt->execute([$hotelId, $roomType]);
        } else {
            $stmt = $db->prepare("SELECT capacity FROM hotel_rooms WHERE room_type = ? LIMIT 1");
            $stmt->execute([$roomType]);
        }
        $row = $stmt->fetch();
        return $row !== false ? (int)$row['capacity'] : null;
    }

    /**
     * Validate (adults + children) <= room capacity. Returns error message or null if valid.
     */
    private function validateVoucherCapacity(PDO $db, int $adults, int $children, int $roomCount, string $roomType, int $hotelIdFromPost = 0, string $hotelName = ''): ?string
    {
        $hotelId = $hotelIdFromPost > 0 ? $hotelIdFromPost : $this->resolveHotelIdByName($db, $hotelName);
        $capacity = $this->getRoomCapacity($db, $hotelId, $roomType);
        if ($capacity === null) return null;
        $maxGuests = $capacity * max(1, $roomCount);
        $occupancy = $adults + $children;
        if ($occupancy > $maxGuests) {
            return sprintf('Adults + children (%d) exceed room capacity (%d for %d room(s)).', $occupancy, $capacity, max(1, $roomCount));
        }
        return null;
    }

    private function saveVoucherServices(PDO $db, int $voucherId, string $linkedServicesJson): void
    {
        $db->prepare("DELETE FROM voucher_services WHERE voucher_id = ?")->execute([$voucherId]);
        $arr = json_decode($linkedServicesJson, true);
        if (!is_array($arr)) return;
        $sortOrder = 0;
        foreach ($arr as $item) {
            $type = $item['type'] ?? '';
            $refId = (int)($item['id'] ?? $item['reference_id'] ?? 0);
            if (($type === 'tour' || $type === 'transfer') && $refId > 0) {
                $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM voucher_services")->fetchColumn();
                $ins = $db->prepare("INSERT INTO voucher_services (id, voucher_id, service_type, reference_id, sort_order) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$nextId, $voucherId, $type, $refId, $sortOrder++]);
            }
        }
    }

    public function voucherDelete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM hotel_vouchers WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . url('hotel-voucher') . '?deleted=1');
        exit;
    }

    public function invoice(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => 'hotel',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Invoice::getAll($filters, $page);

        $this->view('hotels/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => __('hotel_invoice') ?: 'Hotel Invoices',
            'activePage' => 'hotel-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();
        $this->view('hotels/invoice_form', [
            'pageTitle'  => 'New Hotel Invoice',
            'activePage' => 'hotel-invoice',
        ]);
    }

    public function invoiceStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $invoiceNo = 'HI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $rooms = json_decode($_POST['rooms'] ?? '[]', true) ?: [];

        // Calculate totals from rooms
        $subtotal = 0;
        foreach ($rooms as $room) {
            $subtotal += (float)($room['price'] ?? 0);
        }

        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoices")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO invoices
            (id, invoice_no, company_name, invoice_date, due_date, subtotal, total_amount, currency, status, notes, type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, 'hotel')");
        $stmt->execute([
            $nextInvId,
            $invoiceNo,
            trim($_POST['company_name'] ?? ''),
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days')),
            $subtotal,
            $subtotal,
            $_POST['currency'] ?? 'USD',
            'Hotel: ' . trim($_POST['hotel_name'] ?? ''),
        ]);

        $invoiceId = $nextInvId;

        // Insert room items
        foreach ($rooms as $room) {
            $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
            $stmtItem = $db->prepare("INSERT INTO invoice_items (id, invoice_id, description, quantity, unit_price, total_price)
                VALUES (?, ?, ?, 1, ?, ?)");
            $stmtItem->execute([
                $nextItemId,
                $invoiceId,
                'Room: ' . ($room['type'] ?? 'Standard'),
                (float)($room['price'] ?? 0),
                (float)($room['price'] ?? 0),
            ]);
        }

        header('Location: ' . url('hotel-invoice') . '?saved=1');
        exit;
    }

    /**
     * API: Search existing tours and transfers for linking to hotel voucher (Guest Program).
     * GET /api/search-services?q=bosphorus
     * Returns: [{ type: 'tour', id: 7, label: '...' }, { type: 'transfer', id: 12, label: '...' }]
     */
    public function searchServicesApi(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $q = trim($_GET['q'] ?? '');
        $out = [];

        if (strlen($q) >= 2) {
            $term = '%' . $q . '%';
            $stmt = $db->prepare("SELECT id, tour_name, tour_code, tour_date, total_pax FROM tours WHERE (tour_name LIKE ? OR tour_code LIKE ? OR destination LIKE ?) AND status != 'cancelled' ORDER BY tour_date DESC LIMIT 15");
            $stmt->execute([$term, $term, $term]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $date = $row['tour_date'] ? date('d M Y', strtotime($row['tour_date'])) : '';
                $out[] = [
                    'type'  => 'tour',
                    'id'    => (int)$row['id'],
                    'label' => ($row['tour_name'] ?: $row['tour_code']) . ' — ' . $date . ' — ' . ($row['total_pax'] ?? 0) . ' pax',
                ];
            }
            $stmt = $db->prepare("SELECT id, voucher_no, pickup_location, dropoff_location, pickup_date, pickup_time FROM vouchers WHERE (voucher_no LIKE ? OR pickup_location LIKE ? OR dropoff_location LIKE ? OR hotel_name LIKE ?) AND status != 'cancelled' ORDER BY pickup_date DESC LIMIT 15");
            $stmt->execute([$term, $term, $term, $term]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $date = $row['pickup_date'] ? date('d M Y', strtotime($row['pickup_date'])) : '';
                $time = $row['pickup_time'] ? date('H:i', strtotime($row['pickup_time'])) : '';
                $out[] = [
                    'type'  => 'transfer',
                    'id'    => (int)$row['id'],
                    'label' => ($row['voucher_no'] ?: 'Transfer') . ' — ' . ($row['pickup_location'] ?? '') . ' → ' . ($row['dropoff_location'] ?? '') . ' — ' . $date . ' ' . $time,
                ];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Resolve voucher_services rows to Guest Program rows (date, time, service, pickup) for PDF/display.
     * @return array<int, array{date: string, time: string, service: string, pickup: string}>
     */
    public static function resolveGuestProgram(int $voucherId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT service_type, reference_id, sort_order FROM voucher_services WHERE voucher_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$voucherId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $program = [];
        foreach ($rows as $r) {
            if ($r['service_type'] === 'tour') {
                $s = $db->prepare("SELECT tour_name, tour_code, tour_date, start_time, pickup_location FROM tours WHERE id = ?");
                $s->execute([$r['reference_id']]);
                $t = $s->fetch(PDO::FETCH_ASSOC);
                if ($t) {
                    $program[] = [
                        'date'    => $t['tour_date'] ? date('d M Y', strtotime($t['tour_date'])) : '—',
                        'time'    => $t['start_time'] ? date('H:i', strtotime($t['start_time'])) : '—',
                        'service' => $t['tour_name'] ?: $t['tour_code'] ?: 'Tour',
                        'pickup'  => $t['pickup_location'] ?? '—',
                    ];
                }
            } else {
                $s = $db->prepare("SELECT voucher_no, pickup_date, pickup_time, pickup_location, dropoff_location FROM vouchers WHERE id = ?");
                $s->execute([$r['reference_id']]);
                $v = $s->fetch(PDO::FETCH_ASSOC);
                if ($v) {
                    $program[] = [
                        'date'    => $v['pickup_date'] ? date('d M Y', strtotime($v['pickup_date'])) : '—',
                        'time'    => $v['pickup_time'] ? date('H:i', strtotime($v['pickup_time'])) : '—',
                        'service' => 'Transfer: ' . ($v['pickup_location'] ?? '') . ' → ' . ($v['dropoff_location'] ?? ''),
                        'pickup'  => $v['pickup_location'] ?? '—',
                    ];
                }
            }
        }
        return $program;
    }

    /**
     * Parse additional services text into JSON.
     * Lines like "Tour: Bosphorus cruise" or "Transfer: Airport pickup" → [{"type":"tour","description":"..."}]
     */
    private function parseAdditionalServices(string $text): string
    {
        $text = trim($text);
        if ($text === '') return '[]';
        $out = [];
        foreach (preg_split('/\r?\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^(tour|transfer)\s*[:\-]\s*(.+)$/i', $line, $m)) {
                $out[] = ['type' => strtolower($m[1]), 'description' => trim($m[2])];
            } else {
                $out[] = ['type' => 'other', 'description' => $line];
            }
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }
}