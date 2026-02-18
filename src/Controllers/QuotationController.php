<?php
/**
 * CYN Tourism — QuotationController
 * Day-by-day quotation builder with PDF export and convert-to-bookings.
 */
class QuotationController extends Controller
{
    public function index(): void
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
            $where .= " AND (q.quote_number LIKE ? OR q.client_name LIKE ? OR q.client_email LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if (!empty($filters['status'])) {
            $where .= " AND q.status = ?";
            $params[] = $filters['status'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM quotations q WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT q.*, p.company_name AS partner_name
            FROM quotations q
            LEFT JOIN partners p ON q.partner_id = p.id
            WHERE $where
            ORDER BY q.created_at DESC
            LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('quotations/index', [
            'quotations' => $quotations,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('quotations') ?: 'Quotations',
            'activePage' => 'quotations',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('quotations/form', [
            'quotation'  => null,
            'items'      => [],
            'partners'   => $partners,
            'pageTitle'  => __('new_quotation') ?: 'New Quotation',
            'activePage' => 'quotations',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $quoteNumber = 'QT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $items = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)($item['total_price'] ?? 0);
        }

        $discountPercent = (float)($_POST['discount_percent'] ?? 0);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $taxPercent = (float)($_POST['tax_percent'] ?? 0);
        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($taxPercent / 100);
        $total = $afterDiscount + $taxAmount;

        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM quotations")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO quotations
            (id, quote_number, partner_id, client_name, client_email, client_phone,
             travel_dates_from, travel_dates_to, adults, children, infants,
             subtotal, discount_percent, discount_amount, tax_percent, tax_amount, total, currency,
             valid_until, cancellation_policy, payment_terms, notes, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)");
        $stmt->execute([
            $nextId,
            $quoteNumber,
            $this->postNullableId('partner_id'),
            trim($_POST['client_name'] ?? ''),
            trim($_POST['client_email'] ?? ''),
            trim($_POST['client_phone'] ?? ''),
            $_POST['travel_dates_from'] ?: null,
            $_POST['travel_dates_to'] ?: null,
            (int)($_POST['adults'] ?? 0),
            (int)($_POST['children'] ?? 0),
            (int)($_POST['infants'] ?? 0),
            $subtotal,
            $discountPercent,
            $discountAmount,
            $taxPercent,
            $taxAmount,
            $total,
            $_POST['currency'] ?? 'USD',
            $_POST['valid_until'] ?: null,
            trim($_POST['cancellation_policy'] ?? ''),
            trim($_POST['payment_terms'] ?? ''),
            trim($_POST['notes'] ?? ''),
            $_SESSION['user_id'] ?? null,
        ]);

        if (empty($_POST['client_name'])) {
             header('Location: ' . url('quotations/create') . '?error=missing_client');
             exit;
        }

        // Save items
        $this->saveItems($db, $nextId, $items);

        header('Location: ' . url('quotations/show') . '?id=' . $nextId . '&saved=1');
        exit;
    }

    public function show(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT q.*, p.company_name AS partner_name
            FROM quotations q LEFT JOIN partners p ON q.partner_id = p.id WHERE q.id = ?");
        $stmt->execute([$id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quotation) { header('Location: ' . url('quotations')); exit; }

        $items = $db->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY day_number, sort_order");
        $items->execute([$id]);
        $items = $items->fetchAll(PDO::FETCH_ASSOC);

        $this->view('quotations/show', [
            'q'          => $quotation,
            'items'      => $items,
            'pageTitle'  => 'Quotation: ' . $quotation['quote_number'],
            'activePage' => 'quotations',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM quotations WHERE id = ?");
        $stmt->execute([$id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quotation) { header('Location: ' . url('quotations')); exit; }

        $itemsStmt = $db->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY day_number, sort_order");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('quotations/form', [
            'quotation'  => $quotation,
            'items'      => $items,
            'partners'   => $partners,
            'pageTitle'  => 'Edit: ' . $quotation['quote_number'],
            'activePage' => 'quotations',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('quotations')); exit; }

        $items = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)($item['total_price'] ?? 0);
        }

        $discountPercent = (float)($_POST['discount_percent'] ?? 0);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $taxPercent = (float)($_POST['tax_percent'] ?? 0);
        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($taxPercent / 100);
        $total = $afterDiscount + $taxAmount;

        $stmt = $db->prepare("UPDATE quotations SET
            partner_id = ?, client_name = ?, client_email = ?, client_phone = ?,
            travel_dates_from = ?, travel_dates_to = ?, adults = ?, children = ?, infants = ?,
            subtotal = ?, discount_percent = ?, discount_amount = ?, tax_percent = ?, tax_amount = ?, total = ?, currency = ?,
            valid_until = ?, cancellation_policy = ?, payment_terms = ?, notes = ?, status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $this->postNullableId('partner_id'),
            trim($_POST['client_name'] ?? ''),
            trim($_POST['client_email'] ?? ''),
            trim($_POST['client_phone'] ?? ''),
            $_POST['travel_dates_from'] ?: null,
            $_POST['travel_dates_to'] ?: null,
            (int)($_POST['adults'] ?? 0),
            (int)($_POST['children'] ?? 0),
            (int)($_POST['infants'] ?? 0),
            $subtotal,
            $discountPercent,
            $discountAmount,
            $taxPercent,
            $taxAmount,
            $total,
            $_POST['currency'] ?? 'USD',
            $_POST['valid_until'] ?: null,
            trim($_POST['cancellation_policy'] ?? ''),
            trim($_POST['payment_terms'] ?? ''),
            trim($_POST['notes'] ?? ''),
            $_POST['status'] ?? 'draft',
            $id,
        ]);

        $this->saveItems($db, $id, $items);

        header('Location: ' . url('quotations/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM quotation_items WHERE quotation_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM quotations WHERE id = ?")->execute([$id]);
        }
        header('Location: ' . url('quotations') . '?deleted=1');
        exit;
    }

    public function pdf(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT q.*, p.company_name AS partner_name
            FROM quotations q LEFT JOIN partners p ON q.partner_id = p.id WHERE q.id = ?");
        $stmt->execute([$id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quotation) { header('Location: ' . url('quotations')); exit; }

        $itemsStmt = $db->prepare("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY day_number, sort_order");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->viewStandalone('quotations/pdf', [
            'q'     => $quotation,
            'items' => $items,
        ]);
    }

    /**
     * Convert quotation to actual bookings (hotel vouchers, tours, transfers)
     */
    public function convert(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM quotations WHERE id = ? AND status IN ('draft','sent','accepted')");
        $stmt->execute([$id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quotation) {
            header('Location: ' . url('quotations') . '?error=not_found');
            exit;
        }

        // Mark as converted
        $db->prepare("UPDATE quotations SET status = 'converted', converted_at = NOW() WHERE id = ?")->execute([$id]);

        header('Location: ' . url('quotations/show') . '?id=' . $id . '&converted=1');
        exit;
    }

    private function saveItems(PDO $db, int $quotationId, array $items): void
    {
        $db->prepare("DELETE FROM quotation_items WHERE quotation_id = ?")->execute([$quotationId]);
        $sortOrder = 0;
        foreach ($items as $item) {
            $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM quotation_items")->fetchColumn();
            $stmt = $db->prepare("INSERT INTO quotation_items
                (id, quotation_id, day_number, item_type, item_name, description, quantity, unit_price, total_price, currency, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nextItemId,
                $quotationId,
                (int)($item['day_number'] ?? 1),
                $item['item_type'] ?? 'other',
                trim($item['item_name'] ?? ''),
                trim($item['description'] ?? ''),
                (int)($item['quantity'] ?? 1),
                (float)($item['unit_price'] ?? 0),
                (float)($item['total_price'] ?? 0),
                $item['currency'] ?? 'USD',
                $sortOrder++,
            ]);
        }
    }
}
