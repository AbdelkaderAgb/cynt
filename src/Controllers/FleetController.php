<?php
/**
 * CYN Tourism — FleetController (Drivers, Vehicles, Tour Guides)
 */
class FleetController extends Controller
{
    // ── Drivers ──────────────────────────────────────────
    public function drivers(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $filters = ['search' => $_GET['search'] ?? '', 'status' => $_GET['status'] ?? ''];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Fleet::getDrivers($filters, $page);

        $this->view('fleet/drivers', [
            'drivers'    => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => 'Şoför Yönetimi',
            'activePage' => 'drivers',
        ]);
    }

    public function driverForm(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $id = (int)($_GET['id'] ?? 0);
        $driver = $id ? Fleet::getDriver($id) : [];

        $this->view('fleet/driver_form', [
            'driver'     => $driver,
            'isEdit'     => (bool)$id,
            'pageTitle'  => $id ? 'Şoför Düzenle' : 'Yeni Şoför',
            'activePage' => 'drivers',
        ]);
    }

    public function driverStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $data = [
            'first_name'     => trim($_POST['first_name'] ?? ''),
            'last_name'      => trim($_POST['last_name'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'mobile'         => trim($_POST['mobile'] ?? ''),
            'email'          => trim($_POST['email'] ?? '') ?: null,
            'license_no'     => trim($_POST['license_no'] ?? ''),
            'license_expiry' => $_POST['license_expiry'] ?? null,
            'languages'      => trim($_POST['languages'] ?? ''),
            'status'         => $_POST['status'] ?? 'active',
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            header('Location: ' . url('drivers/form') . '?error=missing_name');
            exit;
        }

        Fleet::saveDriver($data, (int)($_POST['id'] ?? 0));
        header('Location: ' . url('drivers') . '?saved=1');
        exit;
    }

    public function driverDelete(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        Fleet::deleteDriver((int)($_GET['id'] ?? 0));
        header('Location: ' . url('drivers') . '?deleted=1');
        exit;
    }

    // ── Vehicles ─────────────────────────────────────────
    public function vehicles(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $filters = ['search' => $_GET['search'] ?? '', 'status' => $_GET['status'] ?? ''];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Fleet::getVehicles($filters, $page);

        $this->view('fleet/vehicles', [
            'vehicles'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => 'Araç Yönetimi',
            'activePage' => 'vehicles',
        ]);
    }

    public function vehicleForm(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $id = (int)($_GET['id'] ?? 0);
        $vehicle = $id ? Fleet::getVehicle($id) : [];

        $this->view('fleet/vehicle_form', [
            'vehicle'    => $vehicle,
            'isEdit'     => (bool)$id,
            'drivers'    => Fleet::getActiveDrivers(),
            'pageTitle'  => $id ? 'Araç Düzenle' : 'Yeni Araç',
            'activePage' => 'vehicles',
        ]);
    }

    public function vehicleStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $data = [
            'plate_number'       => trim($_POST['plate_number'] ?? ''),
            'make'               => trim($_POST['make'] ?? ''),
            'model'              => trim($_POST['model'] ?? ''),
            'year'               => (int)($_POST['year'] ?? date('Y')),
            'color'              => trim($_POST['color'] ?? ''),
            'capacity'           => (int)($_POST['capacity'] ?? 4),
            'vehicle_type'       => $_POST['vehicle_type'] ?? 'sedan',
            'fuel_type'          => $_POST['fuel_type'] ?? 'gasoline',
            'insurance_expiry'   => $_POST['insurance_expiry'] ?: null,
            'registration_expiry'=> $_POST['registration_expiry'] ?: null,
            'driver_id'          => $_POST['driver_id'] ?: null,
            'status'             => $_POST['status'] ?? 'available',
        ];

        if (empty($data['plate_number']) || empty($data['make'])) {
             header('Location: ' . url('vehicles/form') . '?error=missing_info');
             exit;
        }

        Fleet::saveVehicle($data, (int)($_POST['id'] ?? 0));
        header('Location: ' . url('vehicles') . '?saved=1');
        exit;
    }

    public function vehicleDelete(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        Fleet::deleteVehicle((int)($_GET['id'] ?? 0));
        header('Location: ' . url('vehicles') . '?deleted=1');
        exit;
    }

    // ── Tour Guides ──────────────────────────────────────
    public function guides(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $filters = ['search' => $_GET['search'] ?? '', 'status' => $_GET['status'] ?? ''];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Fleet::getGuides($filters, $page);

        $this->view('fleet/guides', [
            'guides'     => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'pages'      => $result['pages'],
            'filters'    => $filters,
            'pageTitle'  => 'Rehber Yönetimi',
            'activePage' => 'guides',
        ]);
    }

    public function guideForm(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $id = (int)($_GET['id'] ?? 0);
        $guide = $id ? Fleet::getGuide($id) : [];

        $this->view('fleet/guide_form', [
            'guide'      => $guide,
            'isEdit'     => (bool)$id,
            'pageTitle'  => $id ? 'Rehber Düzenle' : 'Yeni Rehber',
            'activePage' => 'guides',
        ]);
    }

    public function guideStore(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        $data = [
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'phone'           => trim($_POST['phone'] ?? ''),
            'email'           => trim($_POST['email'] ?? '') ?: null,
            'languages'       => trim($_POST['languages'] ?? ''),
            'specializations' => trim($_POST['specializations'] ?? ''),
            'experience_years'=> (int)($_POST['experience_years'] ?? 0),
            'daily_rate'      => (float)($_POST['daily_rate'] ?? 0),
            'currency'        => $_POST['currency'] ?? 'USD',
            'status'          => $_POST['status'] ?? 'active',
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            header('Location: ' . url('guides/form') . '?error=missing_name');
            exit;
        }
        Fleet::saveGuide($data, (int)($_POST['id'] ?? 0));
        header('Location: ' . url('guides') . '?saved=1');
        exit;
    }

    public function guideDelete(): void
    {
        require_once ROOT_PATH . '/src/Models/Fleet.php';
        Fleet::deleteGuide((int)($_GET['id'] ?? 0));
        header('Location: ' . url('guides') . '?deleted=1');
        exit;
    }
}
