<?php
/**
 * CYN Tourism — TourController
 */
class TourController extends Controller
{
    public function voucher(): void
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
            $where .= " AND (tour_name LIKE ? OR tour_code LIKE ? OR destination LIKE ?)";
            $s = "%{$filters['search']}%";
            $params = [$s, $s, $s];
        }
        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM tours WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT * FROM tours WHERE $where ORDER BY tour_date DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $tours = $stmt->fetchAll();

        $this->view('tours/voucher', [
            'tours'      => $tours,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'filters'    => $filters,
            'pageTitle'  => __('tour_voucher') ?: 'Tour Vouchers',
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherCreate(): void
    {
        $this->requireAuth();
        $this->view('tours/form', [
            'pageTitle'  => 'New Tour Voucher',
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherStore(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $adults = (int)($_POST['adults'] ?? 0);
        $children = (int)($_POST['children'] ?? 0);
        $infants = (int)($_POST['infants'] ?? 0);
        $priceAdult = (float)($_POST['price_per_person'] ?? 0);   // price per 1 pax (adult)
        $priceChild = (float)($_POST['price_child'] ?? 0);        // price per 1 pax (child)
        $priceInfant = (float)($_POST['price_per_infant'] ?? 0);   // price per 1 pax (infant)
        $currency = trim($_POST['currency'] ?? 'USD');
        $totalPrice = $adults * $priceAdult + $children * $priceChild + $infants * $priceInfant;

        // Parse tour_items to get first tour info for backwards-compatible columns
        $tourItems = json_decode($_POST['tour_items'] ?? '[]', true) ?: [];
        $firstTour = $tourItems[0] ?? [];
        $tourName = $firstTour['name'] ?? trim($_POST['company_name'] ?? 'Tour');
        $tourDate = $firstTour['date'] ?? date('Y-m-d');
        $duration = $firstTour['duration'] ?? '';

        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM tours")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO tours
            (id, tour_name, tour_code, description, tour_type, destination, pickup_location, dropoff_location,
             tour_date, start_time, end_time, total_pax, price_per_person, price_child, price_per_infant, total_price, currency, status,
             company_name, hotel_name, customer_phone, adults, children, infants, customers, tour_items)
            VALUES (?, ?, ?, ?, 'daily', '', '', '',
                    ?, '', '', ?, ?, ?, ?, ?, ?, 'pending',
                    ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $nextId,
            $tourName,
            'TV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            $duration,
            $tourDate,
            $adults + $children + $infants,
            $priceAdult,
            $priceChild,
            $priceInfant,
            $totalPrice,
            $currency,
            trim($_POST['company_name'] ?? ''),
            trim($_POST['hotel_name'] ?? ''),
            trim($_POST['customer_phone'] ?? ''),
            $adults,
            $children,
            $infants,
            $_POST['customers'] ?? '[]',
            $_POST['tour_items'] ?? '[]',
        ]);

        header('Location: ' . url('tour-voucher') . '?saved=1');
        exit;
    }

    public function voucherShow(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();
        if (!$tour) { header('Location: ' . url('tour-voucher')); exit; }

        $this->view('tours/voucher_show', [
            't'          => $tour,
            'pageTitle'  => 'Tour: ' . ($tour['tour_name'] ?: $tour['tour_code']),
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherEdit(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();
        if (!$tour) { header('Location: ' . url('tour-voucher')); exit; }

        $this->view('tours/form_edit', [
            't'          => $tour,
            'pageTitle'  => 'Edit: ' . ($tour['tour_name'] ?: $tour['tour_code']),
            'activePage' => 'tour-voucher',
        ]);
    }

    public function voucherUpdate(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: ' . url('tour-voucher')); exit; }

        $adults = (int)($_POST['adults'] ?? 0);
        $children = (int)($_POST['children'] ?? 0);
        $infants = (int)($_POST['infants'] ?? 0);

        $tourItems = json_decode($_POST['tour_items'] ?? '[]', true) ?: [];
        $firstTour = $tourItems[0] ?? [];
        $tourName = $firstTour['name'] ?? trim($_POST['company_name'] ?? 'Tour');
        $tourDate = $firstTour['date'] ?? date('Y-m-d');
        $duration = $firstTour['duration'] ?? '';

        $priceAdult = (float)($_POST['price_per_person'] ?? 0);
        $priceChild = (float)($_POST['price_child'] ?? 0);
        $priceInfant = (float)($_POST['price_per_infant'] ?? 0);
        $totalPrice = $adults * $priceAdult + $children * $priceChild + $infants * $priceInfant;
        $currency = trim($_POST['currency'] ?? 'USD');

        $stmt = $db->prepare("UPDATE tours SET
            tour_name = ?, description = ?, tour_date = ?,
            total_pax = ?, price_per_person = ?, price_child = ?, price_per_infant = ?, total_price = ?, currency = ?,
            company_name = ?, hotel_name = ?, customer_phone = ?,
            adults = ?, children = ?, infants = ?, customers = ?, tour_items = ?,
            status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $stmt->execute([
            $tourName,
            $duration,
            $tourDate,
            $adults + $children + $infants,
            $priceAdult,
            $priceChild,
            $priceInfant,
            $totalPrice,
            $currency,
            trim($_POST['company_name'] ?? ''),
            trim($_POST['hotel_name'] ?? ''),
            trim($_POST['customer_phone'] ?? ''),
            $adults,
            $children,
            $infants,
            $_POST['customers'] ?? '[]',
            $_POST['tour_items'] ?? '[]',
            $_POST['status'] ?? 'pending',
            $id,
        ]);

        header('Location: ' . url('tour-voucher/show') . '?id=' . $id . '&updated=1');
        exit;
    }

    public function voucherDelete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM tours WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: ' . url('tour-voucher') . '?deleted=1');
        exit;
    }

    public function invoice(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type'   => 'tour',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Invoice::getAll($filters, $page);

        $this->view('tours/invoice', [
            'invoices'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => __('tour_invoice') ?: 'Tour Invoices',
            'activePage' => 'tour-invoice',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAuth();
        $this->view('tours/invoice_form', [
            'pageTitle'  => 'New Tour Invoice',
            'activePage' => 'tour-invoice',
        ]);
    }

    public function invoiceStore(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $db = Database::getInstance()->getConnection();

        $invoiceNo = 'TRI-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $tours = json_decode($_POST['tours'] ?? '[]', true) ?: [];
        $totalPrice = (float)($_POST['total_price'] ?? 0);

        $nextInvId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoices")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO invoices
            (id, invoice_no, company_name, invoice_date, due_date, subtotal, total_amount, currency, status, notes, type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, 'tour')");
        $stmt->execute([
            $nextInvId,
            $invoiceNo,
            trim($_POST['company_name'] ?? ''),
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days')),
            $totalPrice,
            $totalPrice,
            $_POST['currency'] ?? 'USD',
            trim($_POST['notes'] ?? ''),
        ]);

        $invoiceId = $nextInvId;

        // Insert tour items
        foreach ($tours as $tour) {
            $desc = ($tour['name'] ?? 'Tour');
            if (!empty($tour['date'])) $desc .= ' (' . $tour['date'] . ')';
            if (!empty($tour['duration'])) $desc .= ' - ' . $tour['duration'];

            $nextItemId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM invoice_items")->fetchColumn();
            $stmtItem = $db->prepare("INSERT INTO invoice_items (id, invoice_id, description, quantity, unit_price, total_price)
                VALUES (?, ?, ?, 1, ?, ?)");
            $stmtItem->execute([
                $nextItemId,
                $invoiceId,
                $desc,
                (float)($tour['price'] ?? 0),
                (float)($tour['price'] ?? 0),
            ]);
        }

        header('Location: ' . url('tour-invoice') . '?saved=1');
        exit;
    }
}