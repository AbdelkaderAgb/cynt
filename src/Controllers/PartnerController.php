<?php
/**
 * CYN Tourism — PartnerController
 * 
 * CRUD + show detail + AJAX search API for partner autocomplete.
 */
class PartnerController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => $_GET['type'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Partner::getAll($filters, $page);

        $this->view('partners/index', [
            'partners'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => __('partner_management'),
            'activePage' => 'partners',
        ]);
    }

    public function create(): void
    {
        $this->view('partners/form', [
            'partner'    => [],
            'isEdit'     => false,
            'pageTitle'  => __('new_partner'),
            'activePage' => 'partners',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $data = [
            'company_name'   => trim($_POST['company_name'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'mobile'         => trim($_POST['mobile'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'country'        => trim($_POST['country'] ?? ''),
            'website'        => trim($_POST['website'] ?? ''),
            'tax_id'         => trim($_POST['tax_id'] ?? ''),
            'commission_rate'=> (float)($_POST['commission_rate'] ?? 0),
            'credit_limit'   => (float)($_POST['credit_limit'] ?? 0),
            'payment_terms'  => (int)($_POST['payment_terms'] ?? 30),
            'partner_type'   => $_POST['partner_type'] ?? 'agency',
            'status'         => $_POST['status'] ?? 'active',
            'notes'          => trim($_POST['notes'] ?? ''),
        ];

        if (empty($data['company_name'])) {
            header('Location: ' . url('partners/create') . '?error=missing_name');
            exit;
        }
        if (empty($data['email'])) {
            header('Location: ' . url('partners/create') . '?error=missing_email');
            exit;
        }

        // Portal password
        $portalPassword = trim($_POST['portal_password'] ?? '');
        if ($portalPassword) {
            $data['password'] = password_hash($portalPassword, PASSWORD_DEFAULT);
        }

        $id = ($_POST['id'] ?? 0);
        if ($id) {
            Partner::update((int)$id, $data);
        } else {
            Partner::create($data);
        }

        header('Location: ' . url('partners') . '?saved=1');
        exit;
    }

    public function edit(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $partner = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        $this->view('partners/form', [
            'partner'    => $partner,
            'isEdit'     => true,
            'pageTitle'  => __('edit_partner') . ': ' . $partner['company_name'],
            'activePage' => 'partners',
        ]);
    }

    public function delete(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id) Partner::delete($id);
        header('Location: ' . url('partners') . '?deleted=1');
        exit;
    }

    /**
     * Show partner detail page with voucher/invoice history
     */
    public function show(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $partner = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        // Get related vouchers
        $vouchers = [];
        try {
            $vouchers = Database::fetchAll(
                "SELECT id, voucher_no, pickup_date, pickup_time, pickup_location, dropoff_location, status, price, currency
                 FROM vouchers WHERE company_id = ? OR company_name = ? ORDER BY pickup_date DESC LIMIT 20",
                [$id, $partner['company_name']]
            );
        } catch (\Exception $e) {}

        // Get related invoices
        $invoices = [];
        try {
            $invoices = Database::fetchAll(
                "SELECT id, invoice_no, invoice_date, total_amount, currency, status
                 FROM invoices WHERE company_id = ? OR company_name = ? ORDER BY invoice_date DESC LIMIT 20",
                [$id, $partner['company_name']]
            );
        } catch (\Exception $e) {}

        $this->view('partners/show', [
            'partner'    => $partner,
            'vouchers'   => $vouchers,
            'invoices'   => $invoices,
            'pageTitle'  => __('partner_details') . ': ' . $partner['company_name'],
            'activePage' => 'partners',
        ]);
    }

    /**
     * AJAX API: search partners by name (returns JSON)
     */
    public function searchApi(): void
    {
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $query = trim($_GET['q'] ?? '');
        $results = [];

        if (strlen($query) >= 1) {
            try {
                $results = Database::fetchAll(
                    "SELECT id, company_name, contact_person, email, phone, address, city, country, partner_type
                     FROM partners
                     WHERE company_name LIKE ? OR contact_person LIKE ? OR email LIKE ?
                     ORDER BY company_name ASC
                     LIMIT 10",
                    ["%{$query}%", "%{$query}%", "%{$query}%"]
                );
            } catch (\Exception $e) {}
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($results, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========================================
    // Statement of Account
    // ========================================

    /**
     * Statement of account for a partner
     * GET /partners/statement?id=X&date_from=Y&date_to=Z
     */
    public function statement(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Partner.php';
        $db = Database::getInstance()->getConnection();

        $id       = (int)($_GET['id'] ?? 0);
        $partner  = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        $dateFrom = $_GET['date_from'] ?? date('Y-01-01');
        $dateTo   = $_GET['date_to'] ?? date('Y-12-31');
        $companyName = $partner['company_name'];

        $transactions = [];

        // Invoices = debits
        $invStmt = $db->prepare(
            "SELECT id, invoice_no, invoice_date, total_amount, currency, status 
             FROM invoices 
             WHERE (company_name = ? OR company_id = ?) AND invoice_date BETWEEN ? AND ?
             ORDER BY invoice_date"
        );
        $invStmt->execute([$companyName, $id, $dateFrom, $dateTo]);
        foreach ($invStmt->fetchAll() as $inv) {
            $transactions[] = [
                'date'        => $inv['invoice_date'],
                'type'        => 'invoice',
                'reference'   => $inv['invoice_no'],
                'description' => 'Invoice — ' . $inv['currency'],
                'debit'       => (float)$inv['total_amount'],
                'credit'      => 0,
            ];
            // If paid, add as credit
            if ($inv['status'] === 'paid') {
                $transactions[] = [
                    'date'        => $inv['invoice_date'],
                    'type'        => 'payment',
                    'reference'   => $inv['invoice_no'],
                    'description' => 'Payment received',
                    'debit'       => 0,
                    'credit'      => (float)$inv['total_amount'],
                ];
            }
        }

        // Credit notes = credits
        try {
            $cnStmt = $db->prepare(
                "SELECT cn.* FROM credit_notes cn 
                 WHERE cn.partner_id = ? AND cn.created_at BETWEEN ? AND ?
                 ORDER BY cn.created_at"
            );
            $cnStmt->execute([$id, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            foreach ($cnStmt->fetchAll() as $cn) {
                $transactions[] = [
                    'date'        => substr($cn['created_at'], 0, 10),
                    'type'        => 'credit_note',
                    'reference'   => $cn['credit_note_no'],
                    'description' => 'Credit Note — ' . ($cn['reason'] ?: ''),
                    'debit'       => 0,
                    'credit'      => (float)$cn['amount'],
                ];
            }
        } catch (\Exception $e) {}

        // Sort by date
        usort($transactions, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Calculate balance
        $totalInvoiced = array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'invoice'), 'debit'));
        $totalPaid     = array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'payment'), 'credit'));
        $totalCredits  = array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'credit_note'), 'credit'));

        $this->view('partners/statement', [
            'partner'      => $partner,
            'transactions' => $transactions,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'balance'      => [
                'total_invoiced' => $totalInvoiced,
                'total_paid'     => $totalPaid,
                'total_credits'  => $totalCredits,
                'outstanding'    => $totalInvoiced - $totalPaid - $totalCredits,
            ],
            'pageTitle'    => 'Statement — ' . $companyName,
            'activePage'   => 'partners',
        ]);
    }

    // ========================================
    // Admin: Partner Booking Requests
    // ========================================

    public function bookingRequests(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("
            SELECT r.*, p.company_name as partner_name
            FROM partner_booking_requests r
            LEFT JOIN partners p ON r.partner_id = p.id
            ORDER BY r.created_at DESC
        ");
        $requests = $stmt->fetchAll();

        $this->view('partners/booking_requests', [
            'requests'   => $requests,
            'pageTitle'  => 'Partner Booking Requests',
            'activePage' => 'partner-requests',
        ]);
    }

    public function bookingRequestAction(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        if ($id && in_array($action, ['approved', 'rejected'])) {
            // Use CURRENT_TIMESTAMP instead of NOW() — NOW() causes syntax error on MariaDB 11.4 with prepared statements
            $stmt = $db->prepare("UPDATE partner_booking_requests SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$action, $adminNotes, $id]);
        }

        header('Location: ' . url('partner-requests'));
        exit;
    }

    // ========================================
    // Admin: Partner Messages
    // ========================================

    public function partnerMessages(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $partnerId = (int)($_GET['partner_id'] ?? 0);

        if ($partnerId) {
            // View messages for a specific partner
            $partner = Database::fetchOne("SELECT * FROM partners WHERE id = ?", [$partnerId]);
            $stmt = $db->prepare("SELECT * FROM partner_messages WHERE partner_id = ? ORDER BY created_at ASC");
            $stmt->execute([$partnerId]);
            $messages = $stmt->fetchAll();

            // Mark partner messages as read
            $db->prepare("UPDATE partner_messages SET is_read = 1 WHERE partner_id = ? AND sender_type = 'partner'")->execute([$partnerId]);

            $this->view('partners/messages', [
                'partner'    => $partner,
                'messages'   => $messages,
                'pageTitle'  => 'Messages: ' . ($partner['company_name'] ?? 'Partner'),
                'activePage' => 'partner-messages',
            ]);
        } else {
            // List all partners with unread counts
            $partners = $db->query("
                SELECT p.id, p.company_name, p.email,
                    (SELECT COUNT(*) FROM partner_messages WHERE partner_id = p.id AND sender_type = 'partner' AND is_read = 0) as unread_count,
                    (SELECT MAX(created_at) FROM partner_messages WHERE partner_id = p.id) as last_message_at
                FROM partners p
                WHERE EXISTS (SELECT 1 FROM partner_messages WHERE partner_id = p.id)
                ORDER BY last_message_at DESC
            ")->fetchAll();

            $this->view('partners/messages_list', [
                'partners'   => $partners,
                'pageTitle'  => 'Partner Messages',
                'activePage' => 'partner-messages',
            ]);
        }
    }

    public function messageReply(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $partnerId = (int)($_POST['partner_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $filePath = '';  // Must be '' not null — file_path column is NOT NULL

        // Handle file upload
        if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/uploads/messages/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $fileName = 'admin_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                $filePath = url('uploads/messages/' . $fileName);
            }
        }

        if ($partnerId && ($message || $filePath)) {
            // Use explicit next ID to work even without AUTO_INCREMENT
            $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM partner_messages")->fetchColumn();
            $stmt = $db->prepare("INSERT INTO partner_messages (id, partner_id, sender_type, sender_id, subject, message, file_path) VALUES (?, ?, 'admin', ?, ?, ?, ?)");
            $stmt->execute([$nextId, $partnerId, Auth::id(), $subject, $message ?: '📎 File attachment', $filePath ?: '']);
        }

        header('Location: ' . url('partner-messages') . '?partner_id=' . $partnerId);
        exit;
    }
}