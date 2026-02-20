<?php
/**
 * CYN Tourism — CreditNoteController
 * CRUD for credit notes linked to invoices/partners.
 * Uses existing credit_notes table.
 */
class CreditNoteController extends Controller
{
    /**
     * List credit notes
     * GET /credit-notes
     */
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (cn.credit_note_no LIKE ? OR p.company_name LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
        }
        if (!empty($filters['status'])) {
            $where .= " AND cn.status = ?";
            $params[] = $filters['status'];
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $countStmt = $db->prepare("SELECT COUNT(*) FROM credit_notes cn WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT cn.*, cn.company_name as partner_name, i.invoice_no 
             FROM credit_notes cn 
             LEFT JOIN invoices i ON cn.invoice_id = i.id
             WHERE $where
             ORDER BY cn.created_at DESC
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $notes = $stmt->fetchAll();

        // Summary
        $sumStmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total_amount, COUNT(*) as total_count FROM credit_notes");
        $summary = $sumStmt->fetch();

        $this->view('invoices/credit_notes', [
            'notes'     => $notes,
            'total'     => $total,
            'page'      => $page,
            'pages'     => $pages,
            'filters'   => $filters,
            'summary'   => $summary,
            'pageTitle' => 'Credit Notes',
            'activePage'=> 'invoices',
        ]);
    }

    /**
     * Create/edit form
     * GET /credit-notes/create?invoice_id=X (optional)
     */
    public function create(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $invoiceId = (int)($_GET['invoice_id'] ?? 0);
        $id        = (int)($_GET['id'] ?? 0);
        $note      = [];

        if ($id) {
            $stmt = $db->prepare("SELECT * FROM credit_notes WHERE id = ?");
            $stmt->execute([$id]);
            $note = $stmt->fetch() ?: [];
        }

        // Get partners and invoices for dropdowns
        require_once ROOT_PATH . '/src/Models/Partner.php';
        $partners = Partner::getActive();
        $invoices = $db->query("SELECT id, invoice_no, company_name, total_amount, currency FROM invoices ORDER BY created_at DESC LIMIT 200")->fetchAll();

        $this->view('invoices/credit_note_form', [
            'note'       => $note,
            'invoiceId'  => $invoiceId ?: ($note['invoice_id'] ?? 0),
            'partners'   => $partners,
            'invoices'   => $invoices,
            'isEdit'     => !empty($note),
            'pageTitle'  => empty($note) ? 'New Credit Note' : 'Edit Credit Note',
            'activePage' => 'invoices',
        ]);
    }

    /**
     * Store / update credit note
     * POST /credit-notes/store
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id        = (int)($_POST['id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0) ?: null;
        $companyName = trim($_POST['company_name'] ?? '');
        $amount    = (float)($_POST['amount'] ?? 0);
        $currency  = $_POST['currency'] ?? 'USD';
        $reason    = trim($_POST['reason'] ?? '');
        $status    = $_POST['status'] ?? 'draft';

        if ($id) {
            $stmt = $db->prepare(
                "UPDATE credit_notes SET invoice_id = ?, company_name = ?, amount = ?, currency = ?, reason = ?, status = ? WHERE id = ?"
            );
            $stmt->execute([$invoiceId, $companyName, $amount, $currency, $reason, $status, $id]);
        } else {
            // Generate credit note number
            $maxStmt = $db->query("SELECT MAX(id) FROM credit_notes");
            $nextNum = ((int)$maxStmt->fetchColumn()) + 1;
            $creditNoteNo = 'CN-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $stmt = $db->prepare(
                "INSERT INTO credit_notes (credit_note_no, invoice_id, company_name, amount, currency, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$creditNoteNo, $invoiceId, $companyName, $amount, $currency, $reason, $status]);
        }

        header('Location: ' . url('credit-notes') . '?saved=1');
        exit;
    }

    /**
     * Delete credit note
     * GET /credit-notes/delete?id=X
     */
    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM credit_notes WHERE id = ?")->execute([$id]);
        }

        header('Location: ' . url('credit-notes') . '?deleted=1');
        exit;
    }

    /**
     * Generate PDF for a credit note
     * GET /credit-notes/pdf?id=X
     */
    public function pdf(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare(
            "SELECT cn.*, cn.company_name AS partner_name, i.invoice_no
             FROM credit_notes cn
             LEFT JOIN invoices i ON cn.invoice_id = i.id
             WHERE cn.id = ?"
        );
        $stmt->execute([$id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$note) { header('Location: ' . url('credit-notes')); exit; }

        $settingRows = Database::fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'company'");
        $s = array_column($settingRows, 'setting_value', 'setting_key');
        $co = [
            'companyName'    => $s['company_name']    ?? COMPANY_NAME,
            'companyAddress' => $s['company_address'] ?? COMPANY_ADDRESS,
            'companyPhone'   => $s['company_phone']   ?? COMPANY_PHONE,
            'companyEmail'   => $s['company_email']   ?? COMPANY_EMAIL,
        ];

        $this->viewStandalone('credit_notes/pdf', array_merge(['cn' => $note], $co));
    }
}
