<?php
/**
 * Service Controller — Manage Tours, Transfers with prices
 * Admin CRUD + AJAX search API for portal booking form
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
                'currency' => 'USD',
                'unit' => 'per_person',
                'details' => '',
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
        
        $id = intval($_POST['id'] ?? 0);
        $data = [
            'service_type' => $_POST['service_type'] ?? 'tour',
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'currency' => $_POST['currency'] ?? 'USD',
            'unit' => $_POST['unit'] ?? 'per_person',
            'details' => trim($_POST['details'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
        ];
        
        if ($id > 0) {
            // Update
            Database::execute(
                "UPDATE services SET service_type = ?, name = ?, description = ?, price = ?, currency = ?, unit = ?, details = ?, status = ? WHERE id = ?",
                [$data['service_type'], $data['name'], $data['description'], $data['price'], $data['currency'], $data['unit'], $data['details'], $data['status'], $id]
            );
        } else {
            // Insert
            Database::execute(
                "INSERT INTO services (service_type, name, description, price, currency, unit, details, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$data['service_type'], $data['name'], $data['description'], $data['price'], $data['currency'], $data['unit'], $data['details'], $data['status']]
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
        $id = intval($_GET['id'] ?? intval($_POST['id'] ?? 0));
        
        if ($id > 0) {
            Database::execute("DELETE FROM services WHERE id = ?", [$id]);
        }
        
        $this->redirect('services');
    }
    
    /**
     * AJAX API: search services by type (returns JSON)
     * Used by portal booking form
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
            $where[] = '(name LIKE ? OR description LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        
        $whereStr = implode(' AND ', $where);
        $services = Database::fetchAll(
            "SELECT id, service_type, name, description, price, currency, unit FROM services WHERE {$whereStr} ORDER BY name ASC LIMIT 50",
            $params
        );
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($services, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
