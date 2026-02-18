<?php
/**
 * Service Controller — Manage Tours, Transfers with prices
 * Admin CRUD + AJAX search API for portal booking form
 * XLSX import for bulk tour/transfer pricing
 */
class ServiceController extends Controller
{
    /**
     * List all services with type filter
     */
    public function index(): void
    {
        Auth::requireAuth();
        
        $type = $_GET['type'] ?? '';
        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;
        
        $where = ['1=1'];
        $params = [];
        
        if ($type) {
            $where[] = 'service_type = ?';
            $params[] = $type;
        }
        if ($search) {
            $where[] = '(name LIKE ? OR description LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereStr = implode(' AND ', $where);
        $total = Database::fetchOne("SELECT COUNT(*) as cnt FROM services WHERE {$whereStr}", $params)['cnt'] ?? 0;
        $offset = ($page - 1) * $perPage;
        
        $services = Database::fetchAll(
            "SELECT * FROM services WHERE {$whereStr} ORDER BY service_type, name ASC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        // Count by type
        $counts = [
            'tour' => Database::fetchOne("SELECT COUNT(*) as c FROM services WHERE service_type = 'tour'")['c'] ?? 0,
            'transfer' => Database::fetchOne("SELECT COUNT(*) as c FROM services WHERE service_type = 'transfer'")['c'] ?? 0,
            'hotel' => Database::fetchOne("SELECT COUNT(*) as c FROM services WHERE service_type = 'hotel'")['c'] ?? 0,
        ];
        
        $this->view('services/index', [
            'services' => $services,
            'total' => $total,
            'counts' => $counts,
            'filters' => ['type' => $type, 'search' => $search],
            'page' => $page,
            'totalPages' => ceil($total / $perPage),
            'pageTitle' => 'Services & Pricing',
            'activePage' => 'services',
        ]);
    }
    
    /**
     * Show create form
     */
    public function create(): void
    {
        Auth::requireAuth();
        
        $this->view('services/form', [
            'service' => [
                'service_type' => $_GET['type'] ?? 'tour',
                'name' => '',
                'description' => '',
                'price' => 0,
                'price_adult' => 0,
                'price_child' => 0,
                'price_infant' => 0,
                'currency' => 'USD',
                'unit' => 'per_person',
                'details' => '',
                'destination' => '',
                'duration' => '',
                'vehicle_type' => '',
                'max_pax' => 0,
                'pickup_location' => '',
                'dropoff_location' => '',
                'status' => 'active',
            ],
            'isEdit' => false,
            'pageTitle' => 'Add New Service',
            'activePage' => 'services',
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit(): void
    {
        Auth::requireAuth();
        $id = intval($_GET['id'] ?? 0);
        $service = Database::fetchOne("SELECT * FROM services WHERE id = ?", [$id]);
        
        if (!$service) {
            $this->redirect('services');
            return;
        }
        
        $this->view('services/form', [
            'service' => $service,
            'isEdit' => true,
            'pageTitle' => 'Edit Service',
            'activePage' => 'services',
        ]);
    }
    
    /**
     * Store (create or update) a service
     */
    public function store(): void
    {
        Auth::requireAuth();
        $this->requireCsrf();
        
        $id = intval($_POST['id'] ?? 0);
        $serviceType = $_POST['service_type'] ?? 'tour';
        
        $data = [
            'service_type'    => $serviceType,
            'name'            => trim($_POST['name'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'price'           => floatval($_POST['price'] ?? 0),
            'price_adult'     => floatval($_POST['price_adult'] ?? 0),
            'price_child'     => floatval($_POST['price_child'] ?? 0),
            'price_infant'    => floatval($_POST['price_infant'] ?? 0),
            'currency'        => $_POST['currency'] ?? 'USD',
            'unit'            => $_POST['unit'] ?? 'per_person',
            'details'         => trim($_POST['details'] ?? ''),
            'destination'     => trim($_POST['destination'] ?? ''),
            'duration'        => trim($_POST['duration'] ?? ''),
            'vehicle_type'    => trim($_POST['vehicle_type'] ?? ''),
            'max_pax'         => intval($_POST['max_pax'] ?? 0),
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
            'dropoff_location'=> trim($_POST['dropoff_location'] ?? ''),
            'status'          => $_POST['status'] ?? 'active',
        ];

        // Sync price field: for tours use price_adult as main price
        if ($serviceType === 'tour' && $data['price_adult'] > 0 && $data['price'] == 0) {
            $data['price'] = $data['price_adult'];
        }
        
        if ($id > 0) {
            $sets = [];
            $params = [];
            foreach ($data as $k => $v) {
                $sets[] = "`{$k}` = ?";
                $params[] = $v;
            }
            $params[] = $id;
            Database::execute(
                "UPDATE services SET " . implode(', ', $sets) . " WHERE id = ?",
                $params
            );
        } else {
            $cols = array_keys($data);
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $colStr = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
            Database::execute(
                "INSERT INTO services ({$colStr}) VALUES ({$placeholders})",
                array_values($data)
            );
        }
        
        $this->redirect('services?type=' . $data['service_type'] . '&saved=1');
    }
    
    /**
     * Delete a service
     */
    public function delete(): void
    {
        Auth::requireAuth();
        $this->requireCsrf();
        $id = intval($_GET['id'] ?? intval($_POST['id'] ?? 0));
        
        if ($id > 0) {
            Database::execute("DELETE FROM services WHERE id = ?", [$id]);
        }
        
        $this->redirect('services');
    }
    
    /**
     * AJAX API: search services by type (returns JSON)
     * GET /api/services/search?type=tour&q=bosphorus
     * Returns full pricing details for invoice integration
     */
    public function searchApi(): void
    {
        $type = $_GET['type'] ?? '';
        $q = trim($_GET['q'] ?? '');
        
        $where = ['status = ?'];
        $params = ['active'];
        
        if ($type) {
            $where[] = 'service_type = ?';
            $params[] = $type;
        }
        if ($q) {
            $where[] = '(name LIKE ? OR description LIKE ? OR destination LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        
        $whereStr = implode(' AND ', $where);
        $services = Database::fetchAll(
            "SELECT id, service_type, name, description, price, price_adult, price_child, price_infant,
                    currency, unit, destination, duration, vehicle_type, max_pax, pickup_location, dropoff_location
             FROM services WHERE {$whereStr} ORDER BY name ASC LIMIT 50",
            $params
        );
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($services, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Import tours from XLSX file
     * POST /services/import-tours
     */
    public function importTours(): void
    {
        Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['xlsx_file']['tmp_name'])) {
            header('Location: ' . url('services') . '?type=tour&error=no_file');
            exit;
        }

        $file = $_FILES['xlsx_file']['tmp_name'];
        $imported = 0;

        try {
            $rows = $this->parseXlsx($file);

            if (empty($rows)) {
                header('Location: ' . url('services') . '?type=tour&error=empty_file');
                exit;
            }

            // Expected: Name, Description, Destination, Duration, Price Adult, Price Child, Price Infant, Currency, Unit, Status
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) < 5) continue;

                $name = trim($row[0] ?? '');
                if (empty($name)) continue;

                $data = [
                    'service_type'  => 'tour',
                    'name'          => $name,
                    'description'   => trim($row[1] ?? ''),
                    'destination'   => trim($row[2] ?? ''),
                    'duration'      => trim($row[3] ?? ''),
                    'price_adult'   => floatval($row[4] ?? 0),
                    'price_child'   => floatval($row[5] ?? 0),
                    'price_infant'  => floatval($row[6] ?? 0),
                    'price'         => floatval($row[4] ?? 0),
                    'currency'      => trim($row[7] ?? 'USD'),
                    'unit'          => trim($row[8] ?? 'per_person'),
                    'status'        => trim($row[9] ?? 'active'),
                ];

                $existing = Database::fetchOne(
                    "SELECT id FROM services WHERE name = ? AND service_type = 'tour'", [$name]
                );

                if ($existing) {
                    $sets = [];
                    $params = [];
                    foreach ($data as $k => $v) {
                        if ($k === 'service_type') continue;
                        $sets[] = "`{$k}` = ?";
                        $params[] = $v;
                    }
                    $params[] = $existing['id'];
                    Database::execute("UPDATE services SET " . implode(', ', $sets) . " WHERE id = ?", $params);
                } else {
                    $cols = array_keys($data);
                    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                    $colStr = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
                    Database::execute("INSERT INTO services ({$colStr}) VALUES ({$placeholders})", array_values($data));
                }
                $imported++;
            }
        } catch (\Exception $e) {
            header('Location: ' . url('services') . '?type=tour&error=' . urlencode($e->getMessage()));
            exit;
        }

        header('Location: ' . url('services') . '?type=tour&imported=' . $imported);
        exit;
    }

    /**
     * Import transfers from XLSX file
     * POST /services/import-transfers
     */
    public function importTransfers(): void
    {
        Auth::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['xlsx_file']['tmp_name'])) {
            header('Location: ' . url('services') . '?type=transfer&error=no_file');
            exit;
        }

        $file = $_FILES['xlsx_file']['tmp_name'];
        $imported = 0;

        try {
            $rows = $this->parseXlsx($file);

            if (empty($rows)) {
                header('Location: ' . url('services') . '?type=transfer&error=empty_file');
                exit;
            }

            // Expected: Name, Description, Pickup, Dropoff, Vehicle Type, Max Pax, Price, Currency, Unit, Status
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (count($row) < 7) continue;

                $name = trim($row[0] ?? '');
                if (empty($name)) continue;

                $data = [
                    'service_type'    => 'transfer',
                    'name'            => $name,
                    'description'     => trim($row[1] ?? ''),
                    'pickup_location' => trim($row[2] ?? ''),
                    'dropoff_location'=> trim($row[3] ?? ''),
                    'vehicle_type'    => trim($row[4] ?? ''),
                    'max_pax'         => intval($row[5] ?? 0),
                    'price'           => floatval($row[6] ?? 0),
                    'currency'        => trim($row[7] ?? 'USD'),
                    'unit'            => trim($row[8] ?? 'per_vehicle'),
                    'status'          => trim($row[9] ?? 'active'),
                ];

                $existing = Database::fetchOne(
                    "SELECT id FROM services WHERE name = ? AND service_type = 'transfer'", [$name]
                );

                if ($existing) {
                    $sets = [];
                    $params = [];
                    foreach ($data as $k => $v) {
                        if ($k === 'service_type') continue;
                        $sets[] = "`{$k}` = ?";
                        $params[] = $v;
                    }
                    $params[] = $existing['id'];
                    Database::execute("UPDATE services SET " . implode(', ', $sets) . " WHERE id = ?", $params);
                } else {
                    $cols = array_keys($data);
                    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
                    $colStr = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
                    Database::execute("INSERT INTO services ({$colStr}) VALUES ({$placeholders})", array_values($data));
                }
                $imported++;
            }
        } catch (\Exception $e) {
            header('Location: ' . url('services') . '?type=transfer&error=' . urlencode($e->getMessage()));
            exit;
        }

        header('Location: ' . url('services') . '?type=transfer&imported=' . $imported);
        exit;
    }

    /**
     * Parse XLSX file using ZipArchive + SimpleXML (no external library)
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Cannot open XLSX file');
        }

        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            foreach ($ss->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception('No sheet found in XLSX');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $value = (string)$cell->v;
                $type = (string)$cell['t'];
                if ($type === 's' && isset($sharedStrings[(int)$value])) {
                    $value = $sharedStrings[(int)$value];
                }
                $rowData[] = $value;
            }
            $rows[] = $rowData;
        }

        $zip->close();
        return $rows;
    }
}
