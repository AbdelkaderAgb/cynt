<?php
/**
 * CYN Tourism — TransferController
 */
class TransferController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ["v.type = 'transfer' OR v.type IS NULL OR 1=1"];
        $params = [];

        // Fetch transfer vouchers (from vouchers table)
        $search = $filters['search'];
        $status = $filters['status'];
        $sqlWhere = '1=1';
        if ($search) { $sqlWhere .= " AND (voucher_no LIKE ? OR company_name LIKE ? OR pickup_location LIKE ? OR dropoff_location LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]); }
        if ($status) { $sqlWhere .= " AND status = ?"; $params[] = $status; }

        $total = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM vouchers WHERE $sqlWhere", $params)['c'] ?? 0);
        $vouchers = Database::fetchAll("SELECT * FROM vouchers WHERE $sqlWhere ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

        $this->view('transfers/index', [
            'vouchers'   => $vouchers,
            'total'      => $total,
            'page'       => $page,
            'pages'      => max(1, ceil($total / $perPage)),
            'filters'    => $filters,
            'pageTitle'  => 'Transfer Vouchers',
            'activePage' => 'transfers',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Fleet.php';

        $this->view('transfers/form', [
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'pageTitle'  => __('new_transfer') ?: 'New Transfer',
            'activePage' => 'transfers',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $transferType = $_POST['transfer_type'] ?? 'one_way';

        // Parse extra stops — if any exist, type becomes multi_stop automatically
        $stopsJson = null;
        $pickupLocation  = trim($_POST['pickup_location'] ?? '');
        $dropoffLocation = trim($_POST['dropoff_location'] ?? '');
        $pickupDate = $_POST['pickup_date'] ?? '';
        $pickupTime = $_POST['pickup_time'] ?? '';

        $rawStops = json_decode($_POST['stops_json'] ?? '[]', true);
        $extraStops = array_values(array_filter((array)$rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));
        if (!empty($extraStops)) {
            $transferType = 'multi_stop';
            $stopsJson = json_encode($extraStops);
        }

        $data = [
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'company_id'         => (int)($_POST['company_id'] ?? 0) ?: null,
            'guest_name'         => trim($_POST['guest_name'] ?? ''),
            'passenger_passport' => trim($_POST['passenger_passport'] ?? ''),
            'hotel_name'         => '',
            'pickup_location'    => $pickupLocation,
            'dropoff_location'   => $dropoffLocation,
            'pickup_date'        => $pickupDate,
            'pickup_time'        => $pickupTime,
            'return_date'        => $_POST['return_date'] ?: null,
            'return_time'        => $_POST['return_time'] ?: null,
            'transfer_type'      => $transferType,
            'stops_json'         => $stopsJson,
            'total_pax'          => (int)($_POST['total_pax'] ?? 1),
            'passengers'         => trim($_POST['passengers'] ?? ''),
            'flight_number'      => trim($_POST['flight_number'] ?? ''),
            'vehicle_id'         => $_POST['vehicle_id'] ?: null,
            'driver_id'          => $_POST['driver_id'] ?: null,
            'guide_id'           => $_POST['guide_id'] ?: null,
            'price'              => 0,
            'currency'           => $_POST['currency'] ?? 'USD',
            'status'             => 'pending',
            'notes'              => trim($_POST['notes'] ?? ''),
        ];

        Voucher::create($data);

        header('Location: ' . url('vouchers') . '?saved=1');
        exit;
    }

    public function invoice(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $filters = [
            'search'    => trim($_GET['search']    ?? ''),
            'status'    => $_GET['status']         ?? '',
            'date_from' => $_GET['date_from']       ?? '',
            'date_to'   => $_GET['date_to']         ?? '',
            'currency'  => $_GET['currency']        ?? '',
            'type'      => 'transfer',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = Invoice::getAll($filters, $page);

        /* ── Quick stats (always all transfers, ignore filters) ── */
        $stats = Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('draft','sent','pending') THEN 1 ELSE 0 END) as outstanding,
                SUM(CASE WHEN status = 'paid'     THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = 'overdue'  THEN 1 ELSE 0 END) as overdue_count,
                SUM(total_amount) as revenue,
                SUM(paid_amount)  as collected,
                SUM(total_amount - paid_amount) as balance
             FROM invoices WHERE type = 'transfer'"
        ) ?: [];

        $this->view('transfers/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'stats'      => $stats,
            'pageTitle'  => __('transfer_invoice') ?: 'Transfer Invoices',
            'activePage' => 'transfer-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();

        $prefillInvoice = [];
        $prefillMainLeg = [];
        $prefillStops   = [];
        $prefillGuests  = [];

        $voucherId = (int)($_GET['voucher_id'] ?? 0);
        if ($voucherId > 0) {
            $v = Database::fetchOne("SELECT * FROM vouchers WHERE id = ?", [$voucherId]);
            if ($v) {
                $prefillInvoice = [
                    'company_name' => $v['company_name'] ?? '',
                    'company_id'   => $v['company_id']   ?? '',
                    'currency'     => $v['currency']     ?? 'USD',
                ];
                $prefillMainLeg = [
                    'from'       => $v['pickup_location']  ?? '',
                    'to'         => $v['dropoff_location'] ?? '',
                    'date'       => $v['pickup_date']      ?? '',
                    'time'       => $v['pickup_time']      ?? '',
                    'type'       => $v['transfer_type']    ?? 'one_way',
                    'flight'     => $v['flight_number']    ?? '',
                    'returnDate' => $v['return_date']      ?? '',
                    'returnTime' => $v['return_time']      ?? '',
                    'price'      => 0,
                ];
                if (!empty($v['passengers'])) {
                    $raw = json_decode($v['passengers'], true);
                    if (is_array($raw)) $prefillGuests = $raw;
                }
                if (!empty($v['stops_json'])) {
                    $rawStops = json_decode($v['stops_json'], true) ?: [];
                    foreach ($rawStops as $s) {
                        $prefillStops[] = [
                            'from'  => $s['from'] ?? '',
                            'to'    => $s['to']   ?? '',
                            'date'  => $s['date'] ?? '',
                            'time'  => $s['time'] ?? '',
                            'type'  => $s['type'] ?? 'one_way',
                            'price' => 0,
                        ];
                    }
                }
            }
        }

        $this->view('transfers/invoice_form', [
            'invoice'       => $prefillInvoice,
            'mainLeg'       => $prefillMainLeg,
            'invoiceStops'  => $prefillStops,
            'invoiceGuests' => $prefillGuests,
            'isEdit'        => false,
            'pageTitle'     => 'New Transfer Invoice',
            'activePage'    => 'transfer-invoice',
        ]);
    }

    public function invoiceStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $invoiceNo  = 'TI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $taxRate    = (float)($_POST['tax_rate']    ?? 0);
        $discount   = (float)($_POST['discount']    ?? 0);
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);

        // Per-leg prices
        $mainPrice  = (float)($_POST['main_price'] ?? $_POST['total_price'] ?? 0);

        // Parse extra stops (each carries its own price)
        $rawStops   = json_decode($_POST['stops_json'] ?? '[]', true) ?: [];
        $extraStops = array_values(array_filter($rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));

        // Compute subtotal from all legs
        $subtotal   = $mainPrice;
        foreach ($extraStops as $stop) {
            $subtotal += (float)($stop['price'] ?? 0);
        }
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $grandTotal = round($subtotal + $taxAmount - $discount, 2);

        $pickupLoc   = trim($_POST['pickup_location']  ?? $_POST['starting_point'] ?? '');
        $dropoffLoc  = trim($_POST['dropoff_location'] ?? $_POST['destination']    ?? '');
        $description = $pickupLoc . ' → ' . $dropoffLoc;

        // Parse guests JSON
        $rawGuests = json_decode($_POST['guests_json'] ?? '[]', true) ?: [];
        $guests    = array_values(array_filter($rawGuests, fn($g) => !empty($g['name'])));

        $invoiceDate  = $_POST['invoice_date'] ?? date('Y-m-d');
        $dueDate      = date('Y-m-d', strtotime($invoiceDate . ' +30 days'));
        $transferType = $_POST['transfer_type'] ?? 'one_way';

        // Build main leg JSON for edit reconstruction
        $mainLeg = [
            'from'       => $pickupLoc,
            'to'         => $dropoffLoc,
            'date'       => $_POST['pickup_date']    ?? '',
            'time'       => $_POST['pickup_time']    ?? '',
            'type'       => $transferType,
            'flight'     => trim($_POST['flight_number'] ?? ''),
            'returnDate' => $_POST['return_date']    ?? '',
            'returnTime' => $_POST['return_time']    ?? '',
            'price'      => $mainPrice,
        ];

        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoices")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO invoices
            (id, invoice_no, company_name, company_id, invoice_date, due_date,
             subtotal, tax_rate, tax_amount, discount, total_amount, paid_amount,
             currency, payment_method, status, notes, terms, type,
             stops_json, guests_json, main_leg_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?, 'transfer', ?, ?, ?)");
        $stmt->execute([
            $nextInvId,
            $invoiceNo,
            trim($_POST['company_name'] ?? ''),
            (int)($_POST['company_id'] ?? 0) ?: null,
            $invoiceDate,
            $dueDate,
            $subtotal,
            $taxRate,
            $taxAmount,
            $discount,
            $grandTotal,
            $paidAmount,
            $_POST['currency']       ?? 'USD',
            $_POST['payment_method'] ?? 'cash',
            trim($_POST['notes'] ?? ''),
            trim($_POST['terms'] ?? ''),
            $_POST['stops_json']  ?? '[]',
            $_POST['guests_json'] ?? '[]',
            json_encode($mainLeg),
        ]);

        $invoiceId = $nextInvId;

        // Auto-link to partner portal
        $companyId = (int)($_POST['company_id'] ?? 0);
        if ($companyId > 0) {
            $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ?")->execute([$companyId, $invoiceId]);
        }

        // Build legs: main leg first, then extra stops
        $typeLabel = $transferType === 'round_trip' ? 'Round Trip' : 'One Way';
        $dateStr   = !empty($_POST['pickup_date']) ? date('d M Y', strtotime($_POST['pickup_date'])) : '';
        $timeStr   = trim($_POST['pickup_time']   ?? '');
        $flightStr = trim($_POST['flight_number'] ?? '');

        $mainDesc = $description;
        if ($dateStr)   $mainDesc .= ' · ' . $dateStr;
        if ($timeStr)   $mainDesc .= ' ' . $timeStr;
        if ($typeLabel) $mainDesc .= ' (' . $typeLabel . ')';
        if ($flightStr) $mainDesc .= ' · Flight: ' . $flightStr;

        $stmtItem = $db->prepare("INSERT INTO invoice_items
            (id, invoice_id, description, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)");

        // Main leg — priced at mainPrice
        $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
        $stmtItem->execute([$nextItemId, $invoiceId, $mainDesc, 1, $mainPrice, $mainPrice]);

        // Extra stops — each billed at its own price
        foreach ($extraStops as $stop) {
            $stopDesc      = trim($stop['from'] ?? '') . ' → ' . trim($stop['to'] ?? '');
            if (!empty($stop['date']))  $stopDesc .= ' · ' . date('d M Y', strtotime($stop['date']));
            if (!empty($stop['time']))  $stopDesc .= ' ' . $stop['time'];
            $stopTypeLabel = ($stop['type'] ?? 'one_way') === 'round_trip' ? 'Round Trip' : 'One Way';
            $stopDesc     .= ' (' . $stopTypeLabel . ')';
            $stopPrice     = (float)($stop['price'] ?? 0);
            $nextItemId    = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
            $stmtItem->execute([$nextItemId, $invoiceId, $stopDesc, 1, $stopPrice, $stopPrice]);
        }

        header('Location: ' . url('invoices/show') . '?id=' . $invoiceId);
        exit;
    }

    public function edit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        require_once ROOT_PATH . '/src/Models/Fleet.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);
        if (!$voucher) { header('Location: ' . url('vouchers')); exit; }

        $this->view('transfers/form_edit', [
            'v'          => $voucher,
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'pageTitle'  => 'Edit Transfer — ' . $voucher['voucher_no'],
            'activePage' => 'transfers',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('vouchers')); exit; }

        $transferType = $_POST['transfer_type'] ?? 'one_way';

        $stopsJson = null;
        $pickupLocation  = trim($_POST['pickup_location'] ?? '');
        $dropoffLocation = trim($_POST['dropoff_location'] ?? '');
        $pickupDate = $_POST['pickup_date'] ?? '';
        $pickupTime = $_POST['pickup_time'] ?? '';

        $rawStops = json_decode($_POST['stops_json'] ?? '[]', true);
        $extraStops = array_values(array_filter((array)$rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));
        if (!empty($extraStops)) {
            $transferType = 'multi_stop';
            $stopsJson = json_encode($extraStops);
        }

        $data = [
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'company_id'         => (int)($_POST['company_id'] ?? 0) ?: null,
            'guest_name'         => trim($_POST['guest_name'] ?? ''),
            'passenger_passport' => trim($_POST['passenger_passport'] ?? ''),
            'pickup_location'    => $pickupLocation,
            'dropoff_location'   => $dropoffLocation,
            'pickup_date'        => $pickupDate,
            'pickup_time'        => $pickupTime,
            'return_date'        => $_POST['return_date'] ?: null,
            'return_time'        => $_POST['return_time'] ?: null,
            'transfer_type'      => $transferType,
            'stops_json'         => $stopsJson,
            'total_pax'          => (int)($_POST['total_pax'] ?? 1),
            'passengers'         => trim($_POST['passengers'] ?? ''),
            'flight_number'      => trim($_POST['flight_number'] ?? ''),
            'vehicle_id'         => $_POST['vehicle_id'] ?: null,
            'driver_id'          => $_POST['driver_id'] ?: null,
            'guide_id'           => $_POST['guide_id'] ?: null,
            'currency'           => $_POST['currency'] ?? 'USD',
            'status'             => $_POST['status'] ?? 'pending',
            'notes'              => trim($_POST['notes'] ?? ''),
        ];

        Voucher::update($id, $data);

        header('Location: ' . url('transfers/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function show(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);
        if (!$voucher) { header('Location: ' . url('vouchers')); exit; }

        $partner = null;
        if (!empty($voucher['company_id'])) {
            $partner = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE id = ? LIMIT 1", [(int)$voucher['company_id']]);
        }
        if (!$partner && !empty($voucher['company_name'])) {
            $partner = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE company_name = ? LIMIT 1", [$voucher['company_name']]);
        }
        if ($partner) {
            $voucher['partner_phone']   = $partner['phone'] ?? '';
            $voucher['partner_address'] = implode(', ', array_filter([$partner['address'] ?? '', $partner['city'] ?? '', $partner['country'] ?? '']));
        }

        // Resolve driver, vehicle, guide names
        if (!empty($voucher['driver_id'])) {
            $d = Database::fetchOne("SELECT first_name, last_name FROM drivers WHERE id = ?", [$voucher['driver_id']]);
            if ($d) $voucher['driver_name'] = trim($d['first_name'] . ' ' . $d['last_name']);
        }
        if (!empty($voucher['vehicle_id'])) {
            $vh = Database::fetchOne("SELECT plate_number, make, model FROM vehicles WHERE id = ?", [$voucher['vehicle_id']]);
            if ($vh) $voucher['vehicle_plate'] = trim($vh['plate_number'] . ' — ' . $vh['make'] . ' ' . $vh['model']);
        }
        if (!empty($voucher['guide_id'])) {
            $g = Database::fetchOne("SELECT first_name, last_name FROM tour_guides WHERE id = ?", [$voucher['guide_id']]);
            if ($g) $voucher['guide_name'] = trim($g['first_name'] . ' ' . $g['last_name']);
        }

        $this->view('transfers/show', [
            'v'          => $voucher,
            'pageTitle'  => 'Transfer: ' . $voucher['voucher_no'],
            'activePage' => 'transfers',
        ]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id) Voucher::delete($id);
        header('Location: ' . url('vouchers') . '?deleted=1');
        exit;
    }

    public function pdf(): void
    {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: ' . url('vouchers')); exit; }
        // Consolidate to single canonical PDF template via ExportController
        header('Location: ' . url('vouchers/pdf') . '?id=' . $id);
        exit;
    }

    /* ─────────────────────────────────────────────────────────────
     *  Transfer Invoice — Edit (show pre-populated form)
     * ───────────────────────────────────────────────────────────── */
    public function invoiceEdit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id      = (int)($_GET['id'] ?? 0);
        $invoice = Database::fetchOne("SELECT * FROM invoices WHERE id = ? AND type = 'transfer'", [$id]);
        if (!$invoice) { header('Location: ' . url('transfer-invoice')); exit; }

        $invoiceItems = Database::fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC", [$id]
        );

        $mainLeg = json_decode($invoice['main_leg_json'] ?? '{}', true) ?: [];
        $stops   = json_decode($invoice['stops_json']   ?? '[]', true) ?: [];
        $guests  = json_decode($invoice['guests_json']  ?? '[]', true) ?: [];

        $currencies = ['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'];
        $payMethods = ['cash'=>'Cash','bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','check'=>'Check','other'=>'Other'];

        $this->view('transfers/invoice_form', [
            'invoice'      => $invoice,
            'invoiceItems' => $invoiceItems,
            'mainLeg'      => $mainLeg,
            'invoiceStops' => $stops,
            'invoiceGuests'=> $guests,
            'currencies'   => $currencies,
            'payMethods'   => $payMethods,
            'isEdit'       => true,
            'pageTitle'    => 'Edit Invoice — ' . $invoice['invoice_no'],
            'activePage'   => 'transfer-invoice',
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     *  Transfer Invoice — Update (save edits)
     * ───────────────────────────────────────────────────────────── */
    public function invoiceUpdate(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $id = (int)($_POST['invoice_id'] ?? 0);
        if (!$id) { header('Location: ' . url('transfer-invoice')); exit; }

        $taxRate    = (float)($_POST['tax_rate']    ?? 0);
        $discount   = (float)($_POST['discount']    ?? 0);
        $paidAmount = (float)($_POST['paid_amount'] ?? 0);
        $mainPrice  = (float)($_POST['main_price']  ?? $_POST['total_price'] ?? 0);

        $rawStops   = json_decode($_POST['stops_json'] ?? '[]', true) ?: [];
        $extraStops = array_values(array_filter($rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));

        $subtotal  = $mainPrice;
        foreach ($extraStops as $stop) {
            $subtotal += (float)($stop['price'] ?? 0);
        }
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $grandTotal = round($subtotal + $taxAmount - $discount, 2);

        $pickupLoc    = trim($_POST['pickup_location']  ?? '');
        $dropoffLoc   = trim($_POST['dropoff_location'] ?? '');
        $transferType = $_POST['transfer_type'] ?? 'one_way';

        $mainLeg = [
            'from'       => $pickupLoc,
            'to'         => $dropoffLoc,
            'date'       => $_POST['pickup_date']    ?? '',
            'time'       => $_POST['pickup_time']    ?? '',
            'type'       => $transferType,
            'flight'     => trim($_POST['flight_number'] ?? ''),
            'returnDate' => $_POST['return_date']    ?? '',
            'returnTime' => $_POST['return_time']    ?? '',
            'price'      => $mainPrice,
        ];

        $rawGuests = json_decode($_POST['guests_json'] ?? '[]', true) ?: [];
        $guests    = array_values(array_filter($rawGuests, fn($g) => !empty($g['name'])));

        // Update invoice row
        $upStmt = $db->prepare("UPDATE invoices SET
            company_name   = ?, company_id   = ?, invoice_date  = ?,
            subtotal       = ?, tax_rate      = ?, tax_amount    = ?,
            discount       = ?, total_amount  = ?, paid_amount   = ?,
            currency       = ?, payment_method= ?, notes         = ?,
            terms          = ?, stops_json    = ?, guests_json   = ?,
            main_leg_json  = ?, updated_at    = ?
            WHERE id = ?");

        $upStmt->execute([
            trim($_POST['company_name'] ?? ''),
            (int)($_POST['company_id'] ?? 0) ?: null,
            $_POST['invoice_date'] ?? date('Y-m-d'),
            $subtotal,
            $taxRate,
            $taxAmount,
            $discount,
            $grandTotal,
            $paidAmount,
            $_POST['currency']       ?? 'USD',
            $_POST['payment_method'] ?? 'cash',
            trim($_POST['notes'] ?? ''),
            trim($_POST['terms'] ?? ''),
            $_POST['stops_json']  ?? '[]',
            json_encode(array_values($guests)),
            json_encode($mainLeg),
            date('Y-m-d H:i:s'),
            $id,
        ]);

        // Auto-link to partner portal on update too
        $companyId = (int)($_POST['company_id'] ?? 0);
        if ($companyId > 0) {
            $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ? AND (partner_id IS NULL OR partner_id = 0)")
               ->execute([$companyId, $id]);
        }

        // Rebuild invoice items
        $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);

        $typeLabel  = $transferType === 'round_trip' ? 'Round Trip' : 'One Way';
        $dateStr    = !empty($_POST['pickup_date'])   ? date('d M Y', strtotime($_POST['pickup_date'])) : '';
        $timeStr    = trim($_POST['pickup_time']   ?? '');
        $flightStr  = trim($_POST['flight_number'] ?? '');

        $mainDesc = $pickupLoc . ' → ' . $dropoffLoc;
        if ($dateStr)   $mainDesc .= ' · ' . $dateStr;
        if ($timeStr)   $mainDesc .= ' ' . $timeStr;
        if ($typeLabel) $mainDesc .= ' (' . $typeLabel . ')';
        if ($flightStr) $mainDesc .= ' · Flight: ' . $flightStr;

        $stmtItem = $db->prepare("INSERT INTO invoice_items
            (id, invoice_id, description, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)");

        $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
        $stmtItem->execute([$nextItemId, $id, $mainDesc, 1, $mainPrice, $mainPrice]);

        foreach ($extraStops as $stop) {
            $stopDesc      = trim($stop['from'] ?? '') . ' → ' . trim($stop['to'] ?? '');
            if (!empty($stop['date']))  $stopDesc .= ' · ' . date('d M Y', strtotime($stop['date']));
            if (!empty($stop['time']))  $stopDesc .= ' ' . $stop['time'];
            $stopTypeLabel = ($stop['type'] ?? 'one_way') === 'round_trip' ? 'Round Trip' : 'One Way';
            $stopDesc     .= ' (' . $stopTypeLabel . ')';
            $stopPrice     = (float)($stop['price'] ?? 0);
            $nextItemId    = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
            $stmtItem->execute([$nextItemId, $id, $stopDesc, 1, $stopPrice, $stopPrice]);
        }

        header('Location: ' . url('invoices/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');
        $data    = json_decode(file_get_contents('php://input'), true) ?? [];
        $id      = (int)($data['id'] ?? $_POST['id'] ?? 0);
        $status  = trim($data['status'] ?? $_POST['status'] ?? '');
        $allowed = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        if (!$id || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $db->prepare("UPDATE vouchers SET status = ?, updated_at = datetime('now') WHERE id = ?")
           ->execute([$status, $id]);

        echo json_encode(['success' => true, 'status' => $status]);
        exit;
    }
}
