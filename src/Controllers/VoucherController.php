<?php
/**
 * CYN Tourism — VoucherController
 */
class VoucherController extends Controller
{
    public function index(): void
    {
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
            'pageTitle'  => 'Voucher Yönetimi',
            'activePage' => 'vouchers',
        ]);
    }

    public function create(): void
    {
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
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $data = [
            'company_name'    => trim($_POST['company_name'] ?? ''),
            'hotel_name'      => trim($_POST['hotel_name'] ?? ''),
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
            'dropoff_location'=> trim($_POST['dropoff_location'] ?? ''),
            'pickup_date'     => $_POST['pickup_date'] ?? '',
            'pickup_time'     => $_POST['pickup_time'] ?? '',
            'return_date'     => $_POST['return_date'] ?: null,
            'return_time'     => $_POST['return_time'] ?: null,
            'transfer_type'   => $_POST['transfer_type'] ?? 'one_way',
            'total_pax'       => (int)($_POST['total_pax'] ?? 1),
            'passengers'      => trim($_POST['passengers'] ?? ''),
            'flight_number'   => trim($_POST['flight_number'] ?? ''),
            'vehicle_id'      => $_POST['vehicle_id'] ?: null,
            'driver_id'       => $_POST['driver_id'] ?: null,
            'guide_id'        => $_POST['guide_id'] ?: null,
            'price'           => (float)($_POST['price'] ?? 0),
            'currency'        => $_POST['currency'] ?? 'USD',
            'status'          => $_POST['status'] ?? 'pending',
            'notes'           => trim($_POST['notes'] ?? ''),
        ];

        $id = ($_POST['id'] ?? 0);
        if ($id) {
            Voucher::update((int)$id, $data);
        } else {
            $id = Voucher::create($data);
        }

        header('Location: ' . url('vouchers') . '?saved=1');
        exit;
    }

    public function show(): void
    {
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);

        if (!$voucher) {
            header('Location: ' . url('vouchers'));
            exit;
        }

        $this->view('vouchers/show', [
            'voucher'    => $voucher,
            'pageTitle'  => 'Voucher: ' . $voucher['voucher_no'],
            'activePage' => 'vouchers',
        ]);
    }

    public function edit(): void
    {
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
            'pageTitle'  => 'Düzenle: ' . $voucher['voucher_no'],
            'activePage' => 'vouchers',
        ]);
    }

    public function delete(): void
    {
        require_once ROOT_PATH . '/src/Models/Voucher.php';
        $id = (int)($_GET['id'] ?? 0);
        if ($id) Voucher::delete($id);
        header('Location: ' . url('vouchers') . '?deleted=1');
        exit;
    }
}
