<?php
/**
 * CYN Tourism — ReceiptController
 * Receipts = Paid Invoices (no separate table needed)
 */
class ReceiptController extends Controller
{
    /**
     * List all receipts (paid invoices)
     */
    public function index(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $filters = [
            'search'    => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'method'    => $_GET['method'] ?? '',
        ];

        $where = "status = 'paid'";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (invoice_no LIKE ? OR company_name LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND payment_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND payment_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['method'])) {
            $where .= " AND payment_method = ?";
            $params[] = $filters['method'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT * FROM invoices WHERE $where ORDER BY payment_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $receipts = $stmt->fetchAll();

        // Summary
        $sumStmt = $db->query("SELECT COALESCE(SUM(paid_amount), 0) as total_paid, COUNT(*) as total_count FROM invoices WHERE status = 'paid'");
        $summary = $sumStmt->fetch();

        $this->view('receipts/index', [
            'receipts'   => $receipts,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'summary'    => $summary,
            'pageTitle'  => __('receipts') ?: 'Receipts',
            'activePage' => 'receipts',
        ]);
    }

    /**
     * View a single receipt detail
     */
    public function show(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'paid'");
        $stmt->execute([$id]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            header('Location: ' . url('receipts'));
            exit;
        }

        $this->view('receipts/show', [
            'receipt'    => $receipt,
            'pageTitle'  => (__('receipt') ?: 'Receipt') . ': ' . $receipt['invoice_no'],
            'activePage' => 'receipts',
        ]);
    }

    /**
     * Mark an invoice as paid (create a receipt)
     */
    public function markPaid(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $method = trim($_POST['payment_method'] ?? $_GET['method'] ?? 'cash');
        $amount = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : null;
        $date   = trim($_POST['payment_date'] ?? '') ?: date('Y-m-d');

        if ($id) {
            $invoice = Invoice::getById($id);
            if ($invoice && $invoice['status'] !== 'paid') {
                $updateData = [
                    'status'         => 'paid',
                    'payment_method' => $method,
                    'payment_date'   => $date,
                    'paid_amount'    => $amount ?? $invoice['total_amount'],
                ];
                Invoice::update($id, $updateData);
            }
        }

        header('Location: ' . url('receipts') . '?marked=1');
        exit;
    }

    /**
     * Edit payment details of a receipt
     */
    public function edit(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'paid'");
        $stmt->execute([$id]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            header('Location: ' . url('receipts'));
            exit;
        }

        $this->view('receipts/edit', [
            'receipt'    => $receipt,
            'pageTitle'  => (__('edit') ?: 'Edit') . ': ' . $receipt['invoice_no'],
            'activePage' => 'receipts',
        ]);
    }

    /**
     * Update payment details
     */
    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id     = (int)($_POST['id'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'cash');
        $amount = (float)($_POST['paid_amount'] ?? 0);
        $date   = trim($_POST['payment_date'] ?? '') ?: date('Y-m-d');
        $notes  = trim($_POST['notes'] ?? '');

        if ($id) {
            Invoice::update($id, [
                'payment_method' => $method,
                'payment_date'   => $date,
                'paid_amount'    => $amount,
                'notes'          => $notes,
            ]);
        }

        header('Location: ' . url('receipts/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    /**
     * Revert a receipt (mark invoice as unpaid)
     */
    public function revert(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            Invoice::update($id, [
                'status'         => 'sent',
                'payment_method' => null,
                'payment_date'   => null,
                'paid_amount'    => 0,
            ]);
        }

        header('Location: ' . url('receipts') . '?reverted=1');
        exit;
    }

    /**
     * Send receipt to partner portal by setting partner_id
     */
    public function sendToPortal(): void
    {
        $this->requireAuth();

        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        // Get the invoice
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND status = 'paid'");
        $stmt->execute([$id]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            $this->jsonResponse(['success' => false, 'message' => 'Receipt not found']);
            return;
        }

        // Find partner by company_name
        $pStmt = $db->prepare("SELECT id FROM partners WHERE company_name = ? LIMIT 1");
        $pStmt->execute([$receipt['company_name']]);
        $partner = $pStmt->fetch();

        if (!$partner) {
            $this->jsonResponse(['success' => false, 'message' => 'No matching partner found for this company']);
            return;
        }

        // Set partner_id on the invoice
        $upStmt = $db->prepare("UPDATE invoices SET partner_id = ? WHERE id = ?");
        $upStmt->execute([$partner['id'], $id]);

        $this->jsonResponse(['success' => true, 'message' => 'Receipt sent to partner portal']);
    }
}
