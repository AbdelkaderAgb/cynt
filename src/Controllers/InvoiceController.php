<?php
/**
 * CYN Tourism — InvoiceController
 * Enhanced with line-item management and PricingCalculator integration
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
            'pageTitle'  => 'Fatura Yonetimi',
            'activePage' => 'invoices',
        ]);
    }

    public function create(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $this->view('invoices/form', [
            'invoice'      => [],
            'invoiceItems' => [],
            'partners'     => Partner::getActive(),
            'isEdit'       => false,
            'pageTitle'    => 'Yeni Fatura',
            'activePage'   => 'invoices',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        require_once ROOT_PATH . '/src/Core/PricingCalculator.php';

        $id = (int)($_POST['id'] ?? 0);

        // Parse line items from JSON
        $itemsJson = $_POST['items_json'] ?? '[]';
        $lineItems = json_decode($itemsJson, true) ?: [];

        // Server-side total calculation using PricingCalculator
        $taxRate  = (float)($_POST['tax_rate'] ?? 0);
        $discount = (float)($_POST['discount'] ?? 0);

        // Build items for calculator
        $calcItems = [];
        foreach ($lineItems as $item) {
            $calcItems[] = [
                'unit_price'  => (float)($item['unit_price'] ?? 0),
                'quantity'    => max(1, (int)($item['quantity'] ?? 1)),
                'total_price' => (float)($item['total_price'] ?? 0),
                'unit_type'   => $item['unit_type'] ?? 'flat',
            ];
        }

        $totals = PricingCalculator::calculateInvoiceTotals($calcItems, $taxRate, $discount);

        $data = [
            'company_name'   => trim($_POST['company_name'] ?? ''),
            'company_id'     => (int)($_POST['company_id'] ?? 0),
            'invoice_date'   => $_POST['invoice_date'] ?? date('Y-m-d'),
            'due_date'       => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'subtotal'       => $totals['subtotal'],
            'tax_rate'       => $taxRate,
            'tax_amount'     => $totals['tax_amount'],
            'discount'       => $totals['discount'],
            'total_amount'   => $totals['total'],
            'currency'       => $_POST['currency'] ?? 'USD',
            'status'         => $_POST['status'] ?? 'draft',
            'payment_method' => $_POST['payment_method'] ?: '',
            'notes'          => trim($_POST['notes'] ?? ''),
            'terms'          => trim($_POST['terms'] ?? ''),
        ];

        if ($id) {
            Invoice::update($id, $data);
        } else {
            $id = Invoice::create($data);
        }

        // Save line items
        $this->saveLineItems($id, $lineItems);

        header('Location: ' . url('invoices') . '?saved=1');
        exit;
    }

    /**
     * Save line items for an invoice — delete old, insert new
     */
    private function saveLineItems(int $invoiceId, array $items): void
    {
        $db = Database::getInstance()->getConnection();

        // Delete existing items
        $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoiceId]);

        if (empty($items)) return;

        $stmt = $db->prepare(
            "INSERT INTO invoice_items (invoice_id, item_type, item_id, service_id, description, quantity, unit_price, total_price, unit_type)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($items as $item) {
            $stmt->execute([
                $invoiceId,
                $item['item_type'] ?? 'other',
                (int)($item['item_id'] ?? 0),
                (int)($item['service_id'] ?? 0) ?: null,
                $item['description'] ?? '',
                max(1, (int)($item['quantity'] ?? 1)),
                (float)($item['unit_price'] ?? 0),
                (float)($item['total_price'] ?? 0),
                $item['unit_type'] ?? 'flat',
            ]);
        }
    }

    /**
     * Load line items for an invoice
     */
    private function getLineItems(int $invoiceId): array
    {
        return Database::fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC",
            [$invoiceId]
        );
    }

    public function show(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $items = $this->getLineItems($id);

        $this->view('invoices/show', [
            'invoice'      => $invoice,
            'invoiceItems' => $items,
            'pageTitle'    => 'Fatura: ' . $invoice['invoice_no'],
            'activePage'   => 'invoices',
        ]);
    }

    public function edit(): void
    {
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $items = $this->getLineItems($id);

        $this->view('invoices/form', [
            'invoice'      => $invoice,
            'invoiceItems' => $items,
            'partners'     => Partner::getActive(),
            'isEdit'       => true,
            'pageTitle'    => 'Duzenle: ' . $invoice['invoice_no'],
            'activePage'   => 'invoices',
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

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            $this->jsonResponse(['success' => false, 'message' => 'Invoice not found']);
            return;
        }

        $pStmt = $db->prepare("SELECT id FROM partners WHERE company_name = ? LIMIT 1");
        $pStmt->execute([$invoice['company_name']]);
        $partner = $pStmt->fetch();

        if (!$partner) {
            $this->jsonResponse(['success' => false, 'message' => 'No matching partner found for this company']);
            return;
        }

        $upStmt = $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ?");
        $upStmt->execute([$partner['id'], $id]);

        $this->jsonResponse(['success' => true, 'message' => 'Invoice sent to partner portal']);
    }
}
