<?php
/**
 * CYN Tourism — InvoiceController
 * Enhanced with line-item management and PricingCalculator integration
 */
class InvoiceController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
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
        $this->requireAuth();
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

        // Auto-link to partner portal: if company_id is set it IS the partner id
        $companyId = (int)($data['company_id'] ?? 0);
        if ($companyId > 0) {
            Database::execute(
                "UPDATE invoices SET partner_id = ? WHERE id = ? AND (partner_id IS NULL OR partner_id = 0)",
                [$companyId, $id]
            );
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

        // Check if new columns exist (from migration_pricing_system.sql)
        $hasNewCols = false;
        try {
            $colCheck = $db->query("SELECT service_id FROM invoice_items LIMIT 0");
            $hasNewCols = ($colCheck !== false);
        } catch (\Exception $e) {
            $hasNewCols = false;
        }

        if ($hasNewCols) {
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
        } else {
            // Fallback: use only base schema columns
            $stmt = $db->prepare(
                "INSERT INTO invoice_items (invoice_id, item_type, item_id, description, quantity, unit_price, total_price)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($items as $item) {
                $stmt->execute([
                    $invoiceId,
                    $item['item_type'] ?? 'other',
                    (int)($item['item_id'] ?? 0),
                    $item['description'] ?? '',
                    max(1, (int)($item['quantity'] ?? 1)),
                    (float)($item['unit_price'] ?? 0),
                    (float)($item['total_price'] ?? 0),
                ]);
            }
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
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        require_once ROOT_PATH . '/src/Models/CreditTransaction.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $items = $this->getLineItems($id);

        // Resolve the authoritative partner ID:
        // company_id is set at invoice creation (definitive owner).
        // partner_id may differ due to manual linking — prefer company_id.
        $partnerId = (int)($invoice['company_id'] ?? 0) ?: (int)($invoice['partner_id'] ?? 0);

        // Per-currency credit balance for Pay with Credit — use invoice's own currency
        $partnerBalance = 0.0;
        if ($partnerId > 0) {
            $invCurrency    = strtoupper($invoice['currency'] ?? 'EUR');
            $partnerBalance = CreditTransaction::getPartnerBalance($partnerId, $invCurrency);
        }

        $flash = $_SESSION['invoice_flash'] ?? null;
        unset($_SESSION['invoice_flash']);

        $this->view('invoices/show', [
            'invoice'        => $invoice,
            'invoiceItems'   => $items,
            'partnerBalance' => $partnerBalance,
            'partnerId'      => $partnerId,
            'flash'          => $flash,
            'pageTitle'      => 'Fatura: ' . $invoice['invoice_no'],
            'activePage'     => 'invoices',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);

        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        // Transfer invoices use their own dedicated edit form
        if (($invoice['type'] ?? '') === 'transfer') {
            header('Location: ' . url('transfer-invoice/edit') . '?id=' . $id);
            exit;
        }

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
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $method = $_GET['method'] ?? 'cash';
        if ($id) Invoice::markPaid($id, $method);
        header('Location: ' . url('invoices') . '?paid=1');
        exit;
    }

    /**
     * AJAX: update invoice status inline
     * POST /invoices/update-status  { id, status }
     */
    public function updateStatus(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $id     = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $allowed = ['draft', 'sent', 'paid', 'overdue', 'partial', 'cancelled'];

        if (!$id || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        try {
            Database::execute(
                "UPDATE invoices SET status = ?, updated_at = datetime('now') WHERE id = ?",
                [$status, $id]
            );
            // If marking as paid, also ensure paid_amount = total_amount
            if ($status === 'paid') {
                Database::execute(
                    "UPDATE invoices
                     SET paid_amount  = total_amount,
                         payment_date = COALESCE(payment_date, date('now')),
                         updated_at   = datetime('now')
                     WHERE id = ? AND (paid_amount IS NULL OR paid_amount < total_amount)",
                    [$id]
                );
            }
            echo json_encode(['success' => true, 'status' => $status]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function delete(): void
    {
        $this->requireAuth();
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
