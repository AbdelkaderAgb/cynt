<?php
/**
 * CYN Tourism - Management System (Consolidated)
 * Merged: partners.php + drivers.php + vehicles.php + tour-guides.php + users.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

// Get management type
$type = $_GET['type'] ?? 'partners';

// Define configurations for each type
$configs = [
    'partners' => [
        'title' => 'Partnerler',
        'table' => 'partners',
        'fields' => ['company' => 'Sirket', 'email' => 'E-posta', 'phone' => 'Telefon', 'status' => 'Durum'],
        'form' => ['company' => 'text', 'contact_name' => 'text', 'email' => 'email', 'phone' => 'text', 'address' => 'textarea', 'status' => 'select'],
        'required' => ['company']
    ],
    'drivers' => [
        'title' => 'Soforler',
        'table' => 'drivers',
        'fields' => ['name' => 'Ad', 'phone' => 'Telefon', 'license_no' => 'Ehliyet No', 'status' => 'Durum'],
        'form' => ['name' => 'text', 'phone' => 'text', 'license_no' => 'text', 'status' => 'select'],
        'required' => ['name']
    ],
    'vehicles' => [
        'title' => 'Araclar',
        'table' => 'vehicles',
        'fields' => ['plate_number' => 'Plaka', 'model' => 'Model', 'capacity' => 'Kapasite', 'status' => 'Durum'],
        'form' => ['plate_number' => 'text', 'model' => 'text', 'capacity' => 'number', 'status' => 'select'],
        'required' => ['plate_number']
    ],
    'guides' => [
        'title' => 'Rehberler',
        'table' => 'tour_guides',
        'fields' => ['name' => 'Ad', 'phone' => 'Telefon', 'languages' => 'Diller', 'status' => 'Durum'],
        'form' => ['name' => 'text', 'phone' => 'text', 'languages' => 'text', 'status' => 'select'],
        'required' => ['name']
    ],
    'users' => [
        'title' => 'Kullanicilar',
        'table' => 'users',
        'fields' => ['first_name' => 'Ad', 'last_name' => 'Soyad', 'email' => 'E-posta', 'role' => 'Rol', 'status' => 'Durum'],
        'form' => ['first_name' => 'text', 'last_name' => 'text', 'email' => 'email', 'password' => 'password', 'role' => 'select', 'status' => 'select'],
        'required' => ['first_name', 'last_name', 'email']
    ]
];

$config = $configs[$type] ?? $configs['partners'];
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formData = array_map('trim', $_POST);
    
    if ($action == 'save') {
        $fields = [];
        $values = [];
        $params = [];
        
        foreach ($config['form'] as $field => $ftype) {
            if ($field == 'password' && empty($formData[$field])) continue;
            if (isset($formData[$field])) {
                $fields[] = $field;
                $values[] = '?';
                $params[] = $formData[$field];
            }
        }
        
        if ($id) {
            // Update
            $setClause = implode(' = ?, ', $fields) . ' = ?';
            $params[] = $id;
            Database::execute("UPDATE {$config['table']} SET $setClause WHERE id = ?", $params);
        } else {
            // Insert
            $fieldsStr = implode(', ', $fields);
            $valuesStr = implode(', ', $values);
            Database::execute("INSERT INTO {$config['table']} ($fieldsStr) VALUES ($valuesStr)", $params);
        }
        
        header("Location: management.php?type=$type&saved=1");
        exit;
    }
}

// Handle delete
if ($action == 'delete' && $id) {
    Database::execute("DELETE FROM {$config['table']} WHERE id = ?", [$id]);
    header("Location: management.php?type=$type&deleted=1");
    exit;
}

// Load data for edit
$editData = null;
if ($action == 'edit' && $id) {
    $editData = Database::fetchOne("SELECT * FROM {$config['table']} WHERE id = ?", [$id]);
}

// Load list
$list = Database::fetchAll("SELECT * FROM {$config['table']} ORDER BY id DESC");

$pageTitle = $config['title'];
$activePage = $type;
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo $config['title']; ?></h1>
        <div class="page-actions">
            <a href="management.php?type=<?php echo $type; ?>&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Ekle
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success">Kayit basariyla kaydedildi</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success">Kayit basariyla silindi</div>
<?php endif; ?>

<?php if ($action == 'add' || $action == 'edit'): ?>
<!-- Form -->
<div class="form-container">
    <form method="post" action="management.php?type=<?php echo $type; ?>&action=save&id=<?php echo $id; ?>" class="form">
        <?php foreach ($config['form'] as $field => $ftype): ?>
        <div class="form-group">
            <label><?php echo ucfirst($field); ?> <?php echo in_array($field, $config['required']) ? '*' : ''; ?></label>
            <?php if ($ftype == 'textarea'): ?>
            <textarea name="<?php echo $field; ?>" <?php echo in_array($field, $config['required']) ? 'required' : ''; ?>><?php echo htmlspecialchars($editData[$field] ?? ''); ?></textarea>
            <?php elseif ($ftype == 'select'): ?>
            <select name="<?php echo $field; ?>" <?php echo in_array($field, $config['required']) ? 'required' : ''; ?>>
                <?php if ($field == 'status'): ?>
                <option value="active" <?php echo ($editData[$field] ?? '') == 'active' ? 'selected' : ''; ?>>Aktif</option>
                <option value="inactive" <?php echo ($editData[$field] ?? '') == 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                <?php elseif ($field == 'role'): ?>
                <option value="admin" <?php echo ($editData[$field] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="manager" <?php echo ($editData[$field] ?? '') == 'manager' ? 'selected' : ''; ?>>Yonetici</option>
                <option value="operator" <?php echo ($editData[$field] ?? '') == 'operator' ? 'selected' : ''; ?>>Operator</option>
                <?php endif; ?>
            </select>
            <?php else: ?>
            <input type="<?php echo $ftype; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($editData[$field] ?? ''); ?>" <?php echo in_array($field, $config['required']) ? 'required' : ''; ?>>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="management.php?type=<?php echo $type; ?>" class="btn btn-secondary">Iptal</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <?php foreach ($config['fields'] as $key => $label): ?>
                        <th><?php echo $label; ?></th>
                        <?php endforeach; ?>
                        <th>Islemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                    <tr>
                        <?php foreach ($config['fields'] as $key => $label): ?>
                        <td>
                            <?php if ($key == 'status'): ?>
                            <span class="status-badge status-<?php echo $item[$key]; ?>"><?php echo $item[$key] == 'active' ? 'Aktif' : 'Pasif'; ?></span>
                            <?php else: ?>
                            <?php echo htmlspecialchars($item[$key] ?? '-'); ?>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td>
                            <a href="management.php?type=<?php echo $type; ?>&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <a href="management.php?type=<?php echo $type; ?>&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediginize emin misiniz?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
.form-actions { display: flex; gap: 10px; margin-top: 30px; }
.alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
.alert-success { background: #d4edda; color: #155724; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; }
.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }
</style>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
