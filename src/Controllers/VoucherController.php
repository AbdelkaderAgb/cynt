<?php
/**
 * CYN Tourism — VoucherController
 */
class VoucherController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $filters = [
            'search'    => $_GET['search'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'company'   => $_GET['company'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result    = Voucher::getAll($filters, $page);
        $companies = Voucher::getCompanies();

        $this->view('vouchers/index', [
            'vouchers'  => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'pages'     => $result['pages'],
            'filters'   => $filters,
            'companies' => $companies,
            'pageTitle'  => __('voucher_management') ?: 'Voucher Management',
            'activePage' => 'vouchers',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $this->view('vouchers/form', [
            'voucher'    => [],
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'partners'   => Partner::getActive(),
            'isEdit'     => false,
            'pageTitle'  => 'Yeni Voucher',
            'activePage' => 'vouchers',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $transferType    = $_POST['transfer_type'] ?? 'one_way';
        $pickupLocation  = trim($_POST['pickup_location'] ?? '');
        $dropoffLocation = trim($_POST['dropoff_location'] ?? '');
        $pickupDate      = $_POST['pickup_date'] ?? '';
        $pickupTime      = $_POST['pickup_time'] ?? '';
        $stopsJson       = null;

        $rawStops = json_decode($_POST['stops_json'] ?? '[]', true);
        $extraStops = array_values(array_filter((array)$rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));
        if (!empty($extraStops)) {
            $transferType = 'multi_stop';
            $stopsJson    = json_encode($extraStops);
        }

        // Parse guests — extract first guest for header fields, store all as JSON in passengers
        $rawGuests   = json_decode($_POST['guests_json'] ?? '[]', true);
        $guests      = array_values(array_filter((array)$rawGuests, fn($g) => !empty($g['name'])));
        $guestName   = $guests[0]['name']     ?? '';
        $guestPassport = $guests[0]['passport'] ?? '';
        $passengersJson = !empty($guests) ? json_encode($guests) : null;

        $data = [
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'company_id'         => (int)($_POST['company_id'] ?? 0) ?: null,
            'hotel_name'         => '',
            'pickup_location'    => $pickupLocation,
            'dropoff_location'   => $dropoffLocation,
            'pickup_date'        => $pickupDate,
            'pickup_time'        => $pickupTime,
            'return_date'        => $_POST['return_date'] ?: null,
            'return_time'        => $_POST['return_time'] ?: null,
            'transfer_type'      => $transferType,
            'stops_json'         => $stopsJson,
            'total_pax'          => (int)($_POST['total_pax'] ?? 1),
            'passengers'         => $passengersJson,
            'guest_name'         => $guestName,
            'passenger_passport' => $guestPassport,
            'flight_number'      => trim($_POST['flight_number'] ?? ''),
            'vehicle_id'         => $_POST['vehicle_id'] ?: null,
            'driver_id'          => $_POST['driver_id'] ?: null,
            'guide_id'           => $_POST['guide_id'] ?: null,
            'price'              => (float)($_POST['price'] ?? 0),
            'currency'           => $_POST['currency'] ?? 'USD',
            'status'             => $_POST['status'] ?? 'pending',
            'notes'              => trim($_POST['notes'] ?? ''),
        ];

        $isEdit    = (int)($_POST['id'] ?? 0);
        $companyId = (int)($data['company_id'] ?? 0);

        if ($isEdit) {
            Voucher::update($isEdit, $data);
            $id = $isEdit;
        } else {
            $id = Voucher::create($data);
        }

        // Auto-link to partner portal
        if ($companyId > 0) {
            Database::execute(
                "UPDATE vouchers SET partner_id = ? WHERE id = ? AND (partner_id IS NULL OR partner_id = 0)",
                [$companyId, $id]
            );
        }

        header('Location: ' . url('vouchers') . '?saved=1');
        exit;
    }

    public function show(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);

        if (!$voucher) {
            header('Location: ' . url('vouchers'));
            exit;
        }

        // Resolve partner phone & address — prefer company_id FK, fall back to name match
        $partner = null;
        if (!empty($voucher['company_id'])) {
            $partner = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE id = ? LIMIT 1", [(int)$voucher['company_id']]);
        }
        if (!$partner && !empty($voucher['company_name'])) {
            $partner = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE company_name = ? LIMIT 1", [$voucher['company_name']]);
        }
        if ($partner) {
            $voucher['partner_phone'] = $partner['phone'] ?? '';
            $voucher['partner_address'] = implode(', ', array_filter([$partner['address'] ?? '', $partner['city'] ?? '', $partner['country'] ?? '']));
        }

        $this->view('vouchers/show', [
            'voucher'    => $voucher,
            'pageTitle'  => 'Voucher: ' . $voucher['voucher_no'],
            'activePage' => 'vouchers',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        require_once ROOT_PATH . '/src/Models/Partner.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);

        if (!$voucher) {
            header('Location: ' . url('vouchers'));
            exit;
        }

        $this->view('vouchers/form', [
            'voucher'    => $voucher,
            'drivers'    => Fleet::getActiveDrivers(),
            'vehicles'   => Fleet::getActiveVehicles(),
            'guides'     => Fleet::getActiveGuides(),
            'partners'   => Partner::getActive(),
            'isEdit'     => true,
            'pageTitle'  => __('edit') . ': ' . $voucher['voucher_no'],
            'activePage' => 'vouchers',
        ]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id) Voucher::delete($id);
        header('Location: ' . url('vouchers') . '?deleted=1');
        exit;
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json');
        $data     = json_decode(file_get_contents('php://input'), true) ?? [];
        $id       = (int)($data['id'] ?? $_POST['id'] ?? 0);
        $status   = trim($data['status'] ?? $_POST['status'] ?? '');
        $allowed  = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        if (!$id || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $db->prepare("UPDATE vouchers SET status = ?, updated_at = datetime('now') WHERE id = ?")
           ->execute([$status, $id]);

        echo json_encode(['success' => true, 'status' => $status]);
        exit;
    }
}
