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
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $this->view('transfers/index', [
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'partners'   => Partner::getActive(),
            'pageTitle'  => __('new_transfer') ?: 'New Transfer',
            'activePage' => 'transfers',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $data = [
            'company_name'    => trim($_POST['company_name'] ?? ''),
            'hotel_name'      => trim($_POST['hotel_name'] ?? ''),
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
            'dropoff_location'=> trim($_POST['dropoff_location'] ?? ''),
            'pickup_date'     => $_POST['pickup_date'] ?? '',
            'pickup_time'     => $_POST['pickup_time'] ?? '',
            'return_date'     => $_POST['return_date'] ?: null,
            'return_time'     => $_POST['return_time'] ?: null,
            'transfer_type'   => $_POST['transfer_type'] ?? 'one_way',
            'total_pax'       => (int)($_POST['total_pax'] ?? 1),
            'passengers'      => trim($_POST['passengers'] ?? ''),
            'flight_number'   => trim($_POST['flight_number'] ?? ''),
            'vehicle_id'      => $_POST['vehicle_id'] ?: null,
            'driver_id'       => $_POST['driver_id'] ?: null,
            'guide_id'        => $_POST['guide_id'] ?: null,
            'price'           => (float)($_POST['price'] ?? 0),
            'currency'        => $_POST['currency'] ?? 'USD',
            'status'          => $_POST['status'] ?? 'pending',
            'notes'           => trim($_POST['notes'] ?? ''),
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
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => 'transfer',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = Invoice::getAll($filters, $page);

        $this->view('transfers/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => __('transfer_invoice') ?: 'Transfer Invoices',
            'activePage' => 'transfer-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();
        $this->view('transfers/invoice_form', [
            'pageTitle'  => 'New Transfer Invoice',
            'activePage' => 'transfer-invoice',
        ]);
    }

    public function invoiceStore(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $invoiceNo = 'TI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalPrice = (float)($_POST['total_price'] ?? 0);

        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoices")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO invoices
            (id, invoice_no, company_name, invoice_date, due_date, subtotal, total_amount, currency, status, notes, type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, 'transfer')");
        $stmt->execute([
            $nextInvId,
            $invoiceNo,
            trim($_POST['company_name'] ?? ''),
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days')),
            $totalPrice,
            $totalPrice,
            $_POST['currency'] ?? 'USD',
            'Transfer: ' . trim($_POST['starting_point'] ?? '') . ' → ' . trim($_POST['destination'] ?? ''),
        ]);

        $invoiceId = $nextInvId;

        // Insert transfer as invoice item
        $description = trim($_POST['starting_point'] ?? '') . ' → ' . trim($_POST['destination'] ?? '');
        if (!empty($_POST['hotel_name'])) {
            $description .= ' | Hotel: ' . trim($_POST['hotel_name']);
        }
        $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
        $stmtItem = $db->prepare("INSERT INTO invoice_items (id, invoice_id, description, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmtItem->execute([
            $nextItemId,
            $invoiceId,
            $description,
            (int)($_POST['total_pax'] ?? 1),
            $totalPrice,
            $totalPrice,
        ]);

        header('Location: ' . url('transfer-invoice') . '?saved=1');
        exit;
    }
}
