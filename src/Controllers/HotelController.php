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
        $checkOut = !empty($_POST['check_out']) ? $_POST['check_out'] : date('Y-m-d', strtotime($checkIn . " + $nights days"));
        if ($checkIn && $checkOut && empty($_POST['nights'])) {
            $diff = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
            if ($diff > 0) $nights = $diff;
        }

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
        $roomsJson = $_POST['rooms_json'] ?? '';

        $hotelId = (int)($_POST['hotel_id'] ?? 0);

        $companyId = (int)($_POST['company_id'] ?? 0);

        $stmt = $db->prepare("INSERT INTO hotel_vouchers
            (id, voucher_no, guest_name, passenger_passport, hotel_name, hotel_id, company_name, company_id, address, telephone,
             room_type, room_count, board_type, transfer_type,
             check_in, check_out, nights, total_pax, adults, children, infants,
             price_per_night, total_price, currency, customers,
             special_requests, additional_services, rooms_json, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nextId,
            $voucherNo,
            $guestName,
            $passengerPassport,
            trim($_POST['hotel_name'] ?? ''),
            $hotelId,
            trim($_POST['company_name'] ?? ''),
            $companyId,
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
            $roomsJson,
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
        $checkOut = !empty($_POST['check_out']) ? $_POST['check_out'] : date('Y-m-d', strtotime($checkIn . " + $nights days"));
        if ($checkIn && $checkOut && empty($_POST['nights'])) {
            $diff = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
            if ($diff > 0) $nights = $diff;
        }

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

        $roomsJson = $_POST['rooms_json'] ?? '';

        $hotelId = (int)($_POST['hotel_id'] ?? 0);
        $companyId = (int)($_POST['company_id'] ?? 0);

        $stmt = $db->prepare("UPDATE hotel_vouchers SET
            guest_name = ?, passenger_passport = ?, hotel_name = ?, hotel_id = ?, company_name = ?, company_id = ?, address = ?, telephone = ?,
            room_type = ?, room_count = ?, board_type = ?, transfer_type = ?,
            check_in = ?, check_out = ?, nights = ?, total_pax = ?, adults = ?, children = ?, infants = ?,
            price_per_night = ?, total_price = ?, currency = ?, customers = ?,
            special_requests = ?, additional_services = ?, rooms_json = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $guestName,
            $passengerPassport,
            trim($_POST['hotel_name'] ?? ''),
            $hotelId,
            trim($_POST['company_name'] ?? ''),
            $companyId,
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
            $roomsJson,
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
            'search'    => $_GET['search']    ?? '',
            'status'    => $_GET['status']    ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
            'currency'  => $_GET['currency']  ?? '',
            'type'      => 'hotel',
        ];
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $result = Invoice::getAll($filters, $page);

        // Stats for hotel invoices
        $db = Database::getInstance()->getConnection();
        $statsRow = $db->query("
            SELECT
                COUNT(*)                                             AS total,
                SUM(CASE WHEN status='paid'    THEN 1 ELSE 0 END)  AS paid_count,
                SUM(CASE WHEN status='overdue' THEN 1 ELSE 0 END)  AS overdue_count,
                SUM(CASE WHEN status IN ('sent','draft','overdue') THEN 1 ELSE 0 END) AS outstanding
            FROM invoices WHERE type='hotel'
        ")->fetch(\PDO::FETCH_ASSOC);

        $this->view('hotels/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'stats'      => $statsRow ?: [],
            'pageTitle'  => __('hotel_invoice') ?: 'Hotel Invoices',
            'activePage' => 'hotel-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();

        $prefill = [];
        $voucherId = (int)($_GET['voucher_id'] ?? 0);
        if ($voucherId > 0) {
            $v = Database::fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$voucherId]);
            if ($v) {
                $prefill = [
                    'company_name' => $v['company_name'] ?? '',
                    'company_id'   => $v['company_id']   ?? 0,
                    'currency'     => $v['currency']      ?? 'USD',
                    'hotel_name'   => $v['hotel_name']    ?? '',
                    'check_in'     => $v['check_in']      ?? '',
                    'check_out'    => $v['check_out']     ?? '',
                    'nights'       => $v['nights']        ?? 1,
                ];
            }
        }

        $this->view('hotels/invoice_form', [
            'prefill'    => $prefill,
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

        /* ── parse multi-hotel payload ── */
        $hotelsData  = json_decode($_POST['hotels_json'] ?? '[]', true) ?: [];
        $guestsData  = json_decode($_POST['guests_json']  ?? '[]', true) ?: [];

        /* ── totals from all hotel rooms ── */
        $subtotal = 0.0;
        foreach ($hotelsData as $h) {
            $nights = max(1, (int)($h['nights'] ?? 1));
            foreach ($h['rooms'] ?? [] as $r) {
                $count      = max(1, (int)($r['count']      ?? 1));
                $adults     = (int)($r['adults']     ?? 1);
                $children   = max(0, (int)($r['children']   ?? 0));
                $basePrice  = (float)($r['price']      ?? 0);
                $childPrice = (float)($r['childPrice'] ?? 0);
                if ($adults === 0) {
                    // Child-only room: base price IS the child rate
                    $subtotal += $basePrice * $count * $nights;
                } else {
                    // Adult room ± extra-bed supplement
                    $subtotal += ($basePrice * $count + $childPrice * $children * $count) * $nights;
                }
            }
        }

        $taxRate    = (float)($_POST['tax_rate']    ?? 0);
        $discount   = (float)($_POST['discount']    ?? 0);
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $total      = round(max(0, $subtotal + $taxAmount - $discount), 2);
        $paidAmount = min($paidAmount, $total);

        /* ── invoice header notes (all hotel names) ── */
        $hotelNames = array_filter(array_map(fn($h) => trim($h['name'] ?? ''), $hotelsData));
        $hotelNote  = count($hotelNames) ? 'Hotel: ' . implode(', ', $hotelNames) : '';
        $userNotes  = trim($_POST['notes'] ?? '');
        $notes      = $hotelNote . ($userNotes !== '' ? "\n" . $userNotes : '');

        /* ── build unique invoice number ── */
        $invoiceNo   = 'HI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $invoiceDate = !empty($_POST['invoice_date']) ? $_POST['invoice_date'] : date('Y-m-d');
        $dueDate     = !empty($_POST['due_date'])     ? $_POST['due_date']     : date('Y-m-d', strtotime('+30 days'));
        $currency    = $_POST['currency'] ?? 'USD';
        $companyName = trim($_POST['company_name'] ?? '');

        $companyId = (int)($_POST['company_id'] ?? 0);

        /* ── snapshot partner contact details ── */
        $partnerContact = '';
        $partnerPhone   = '';
        $partnerEmail   = '';
        $partnerAddress = '';
        $partnerCity    = '';
        $partnerCountry = '';
        if ($companyId > 0) {
            $p = Database::fetchOne(
                "SELECT contact_person, phone, email, address, city, country FROM partners WHERE id = ?",
                [$companyId]
            );
            if ($p) {
                $partnerContact = $p['contact_person'] ?? '';
                $partnerPhone   = $p['phone']          ?? '';
                $partnerEmail   = $p['email']          ?? '';
                $partnerAddress = $p['address']        ?? '';
                $partnerCity    = $p['city']           ?? '';
                $partnerCountry = $p['country']        ?? '';
            }
        }

        /* ── insert invoice row ── */
        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id),0)+1 FROM invoices")->fetchColumn();
        $db->prepare(
            "INSERT INTO invoices
             (id, invoice_no, company_name, company_id, partner_id, invoice_date, due_date,
              subtotal, total_amount, tax_rate, tax_amount, discount, paid_amount,
              currency, status, notes, terms, payment_method, hotels_json, guests_json, type,
              partner_contact, partner_phone, partner_email, partner_address, partner_city, partner_country)
             VALUES (?,?,?,?,?, ?,?, ?,?,?,?,?, ?, ?,?,'draft',?,?,?, ?,?,?, ?,?,?,?,?,?)"
        )->execute([
            $nextInvId, $invoiceNo, $companyName, $companyId, $companyId,
            $invoiceDate, $dueDate,
            round($subtotal, 2), $total, $taxRate, $taxAmount, $discount, $paidAmount,
            $currency, 'draft', $notes,
            trim($_POST['terms'] ?? ''),
            trim($_POST['payment_method'] ?? ''),
            $_POST['hotels_json']  ?? '[]',
            $_POST['guests_json']  ?? '[]',
            'hotel',
            $partnerContact, $partnerPhone, $partnerEmail, $partnerAddress, $partnerCity, $partnerCountry,
        ]);

        $invoiceId = $nextInvId;

        /* ── insert invoice_items: one row per hotel × room-line ── */
        foreach ($hotelsData as $h) {
            $nights    = max(1, (int)($h['nights'] ?? 1));
            $hotelName = trim($h['name'] ?? 'Hotel');

            foreach ($h['rooms'] ?? [] as $r) {
                $roomType   = trim($r['roomType'] ?? $r['type'] ?? 'Standard');
                $board      = strtoupper(trim($r['board'] ?? ''));
                $count      = max(1, (int)($r['count']      ?? 1));
                $adults     = (int)($r['adults']     ?? 1);
                $children   = max(0, (int)($r['children']   ?? 0));
                $infants    = max(0, (int)($r['infants']    ?? 0));
                $basePrice  = (float)($r['price']      ?? 0);
                $childPrice = (float)($r['childPrice'] ?? 0);
                $qty        = $count * $nights;

                if ($adults === 0) {
                    // Child-only room: price is the child room rate — no extra supplement
                    $lineTotal = round($basePrice * $count * $nights, 2);
                    $paxParts  = [$children . ' CHD (room)'];
                    if ($infants > 0) $paxParts[] = $infants . ' INF';
                } else {
                    // Adult room ± extra-bed supplement per child
                    $lineTotal = round(($basePrice * $count + $childPrice * $children * $count) * $nights, 2);
                    $paxParts  = [$adults . ' ADL'];
                    if ($children > 0) {
                        $paxParts[] = $children . ' CHD' . ($childPrice > 0 ? ' extra bed' : '');
                    }
                    if ($infants > 0) $paxParts[] = $infants . ' INF';
                }
                $paxStr = implode(' + ', $paxParts);

                $desc = "{$hotelName} — {$roomType}";
                if ($board)      $desc .= " ({$board})";
                $desc .= " [{$paxStr}]";
                if ($count > 1)  $desc .= " × {$count} rooms";
                if ($nights > 1) $desc .= " × {$nights} nights";

                $db->prepare(
                    "INSERT INTO invoice_items (invoice_id, item_type, description, quantity, unit_price, total_price)
                     VALUES (?, 'other', ?, ?, ?, ?)"
                )->execute([$invoiceId, $desc, $qty, $basePrice, $lineTotal]);
            }
        }

        header('Location: ' . url('hotel-invoice/show') . '?id=' . $invoiceId);
        exit;
    }

    public function invoiceEdit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id      = (int)($_GET['id'] ?? 0);
        $invoice = Database::fetchOne("SELECT * FROM invoices WHERE id = ? AND type = 'hotel'", [$id]);
        if (!$invoice) { header('Location: ' . url('hotel-invoice')); exit; }

        $invoiceItems = Database::fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC", [$id]
        );

        $this->view('hotels/invoice_form', [
            'invoice'      => $invoice,
            'invoiceItems' => $invoiceItems,
            'isEdit'       => true,
            'pageTitle'    => 'Edit Invoice — ' . $invoice['invoice_no'],
            'activePage'   => 'hotel-invoice',
        ]);
    }

    public function invoiceUpdate(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $id = (int)($_POST['invoice_id'] ?? 0);
        if (!$id) { header('Location: ' . url('hotel-invoice')); exit; }

        /* ── parse multi-hotel payload ── */
        $hotelsData = json_decode($_POST['hotels_json'] ?? '[]', true) ?: [];

        /* ── recalculate totals from posted hotels_json ── */
        $subtotal = 0.0;
        foreach ($hotelsData as $h) {
            $nights = max(1, (int)($h['nights'] ?? 1));
            foreach ($h['rooms'] ?? [] as $r) {
                $count      = max(1, (int)($r['count']      ?? 1));
                $adults     = (int)($r['adults']     ?? 1);
                $children   = max(0, (int)($r['children']   ?? 0));
                $basePrice  = (float)($r['price']      ?? 0);
                $childPrice = (float)($r['childPrice'] ?? 0);
                if ($adults === 0) {
                    $subtotal += $basePrice * $count * $nights;
                } else {
                    $subtotal += ($basePrice * $count + $childPrice * $children * $count) * $nights;
                }
            }
        }

        $taxRate    = (float)($_POST['tax_rate']    ?? 0);
        $discount   = (float)($_POST['discount']    ?? 0);
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $total      = round(max(0, $subtotal + $taxAmount - $discount), 2);
        $paidAmount = min($paidAmount, $total);

        /* ── auto-status from paid amount ── */
        if ($paidAmount >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } else {
            $status = $_POST['status'] ?? 'draft';
        }

        /* ── hotel names for notes ── */
        $hotelNames = array_filter(array_map(fn($h) => trim($h['name'] ?? ''), $hotelsData));
        $hotelNote  = count($hotelNames) ? 'Hotel: ' . implode(', ', $hotelNames) : '';
        $userNotes  = trim($_POST['notes'] ?? '');
        $notes      = $hotelNote . ($userNotes !== '' ? "\n" . $userNotes : '');

        /* ── snapshot partner contact details ── */
        $companyId      = (int)($_POST['company_id'] ?? 0);
        $partnerContact = '';
        $partnerPhone   = '';
        $partnerEmail   = '';
        $partnerAddress = '';
        $partnerCity    = '';
        $partnerCountry = '';
        if ($companyId > 0) {
            $p = Database::fetchOne(
                "SELECT contact_person, phone, email, address, city, country FROM partners WHERE id = ?",
                [$companyId]
            );
            if ($p) {
                $partnerContact = $p['contact_person'] ?? '';
                $partnerPhone   = $p['phone']          ?? '';
                $partnerEmail   = $p['email']          ?? '';
                $partnerAddress = $p['address']        ?? '';
                $partnerCity    = $p['city']           ?? '';
                $partnerCountry = $p['country']        ?? '';
            }
        }

        /* ── update invoice row ── */
        $db->prepare(
            "UPDATE invoices SET
                company_name    = ?,
                company_id      = ?,
                partner_id      = ?,
                invoice_date    = ?,
                due_date        = ?,
                subtotal        = ?,
                tax_rate        = ?,
                tax_amount      = ?,
                discount        = ?,
                total_amount    = ?,
                paid_amount     = ?,
                status          = ?,
                currency        = ?,
                payment_method  = ?,
                notes           = ?,
                terms           = ?,
                hotels_json     = ?,
                guests_json     = ?,
                partner_contact = ?,
                partner_phone   = ?,
                partner_email   = ?,
                partner_address = ?,
                partner_city    = ?,
                partner_country = ?,
                updated_at      = datetime('now')
             WHERE id = ? AND type = 'hotel'"
        )->execute([
            trim($_POST['company_name'] ?? ''),
            $companyId,
            $companyId ?: null,
            $_POST['invoice_date'] ?? date('Y-m-d'),
            $_POST['due_date']     ?? date('Y-m-d', strtotime('+30 days')),
            round($subtotal, 2),
            $taxRate,
            $taxAmount,
            $discount,
            $total,
            $paidAmount,
            $status,
            $_POST['currency']        ?? 'USD',
            trim($_POST['payment_method'] ?? ''),
            $notes,
            trim($_POST['terms'] ?? ''),
            $_POST['hotels_json'] ?? '[]',
            $_POST['guests_json'] ?? '[]',
            $partnerContact,
            $partnerPhone,
            $partnerEmail,
            $partnerAddress,
            $partnerCity,
            $partnerCountry,
            $id,
        ]);

        /* ── rebuild invoice_items: delete old, insert new ── */
        $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);

        foreach ($hotelsData as $h) {
            $nights    = max(1, (int)($h['nights'] ?? 1));
            $hotelName = trim($h['name'] ?? 'Hotel');

            foreach ($h['rooms'] ?? [] as $r) {
                $roomType   = trim($r['roomType'] ?? $r['type'] ?? 'Standard');
                $board      = strtoupper(trim($r['board'] ?? ''));
                $count      = max(1, (int)($r['count']      ?? 1));
                $adults     = (int)($r['adults']     ?? 1);
                $children   = max(0, (int)($r['children']   ?? 0));
                $infants    = max(0, (int)($r['infants']    ?? 0));
                $basePrice  = (float)($r['price']      ?? 0);
                $childPrice = (float)($r['childPrice'] ?? 0);
                $qty        = $count * $nights;

                if ($adults === 0) {
                    // Child-only room: price IS the child room rate
                    $lineTotal = round($basePrice * $count * $nights, 2);
                    $paxParts  = [$children . ' CHD (room)'];
                    if ($infants > 0) $paxParts[] = $infants . ' INF';
                } else {
                    // Adult room ± extra-bed supplement per child
                    $lineTotal = round(($basePrice * $count + $childPrice * $children * $count) * $nights, 2);
                    $paxParts  = [$adults . ' ADL'];
                    if ($children > 0) {
                        $paxParts[] = $children . ' CHD' . ($childPrice > 0 ? ' extra bed' : '');
                    }
                    if ($infants > 0) $paxParts[] = $infants . ' INF';
                }
                $paxStr = implode(' + ', $paxParts);

                $desc = "{$hotelName} — {$roomType}";
                if ($board)      $desc .= " ({$board})";
                $desc .= " [{$paxStr}]";
                if ($count > 1)  $desc .= " × {$count} rooms";
                if ($nights > 1) $desc .= " × {$nights} nights";

                $db->prepare(
                    "INSERT INTO invoice_items (invoice_id, item_type, description, quantity, unit_price, total_price)
                     VALUES (?, 'other', ?, ?, ?, ?)"
                )->execute([$id, $desc, $qty, $basePrice, $lineTotal]);
            }
        }

        header('Location: ' . url('hotel-invoice/show') . '?id=' . $id . '&updated=1');
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