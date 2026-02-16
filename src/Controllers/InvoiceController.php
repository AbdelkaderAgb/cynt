<?php
/**
 * CYN Tourism — InvoiceController
 */
class InvoiceController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $filters = [
            'search'    => $_GET['search'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result  = Invoice::getAll($filters, $page);
        $summary = Invoice::getSummary();

        $this->view('invoices/index', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'summary'    => $summary,
            'pageTitle'  => 'Fatura Yönetimi',
            'activePage' => 'invoices',
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $this->view('invoices/form', [
            'invoice'    => [],
            'partners'   => Partner::getActive(),
            'isEdit'     => false,
            'pageTitle'  => 'Yeni Fatura',
            'activePage' => 'invoices',
        ]);
    }

    public function store(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $data = [
            'company_name'   => trim($_POST['company_name'] ?? ''),
            'invoice_date'   => $_POST['invoice_date'] ?? date('Y-m-d'),
            'due_date'       => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'subtotal'       => (float)($_POST['subtotal'] ?? 0),
            'tax_rate'       => (float)($_POST['tax_rate'] ?? 0),
            'tax_amount'     => (float)($_POST['tax_amount'] ?? 0),
            'discount'       => (float)($_POST['discount'] ?? 0),
            'total_amount'   => (float)($_POST['total_amount'] ?? 0),
            'currency'       => $_POST['currency'] ?? 'USD',
            'status'         => $_POST['status'] ?? 'draft',
            'payment_method' => $_POST['payment_method'] ?: null,
            'notes'          => trim($_POST['notes'] ?? ''),
            'terms'          => trim($_POST['terms'] ?? ''),
        ];

        $id = ($_POST['id'] ?? 0);
        if ($id) {
            Invoice::update((int)$id, $data);
        } else {
            Invoice::create($data);
        }

        header('Location: ' . url('invoices') . '?saved=1');
        exit;
    }

    public function show(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $this->view('invoices/show', [
            'invoice'    => $invoice,
            'pageTitle'  => 'Fatura: ' . $invoice['invoice_no'],
            'activePage' => 'invoices',
        ]);
    }

    public function edit(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $this->view('invoices/form', [
            'invoice'    => $invoice,
            'partners'   => Partner::getActive(),
            'isEdit'     => true,
            'pageTitle'  => 'Düzenle: ' . $invoice['invoice_no'],
            'activePage' => 'invoices',
        ]);
    }

    public function markPaid(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $method = $_GET['method'] ?? 'cash';
        if ($id) Invoice::markPaid($id, $method);
        header('Location: ' . url('invoices') . '?paid=1');
        exit;
    }

    public function delete(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id) Invoice::delete($id);
        header('Location: ' . url('invoices') . '?deleted=1');
        exit;
    }

    /**
     * Send invoice to partner portal by setting partner_id
     */
    public function sendToPortal(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        // Get the invoice
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            $this->jsonResponse(['success' => false, 'message' => 'Invoice not found']);
            return;
        }

        // Find partner by company_name
        $pStmt = $db->prepare("SELECT id FROM partners WHERE company_name = ? LIMIT 1");
        $pStmt->execute([$invoice['company_name']]);
        $partner = $pStmt->fetch();

        if (!$partner) {
            $this->jsonResponse(['success' => false, 'message' => 'No matching partner found for this company']);
            return;
        }

        // Set partner_id on the invoice
        $upStmt = $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ?");
        $upStmt->execute([$partner['id'], $id]);

        $this->jsonResponse(['success' => true, 'message' => 'Invoice sent to partner portal']);
    }
}
