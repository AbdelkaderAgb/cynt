<?php
/**
 * CYN Tourism — TaxController
 * CRUD for tax rates (admin settings page).
 * Uses existing tax_rates table.
 */
class TaxController extends Controller
{
    /**
     * List tax rates
     * GET /settings/tax-rates
     */
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM tax_rates ORDER BY applies_to, name");
        $rates = $stmt->fetchAll();

        $this->view('settings/tax_rates', [
            'rates'      => $rates,
            'pageTitle'  => 'Tax Rates',
            'activePage' => 'settings',
        ]);
    }

    /**
     * Store / update tax rate
     * POST /settings/tax-rates/store
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $db = Database::getInstance()->getConnection();

        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $rate      = (float)($_POST['rate'] ?? 0);
        $country   = trim($_POST['country'] ?? '');
        $appliesTo = $_POST['applies_to'] ?? 'all';
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        $status    = $_POST['status'] ?? 'active';

        if (!$name) {
            header('Location: ' . url('settings/tax-rates') . '?error=1');
            exit;
        }

        // If marking as default, clear other defaults for same applies_to
        if ($isDefault) {
            $db->prepare("UPDATE tax_rates SET is_default = 0 WHERE applies_to = ?")->execute([$appliesTo]);
        }

        if ($id) {
            $stmt = $db->prepare(
                "UPDATE tax_rates SET name = ?, rate = ?, country = ?, applies_to = ?, is_default = ?, status = ? WHERE id = ?"
            );
            $stmt->execute([$name, $rate, $country, $appliesTo, $isDefault, $status, $id]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO tax_rates (name, rate, country, applies_to, is_default, status) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $rate, $country, $appliesTo, $isDefault, $status]);
        }

        header('Location: ' . url('settings/tax-rates') . '?saved=1');
        exit;
    }

    /**
     * Delete a tax rate
     * GET /settings/tax-rates/delete?id=X
     */
    public function delete(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM tax_rates WHERE id = ?")->execute([$id]);
        }

        header('Location: ' . url('settings/tax-rates') . '?deleted=1');
        exit;
    }

    /**
     * API: Get default tax rate for a service type
     * GET /api/tax/default?type=hotel
     */
    public function defaultApi(): void
    {
        $db = Database::getInstance()->getConnection();
        $type = $_GET['type'] ?? 'all';

        $stmt = $db->prepare(
            "SELECT * FROM tax_rates 
             WHERE status = 'active' AND is_default = 1 
             AND (applies_to = ? OR applies_to = 'all')
             ORDER BY applies_to DESC
             LIMIT 1"
        );
        $stmt->execute([$type]);
        $rate = $stmt->fetch();

        $this->jsonResponse([
            'success' => true,
            'rate'    => $rate ?: ['rate' => 0, 'name' => 'None'],
        ]);
    }
}
