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
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => $_GET['type'] ?? '',
        ];
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $result = Partner::getAll($filters, $page);

        // Fetch per-currency balances for all partners on this page in one query
        // Result keyed as: $creditBalances[partner_id][currency] = balance
        $creditBalances = [];
        if (!empty($result['data'])) {
            $ids         = array_column($result['data'], 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = Database::fetchAll(
                "SELECT partner_id, currency,
                        ROUND(SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE -amount END), 2) AS balance
                 FROM credit_transactions
                 WHERE partner_id IN ($placeholders)
                 GROUP BY partner_id, currency
                 HAVING balance > 0
                 ORDER BY partner_id, currency",
                $ids
            );
            foreach ($rows as $row) {
                $creditBalances[(int)$row['partner_id']][$row['currency']] = (float)$row['balance'];
            }
        }

        $this->view('partners/index', [
            'partners'       => $result['data'],
            'total'          => $result['total'],
            'page'           => $result['page'],
            'pages'          => $result['pages'],
            'filters'        => $filters,
            'creditBalances' => $creditBalances,
            'pageTitle'      => __('partner_management'),
            'activePage'     => 'partners',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
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
        $this->requireAuth();
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
        $this->requireAuth();
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
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Partner.php';
        require_once ROOT_PATH . '/src/Models/CreditTransaction.php';

        $id = (int)($_GET['id'] ?? 0);
        $partner = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        // Per-currency credit balances from the ledger
        $currencyBalances = [];
        try {
            $rows = Database::fetchAll(
                "SELECT currency,
                        ROUND(SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE -amount END), 2) AS balance,
                        SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE 0 END) AS total_in,
                        SUM(CASE WHEN type IN ('payment','adjustment') THEN amount ELSE 0 END) AS total_out,
                        COUNT(*) AS tx_count
                 FROM credit_transactions
                 WHERE partner_id = ?
                 GROUP BY currency
                 ORDER BY currency",
                [$id]
            );
            foreach ($rows as $row) {
                $currencyBalances[$row['currency']] = $row;
            }
        } catch (\Exception $e) {}

        // Invoices – match by partner_id (most reliable), company_id, or company_name (NOCASE)
        $invoices = [];
        try {
            $invoices = Database::fetchAll(
                "SELECT DISTINCT id, invoice_no, invoice_date, due_date, total_amount, paid_amount, currency, status, type
                 FROM invoices
                 WHERE partner_id = ?
                    OR company_id = ?
                    OR LOWER(TRIM(company_name)) = LOWER(TRIM(?))
                 ORDER BY CASE WHEN invoice_date IS NULL OR invoice_date = '' THEN 0 ELSE 1 END DESC,
                          invoice_date DESC",
                [$id, $id, $partner['company_name']]
            );
        } catch (\Exception $e) {}

        // Full paginated credit transactions
        $txPage   = max(1, (int)($_GET['tx_page'] ?? 1));
        $txResult = CreditTransaction::getByPartner($id, $txPage, 20);

        // Flash messages (from recharge / pay-invoice redirects)
        $flash = $_SESSION['credit_flash'] ?? null;
        unset($_SESSION['credit_flash']);

        $this->view('partners/show', [
            'partner'          => $partner,
            'currencyBalances' => $currencyBalances,
            'invoices'         => $invoices,
            'transactions'     => $txResult['data'],
            'txTotal'          => $txResult['total'],
            'txPage'           => $txResult['page'],
            'txPages'          => $txResult['pages'],
            'flash'            => $flash,
            'pageTitle'        => __('partner_details') . ': ' . $partner['company_name'],
            'activePage'       => 'partners',
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

        $id          = (int)($_GET['id'] ?? 0);
        $partner     = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        $dateFrom    = $_GET['date_from'] ?? date('Y-01-01');
        $dateTo      = $_GET['date_to']   ?? date('Y-12-31');
        $companyName = $partner['company_name'];

        // ------------------------------------------------------------------
        // Transactions grouped by currency
        // ------------------------------------------------------------------
        // Key: currency code → ['entries' => [...], 'total_invoiced', 'total_paid', 'outstanding']
        $byCurrency = [];

        // Helper to initialise a currency bucket
        $initCur = function (string $cur) use (&$byCurrency) {
            if (!isset($byCurrency[$cur])) {
                $byCurrency[$cur] = [
                    'entries'       => [],
                    'total_invoiced'=> 0.0,
                    'total_paid'    => 0.0,
                    'total_credits' => 0.0,
                ];
            }
        };

        // ── Invoices (debit) + any payment already recorded (credit) ───────
        $invStmt = $db->prepare(
            "SELECT DISTINCT id, invoice_no, invoice_date, due_date,
                    total_amount, paid_amount, currency, status, payment_date
             FROM invoices
             WHERE (partner_id = ? OR company_id = ? OR LOWER(TRIM(company_name)) = LOWER(TRIM(?)))
               AND invoice_date BETWEEN ? AND ?
             ORDER BY invoice_date"
        );
        $invStmt->execute([$id, $id, $companyName, $dateFrom, $dateTo]);

        foreach ($invStmt->fetchAll() as $inv) {
            $cur       = strtoupper($inv['currency'] ?? 'EUR');
            $total     = (float)$inv['total_amount'];
            $paid      = (float)($inv['paid_amount'] ?? 0);
            $status    = $inv['status'] ?? 'draft';
            $invDate   = $inv['invoice_date'] ?? date('Y-m-d');
            $payDate   = !empty($inv['payment_date']) ? $inv['payment_date'] : $invDate;

            $initCur($cur);

            // Debit row — invoice issued
            $byCurrency[$cur]['entries'][] = [
                'date'        => $invDate,
                'sort_date'   => $invDate,
                'type'        => 'invoice',
                'reference'   => $inv['invoice_no'],
                'ref_id'      => $inv['id'],
                'description' => 'Invoice issued',
                'debit'       => $total,
                'credit'      => 0.0,
                'currency'    => $cur,
            ];
            $byCurrency[$cur]['total_invoiced'] += $total;

            // Credit row — payment received (full or partial)
            if ($paid > 0) {
                $byCurrency[$cur]['entries'][] = [
                    'date'        => $payDate,
                    'sort_date'   => $payDate . 'Z', // sort after same-day debit
                    'type'        => 'payment',
                    'reference'   => $inv['invoice_no'],
                    'ref_id'      => $inv['id'],
                    'description' => $status === 'paid' ? 'Payment received (full)' : 'Payment received (partial)',
                    'debit'       => 0.0,
                    'credit'      => $paid,
                    'currency'    => $cur,
                ];
                $byCurrency[$cur]['total_paid'] += $paid;
            }
        }

        // ── Credit notes ────────────────────────────────────────────────────
        try {
            $cnStmt = $db->prepare(
                "SELECT * FROM credit_notes
                 WHERE partner_id = ? AND created_at BETWEEN ? AND ?
                 ORDER BY created_at"
            );
            $cnStmt->execute([$id, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            foreach ($cnStmt->fetchAll() as $cn) {
                $cur = strtoupper($cn['currency'] ?? 'EUR');
                $initCur($cur);
                $dt = substr($cn['created_at'], 0, 10);
                $byCurrency[$cur]['entries'][] = [
                    'date'        => $dt,
                    'sort_date'   => $dt . 'Z',
                    'type'        => 'credit_note',
                    'reference'   => $cn['credit_note_no'] ?? 'CN',
                    'description' => 'Credit Note' . ($cn['reason'] ? ' — ' . $cn['reason'] : ''),
                    'debit'       => 0.0,
                    'credit'      => (float)$cn['amount'],
                    'currency'    => $cur,
                ];
                $byCurrency[$cur]['total_credits'] += (float)$cn['amount'];
                $byCurrency[$cur]['total_paid']    += (float)$cn['amount'];
            }
        } catch (\Exception $e) {}

        // ── Credit Transactions (recharges / refunds / adjustments) ────────
        try {
            $ctStmt = $db->prepare(
                "SELECT id, type, amount, currency, description, created_at
                 FROM credit_transactions
                 WHERE partner_id = ?
                   AND DATE(created_at) BETWEEN ? AND ?
                 ORDER BY created_at"
            );
            $ctStmt->execute([$id, $dateFrom, $dateTo]);
            foreach ($ctStmt->fetchAll() as $ct) {
                $cur  = strtoupper($ct['currency'] ?? 'EUR');
                $amt  = (float)$ct['amount'];
                $dt   = substr($ct['created_at'], 0, 10);
                $type = $ct['type'] ?? 'recharge';
                $initCur($cur);

                $isCredit = in_array($type, ['recharge', 'refund'], true);
                $byCurrency[$cur]['entries'][] = [
                    'date'        => $dt,
                    'sort_date'   => $dt . 'W', // between invoice(no suffix) and payment(Z)
                    'type'        => $type,
                    'reference'   => 'CT-' . $ct['id'],
                    'ref_id'      => null,
                    'description' => $ct['description'] ?: ucfirst(str_replace('_', ' ', $type)),
                    'debit'       => $isCredit ? 0.0 : $amt,
                    'credit'      => $isCredit ? $amt : 0.0,
                    'currency'    => $cur,
                ];
                if ($isCredit) {
                    $byCurrency[$cur]['total_credits'] += $amt;
                }
            }
        } catch (\Exception $e) {}

        // ── Sort each currency bucket by date ───────────────────────────────
        foreach ($byCurrency as $cur => &$bucket) {
            usort($bucket['entries'], fn($a, $b) => strcmp($a['sort_date'], $b['sort_date']));
            $bucket['outstanding'] = round(
                $bucket['total_invoiced'] - $bucket['total_paid'] - $bucket['total_credits'], 2
            );
        }
        unset($bucket);

        // Sort currencies alphabetically
        ksort($byCurrency);

        // ── Overall totals for the summary cards (multi-currency aware) ─────
        $grandTotals = [];
        foreach ($byCurrency as $cur => $bucket) {
            $grandTotals[$cur] = [
                'currency'       => $cur,
                'total_invoiced' => $bucket['total_invoiced'],
                'total_paid'     => $bucket['total_paid'],
                'outstanding'    => $bucket['outstanding'],
            ];
        }

        $this->view('partners/statement', [
            'partner'     => $partner,
            'byCurrency'  => $byCurrency,
            'grandTotals' => $grandTotals,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'pageTitle'   => 'Statement — ' . $companyName,
            'activePage'  => 'partners',
        ]);
    }

    // ========================================
    // Statement PDF Export
    // ========================================

    public function statementPdf(): void
    {
        $this->requireAuth();
        // Reuse statement data, then render print-friendly HTML
        $id = (int)($_GET['id'] ?? 0);
        require_once ROOT_PATH . '/src/Models/Partner.php';
        $partner = Partner::getById($id);
        if (!$partner) { header('Location: ' . url('partners')); exit; }

        // Forward to the same statement logic but capture output for PDF.
        // For now: redirect to print-friendly statement page.
        $qs = http_build_query([
            'id'        => $id,
            'date_from' => $_GET['date_from'] ?? date('Y-01-01'),
            'date_to'   => $_GET['date_to']   ?? date('Y-12-31'),
            'print'     => '1',
        ]);
        header('Location: ' . url('partners/statement') . '?' . $qs);
        exit;
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

    // ========================================
    // Partner Credits
    // ========================================

    /**
     * GET /partners/credits?id=X
     * Display credit balance + transaction history + recharge form.
     */
    /**
     * GET /partners/credits?id=X  — now embedded in show page, just redirect.
     */
    public function credits(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        header('Location: ' . url('partners/show') . '?id=' . $id . '#credits');
        exit;
    }

    /**
     * POST /partners/credits/recharge — Add credit, redirect back to partner show.
     */
    public function creditRecharge(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/CreditTransaction.php';

        $partnerId   = (int)($_POST['partner_id'] ?? 0);
        $amount      = (float)($_POST['amount'] ?? 0);
        $currency    = $_POST['currency']    ?? 'EUR';
        $description = trim($_POST['description'] ?? '');

        if (!$partnerId || $amount <= 0) {
            $_SESSION['credit_flash'] = ['type' => 'error', 'message' => 'Invalid amount or partner.'];
        } else {
            $txId = CreditTransaction::create([
                'partner_id'  => $partnerId,
                'type'        => 'recharge',
                'amount'      => $amount,
                'currency'    => $currency,
                'description' => $description ?: 'Manual credit recharge',
                'ref_type'    => 'manual',
            ]);
            $_SESSION['credit_flash'] = [
                'type'       => 'success',
                'message'    => number_format($amount, 2) . ' ' . $currency . ' added to credit balance.',
                'receipt_url'=> url('partners/credits/receipt-pdf') . '?id=' . (int)$txId,
            ];
        }

        header('Location: ' . url('partners/show') . '?id=' . $partnerId . '#credits');
        exit;
    }

    /**
     * POST /partners/credits/pay-invoice — Pay invoice with credit.
     * Redirects back to the invoice show page (hotel or transfer) or partner show for others.
     */
    public function creditPayInvoice(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/CreditTransaction.php';

        $partnerId = (int)($_POST['partner_id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount    = (float)($_POST['amount'] ?? 0);
        $currency  = $_POST['currency'] ?? 'EUR';

        $result = CreditTransaction::payInvoice($partnerId, $invoiceId, $amount, $currency);

        /* ── redirect to invoice show page so flash appears there ── */
        $inv     = Database::fetchOne("SELECT type FROM invoices WHERE id = ?", [$invoiceId]);
        $invType = $inv['type'] ?? '';

        if ($invType === 'hotel') {
            $_SESSION['invoice_flash'] = [
                'type'    => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
            ];
            header('Location: ' . url('hotel-invoice/show') . '?id=' . $invoiceId);
        } elseif ($invType === 'transfer') {
            $_SESSION['invoice_flash'] = [
                'type'    => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
            ];
            header('Location: ' . url('invoices/show') . '?id=' . $invoiceId);
        } else {
            $_SESSION['credit_flash'] = [
                'type'    => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
            ];
            header('Location: ' . url('partners/show') . '?id=' . $partnerId . '#invoices');
        }
        exit;
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

    /**
     * POST /partners/credits/pay-invoice
     * Apply partner credit to an invoice.
     * Used by: views/invoices/show.php "Pay with Credit" modal (all invoice types).
     */
    public function payInvoiceCredit(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        require_once ROOT_PATH . '/src/Models/CreditTransaction.php';
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $partnerId = (int)($_POST['partner_id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount    = (float)($_POST['amount']     ?? 0);
        $currency  = strtoupper(trim($_POST['currency'] ?? 'EUR'));

        if (!$partnerId || !$invoiceId || $amount <= 0) {
            $_SESSION['invoice_flash'] = ['type' => 'error', 'message' => 'Invalid payment request.'];
            $this->redirectBack($invoiceId);
        }

        $result = CreditTransaction::payInvoice($partnerId, $invoiceId, $amount, $currency);

        $_SESSION['invoice_flash'] = [
            'type'    => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ];

        $this->redirectBack($invoiceId);
    }

    /**
     * Redirect back to the invoice show page after credit payment.
     */
    private function redirectBack(int $invoiceId): never
    {
        // Detect invoice type to redirect to the right "show" URL
        $inv = null;
        if ($invoiceId > 0) {
            $inv = Database::fetchOne("SELECT type FROM invoices WHERE id = ?", [$invoiceId]);
        }
        $type = $inv['type'] ?? 'generic';

        $baseUrl = match($type) {
            'hotel'    => url('hotel-invoice/show'),
            'transfer' => url('transfer-invoice/show'),
            default    => url('invoices/show'),
        };

        header('Location: ' . $baseUrl . '?id=' . $invoiceId);
        exit;
    }
}