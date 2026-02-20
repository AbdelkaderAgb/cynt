<?php
/**
 * CYN Tourism — TourController
 */
class TourController extends Controller
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
            $where .= " AND (tour_name LIKE ? OR tour_code LIKE ? OR destination LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = [$s, $s, $s];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM tours WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT * FROM tours WHERE $where ORDER BY tour_date DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $tours = $stmt->fetchAll();

        $this->view('tours/voucher', [
            'tours'      => $tours,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('tour_voucher') ?: 'Tour Vouchers',
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherCreate(): void
    {
        $this->requireAuth();
        $this->view('tours/form', [
            'pageTitle'  => __('new_tour_voucher') ?: 'New Tour Voucher',
            'activePage' => 'tour-voucher',
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
        $priceAdult = (float)($_POST['price_per_person'] ?? 0);   // price per 1 pax (adult)
        $priceChild = (float)($_POST['price_child'] ?? 0);        // price per 1 pax (child)
        $priceInfant = (float)($_POST['price_per_infant'] ?? 0);   // price per 1 pax (infant)
        $currency = trim($_POST['currency'] ?? 'USD');
        $totalPrice = $adults * $priceAdult + $children * $priceChild + $infants * $priceInfant;

        // Parse tour_items to get first tour info for backwards-compatible columns
        $tourItems = json_decode($_POST['tour_items'] ?? '[]', true) ?: [];
        $firstTour = $tourItems[0] ?? [];
        $tourName = $firstTour['name'] ?? trim($_POST['company_name'] ?? 'Tour');
        $tourDate = $firstTour['date'] ?? date('Y-m-d');
        $duration = $firstTour['duration'] ?? '';

        $passengerPassport = trim($_POST['passenger_passport'] ?? '');
        $guestName = trim($_POST['guest_name'] ?? '');

        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM tours")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO tours
            (id, tour_name, guest_name, passenger_passport, tour_code, description, tour_type, destination, pickup_location, dropoff_location,
             tour_date, start_time, end_time, total_pax, price_per_person, price_child, price_per_infant, total_price, currency, status,
             company_name, customer_phone, adults, children, infants, customers, tour_items)
            VALUES (?, ?, ?, ?, ?, ?, 'daily', '', '', '',
                    ?, '', '', ?, ?, ?, ?, ?, ?, 'pending',
                    ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nextId,
            $tourName,
            $guestName,
            $passengerPassport,
            'TV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            $duration,
            $tourDate,
            $adults + $children + $infants,
            $priceAdult,
            $priceChild,
            $priceInfant,
            $totalPrice,
            $currency,
            trim($_POST['company_name'] ?? ''),
            trim($_POST['customer_phone'] ?? ''),
            $adults,
            $children,
            $infants,
            $_POST['customers'] ?? '[]',
            $_POST['tour_items'] ?? '[]',
        ]);

        header('Location: ' . url('tour-voucher') . '?saved=1');
        exit;
    }

    public function voucherShow(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();
        if (!$tour) { header('Location: ' . url('tour-voucher')); exit; }

        $this->view('tours/voucher_show', [
            't'          => $tour,
            'pageTitle'  => 'Tour: ' . ($tour['tour_name'] ?: $tour['tour_code']),
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherEdit(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();
        if (!$tour) { header('Location: ' . url('tour-voucher')); exit; }

        $this->view('tours/form_edit', [
            't'          => $tour,
            'pageTitle'  => (__('edit') ?: 'Edit') . ': ' . ($tour['tour_name'] ?: $tour['tour_code']),
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherUpdate(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('tour-voucher')); exit; }

        $adults = (int)($_POST['adults'] ?? 0);
        $children = (int)($_POST['children'] ?? 0);
        $infants = (int)($_POST['infants'] ?? 0);

        $tourItems = json_decode($_POST['tour_items'] ?? '[]', true) ?: [];
        $firstTour = $tourItems[0] ?? [];
        $tourName = $firstTour['name'] ?? trim($_POST['company_name'] ?? 'Tour');
        $tourDate = $firstTour['date'] ?? date('Y-m-d');
        $duration = $firstTour['duration'] ?? '';

        $existing = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);

        $priceAdult  = strlen($_POST['price_per_person'] ?? '') > 0
            ? (float)$_POST['price_per_person']
            : (float)($existing['price_per_person'] ?? 0);
        $priceChild  = strlen($_POST['price_child'] ?? '') > 0
            ? (float)$_POST['price_child']
            : (float)($existing['price_child'] ?? 0);
        $priceInfant = strlen($_POST['price_per_infant'] ?? '') > 0
            ? (float)$_POST['price_per_infant']
            : (float)($existing['price_per_infant'] ?? 0);
        $totalPrice  = $adults * $priceAdult + $children * $priceChild + $infants * $priceInfant;

        if ($totalPrice == 0 && ($existing['total_price'] ?? 0) > 0) {
            $totalPrice = (float)$existing['total_price'];
        }
        $currency = trim($_POST['currency'] ?? 'USD');

        $passengerPassport = trim($_POST['passenger_passport'] ?? '');
        $guestName = trim($_POST['guest_name'] ?? '');

        $stmt = $db->prepare("UPDATE tours SET
            tour_name = ?, guest_name = ?, passenger_passport = ?, description = ?, tour_date = ?,
            total_pax = ?, price_per_person = ?, price_child = ?, price_per_infant = ?, total_price = ?, currency = ?,
            company_name = ?, customer_phone = ?,
            adults = ?, children = ?, infants = ?, customers = ?, tour_items = ?,
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $tourName,
            $guestName,
            $passengerPassport,
            $duration,
            $tourDate,
            $adults + $children + $infants,
            $priceAdult,
            $priceChild,
            $priceInfant,
            $totalPrice,
            $currency,
            trim($_POST['company_name'] ?? ''),
            trim($_POST['customer_phone'] ?? ''),
            $adults,
            $children,
            $infants,
            $_POST['customers'] ?? '[]',
            $_POST['tour_items'] ?? '[]',
            $_POST['status'] ?? 'pending',
            $id,
        ]);

        header('Location: ' . url('tour-voucher/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function voucherDelete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM tours WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . url('tour-voucher') . '?deleted=1');
        exit;
    }

    public function invoice(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => 'tour',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Invoice::getAll($filters, $page);

        $this->view('tours/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => __('tour_invoice') ?: 'Tour Invoices',
            'activePage' => 'tour-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();

        $prefill = [];
        $voucherId = (int)($_GET['voucher_id'] ?? 0);
        if ($voucherId > 0) {
            $t = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$voucherId]);
            if ($t) {
                $partnerPhone   = '';
                $partnerAddress = '';
                $partnerContact = '';
                if (!empty($t['company_id'])) {
                    $partner = Database::fetchOne(
                        "SELECT phone, address, contact_person FROM partners WHERE id = ?",
                        [(int)$t['company_id']]
                    );
                    if ($partner) {
                        $partnerPhone   = $partner['phone']          ?? '';
                        $partnerAddress = $partner['address']        ?? '';
                        $partnerContact = $partner['contact_person'] ?? '';
                    }
                }

                $prefill = [
                    'voucher_id'         => $voucherId,
                    'company_name'       => $t['company_name']       ?? '',
                    'company_id'         => $t['company_id']         ?? 0,
                    'currency'           => $t['currency']           ?? 'USD',
                    'guest_name'         => $t['guest_name']         ?? '',
                    'passenger_passport' => $t['passenger_passport'] ?? '',
                    'company_phone'      => $partnerPhone,
                    'company_address'    => $partnerAddress,
                    'company_contact'    => $partnerContact,
                    'tour_items'         => json_decode($t['tour_items'] ?? '[]', true) ?: [],
                ];
            }
        }

        $this->view('tours/invoice_form', [
            'prefill'    => $prefill,
            'pageTitle'  => 'New Tour Invoice',
            'activePage' => 'tour-invoice',
        ]);
    }

    public function invoiceStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $invoiceNo = 'TRI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $tours = json_decode($_POST['tours'] ?? '[]', true) ?: [];
        $totalPrice = (float)($_POST['total_price'] ?? 0);

        $guestName  = trim($_POST['guest_name'] ?? '');
        $guestPass  = trim($_POST['passenger_passport'] ?? '');
        $guestsJson = '[]';
        if ($guestName !== '') {
            $guestsJson = json_encode([['name' => $guestName, 'passport' => $guestPass]]);
        }

        $voucherIdPost = (int)($_POST['voucher_id'] ?? 0);
        $tourItemsJson = $_POST['tours'] ?? '[]';

        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoices")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO invoices
            (id, invoice_no, company_name, invoice_date, due_date, subtotal, total_amount, currency, status, notes, type,
             guests_json, tour_items_json, voucher_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, 'tour', ?, ?, ?)");
        $stmt->execute([
            $nextInvId,
            $invoiceNo,
            trim($_POST['company_name'] ?? ''),
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days')),
            $totalPrice,
            $totalPrice,
            $_POST['currency'] ?? 'USD',
            trim($_POST['notes'] ?? ''),
            $guestsJson,
            $tourItemsJson,
            $voucherIdPost,
        ]);

        $invoiceId = $nextInvId;

        // Insert tour items
        foreach ($tours as $tour) {
            $desc = ($tour['name'] ?? 'Tour');
            if (!empty($tour['date'])) $desc .= ' (' . $tour['date'] . ')';
            if (!empty($tour['duration'])) $desc .= ' - ' . $tour['duration'];

            $tAdl = (int)($tour['adults']   ?? 0);
            $tChd = (int)($tour['children'] ?? 0);
            $tInf = (int)($tour['infants']  ?? 0);
            $qty  = max(1, ($tAdl + $tChd + $tInf) ?: (int)($tour['pax'] ?? 1));
            $itemTotal = (float)($tour['total'] ?? $tour['price'] ?? 0);
            $unitPrice = $qty > 0 ? round($itemTotal / $qty, 4) : $itemTotal;

            $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
            $stmtItem = $db->prepare("INSERT INTO invoice_items (id, invoice_id, description, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmtItem->execute([
                $nextItemId,
                $invoiceId,
                $desc,
                $qty,
                $unitPrice,
                $itemTotal,
            ]);
        }

        // Bind partner_id if submitted
        if (!empty($_POST['partner_id'])) {
            try {
                $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ?")->execute([(int)$_POST['partner_id'], $invoiceId]);
                $partner = Database::fetchOne("SELECT phone, address, contact_person FROM partners WHERE id = ?", [(int)$_POST['partner_id']]);
                if ($partner) {
                    $db->prepare("UPDATE invoices SET partner_phone = ?, partner_address = ?, partner_contact = ? WHERE id = ?")
                       ->execute([$partner['phone'] ?? '', $partner['address'] ?? '', $partner['contact_person'] ?? '', $invoiceId]);
                }
            } catch (\Exception $e) {
                // column may not exist in older schema — safe to ignore
            }
        }

        header('Location: ' . url('tour-invoice') . '?saved=1');
        exit;
    }
}