<?php
/**
 * CYN Tourism - Forms System (Consolidated)
 * Merged: transfer-voucher-form.php + transfer-invoice-form.php + tour-voucher-form.php + hotel-invoice-form.php + receipt-form.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

$type = $_GET['type'] ?? 'transfer';
$id = intval($_GET['id'] ?? 0);
$isNew = ($id == 0);

// Form configurations
$forms = [
    'transfer' => [
        'title' => 'Transfer Voucher',
        'table' => 'vouchers',
        'fields' => [
            'company_name' => ['label' => 'Sirket', 'type' => 'text', 'required' => true],
            'hotel_name' => ['label' => 'Otel', 'type' => 'text'],
            'pickup_location' => ['label' => 'Alis Yeri', 'type' => 'text', 'required' => true],
            'dropoff_location' => ['label' => 'Birakis Yeri', 'type' => 'text', 'required' => true],
            'pickup_date' => ['label' => 'Alis Tarihi', 'type' => 'date', 'required' => true],
            'pickup_time' => ['label' => 'Alis Saati', 'type' => 'time', 'required' => true],
            'return_date' => ['label' => 'Donus Tarihi', 'type' => 'date'],
            'return_time' => ['label' => 'Donus Saati', 'type' => 'time'],
            'flight_number' => ['label' => 'Ucus Numarasi', 'type' => 'text'],
            'total_pax' => ['label' => 'Kisi Sayisi', 'type' => 'number', 'default' => 1],
            'passengers' => ['label' => 'Yolcu Isimleri', 'type' => 'textarea'],
            'notes' => ['label' => 'Notlar', 'type' => 'textarea']
        ]
    ],
    'tour' => [
        'title' => 'Tur Voucher',
        'table' => 'tours',
        'fields' => [
            'company_name' => ['label' => 'Sirket', 'type' => 'text', 'required' => true],
            'tour_name' => ['label' => 'Tur Adi', 'type' => 'text', 'required' => true],
            'tour_date' => ['label' => 'Tur Tarihi', 'type' => 'date', 'required' => true],
            'meeting_time' => ['label' => 'Bulusma Saati', 'type' => 'time', 'required' => true],
            'meeting_point' => ['label' => 'Bulusma Yeri', 'type' => 'text', 'required' => true],
            'total_pax' => ['label' => 'Kisi Sayisi', 'type' => 'number', 'default' => 1],
            'passengers' => ['label' => 'Yolcu Isimleri', 'type' => 'textarea'],
            'tour_guide_name' => ['label' => 'Rehber', 'type' => 'text'],
            'vehicle_plate' => ['label' => 'Arac Plaka', 'type' => 'text'],
            'total_amount' => ['label' => 'Tutar', 'type' => 'number', 'step' => '0.01'],
            'notes' => ['label' => 'Notlar', 'type' => 'textarea']
        ]
    ],
    'hotel' => [
        'title' => 'Otel Voucher',
        'table' => 'hotel_vouchers',
        'fields' => [
            'company_name' => ['label' => 'Sirket', 'type' => 'text', 'required' => true],
            'hotel_name' => ['label' => 'Otel Adi', 'type' => 'text', 'required' => true],
            'check_in' => ['label' => 'Giris Tarihi', 'type' => 'date', 'required' => true],
            'check_out' => ['label' => 'Cikis Tarihi', 'type' => 'date', 'required' => true],
            'room_type' => ['label' => 'Oda Tipi', 'type' => 'text', 'required' => true],
            'meal_plan' => ['label' => 'Yemek Plani', 'type' => 'text'],
            'total_pax' => ['label' => 'Kisi Sayisi', 'type' => 'number', 'default' => 1],
            'passengers' => ['label' => 'Yolcu Isimleri', 'type' => 'textarea'],
            'total_amount' => ['label' => 'Tutar', 'type' => 'number', 'step' => '0.01'],
            'notes' => ['label' => 'Notlar', 'type' => 'textarea']
        ]
    ],
    'invoice' => [
        'title' => 'Fatura',
        'table' => 'invoices',
        'fields' => [
            'company_name' => ['label' => 'Sirket', 'type' => 'text', 'required' => true],
            'invoice_no' => ['label' => 'Fatura No', 'type' => 'text', 'required' => true],
            'amount' => ['label' => 'Tutar', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'total_amount' => ['label' => 'Toplam Tutar', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'status' => ['label' => 'Durum', 'type' => 'select', 'options' => ['pending' => 'Beklemede', 'paid' => 'Odendi', 'cancelled' => 'Iptal']],
            'notes' => ['label' => 'Notlar', 'type' => 'textarea']
        ]
    ],
    'receipt' => [
        'title' => 'Dekont',
        'table' => 'receipts',
        'fields' => [
            'company_name' => ['label' => 'Sirket', 'type' => 'text', 'required' => true],
            'receipt_no' => ['label' => 'Dekont No', 'type' => 'text', 'required' => true],
            'amount' => ['label' => 'Tutar', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'payment_method' => ['label' => 'Odeme Sekli', 'type' => 'select', 'options' => ['cash' => 'Nakit', 'card' => 'Kredi Karti', 'transfer' => 'Havale']],
            'notes' => ['label' => 'Notlar', 'type' => 'textarea']
        ]
    ]
];

$form = $forms[$type] ?? $forms['transfer'];
$errors = [];

// Load existing data
$data = [];
if (!$isNew) {
    $data = Database::fetchOne("SELECT * FROM {$form['table']} WHERE id = ?", [$id]);
}

// Process form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formData = [];
    foreach ($form['fields'] as $field => $config) {
        $formData[$field] = trim($_POST[$field] ?? '');
        if (($config['required'] ?? false) && empty($formData[$field])) {
            $errors[] = $config['label'] . ' zorunludur';
        }
    }
    
    if (empty($errors)) {
        $fields = [];
        $placeholders = [];
        $values = [];
        
        foreach ($formData as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }
        
        if ($isNew) {
            if ($type == 'transfer') {
                $fields[] = 'voucher_no';
                $placeholders[] = '?';
                $values[] = generate_voucher_no();
            }
            $sql = "INSERT INTO {$form['table']} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        } else {
            $set = [];
            foreach ($fields as $field) {
                $set[] = "$field = ?";
            }
            $values[] = $id;
            $sql = "UPDATE {$form['table']} SET " . implode(', ', $set) . " WHERE id = ?";
        }
        
        Database::execute($sql, $values);
        header("Location: forms.php?type=$type&saved=1");
        exit;
    }
}

$pageTitle = ($isNew ? 'Yeni ' : 'Duzenle: ') . $form['title'];
$activePage = $type;
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <div class="page-actions">
            <a href="forms.php?type=<?php echo $type; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Liste</a>
        </div>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error"><?php echo implode('<br>', $errors); ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="form">
        <div class="form-grid">
            <?php foreach ($form['fields'] as $field => $config): ?>
            <div class="form-group <?php echo $config['type'] == 'textarea' ? 'full-width' : ''; ?>">
                <label><?php echo $config['label']; ?> <?php echo $config['required'] ? '*' : ''; ?></label>
                <?php if ($config['type'] == 'textarea'): ?>
                <textarea name="<?php echo $field; ?>" rows="4" <?php echo $config['required'] ? 'required' : ''; ?>><?php echo htmlspecialchars($data[$field] ?? ''); ?></textarea>
                <?php elseif ($config['type'] == 'select'): ?>
                <select name="<?php echo $field; ?>" <?php echo $config['required'] ? 'required' : ''; ?>>
                    <?php foreach ($config['options'] as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo ($data[$field] ?? '') == $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="<?php echo $config['type']; ?>" name="<?php echo $field; ?>" 
                       value="<?php echo htmlspecialchars($data[$field] ?? $config['default'] ?? ''); ?>" 
                       <?php echo $config['required'] ? 'required' : ''; ?>
                       <?php echo isset($config['step']) ? 'step="' . $config['step'] . '"' : ''; ?>>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            <a href="forms.php?type=<?php echo $type; ?>" class="btn btn-secondary">Iptal</a>
        </div>
    </form>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success">Kayit basariyla kaydedildi</div>
<?php endif; ?>

<style>
.form-container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.form-group { margin-bottom: 20px; }
.form-group.full-width { grid-column: span 2; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
.form-actions { display: flex; gap: 10px; margin-top: 30px; }
.alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
.alert-error { background: #f8d7da; color: #721c24; }
.alert-success { background: #d4edda; color: #155724; }
</style>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
