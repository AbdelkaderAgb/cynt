<?php
/**
 * CYN Tourism - Edit System (Consolidated)
 * Merged: edit-transfer.php + edit-tour.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

// Get type and ID
$type = $_GET['type'] ?? 'transfer';
$id = intval($_GET['id'] ?? 0);
$isNew = ($id == 0);

// Initialize data
$data = [];
$errors = [];
$success = false;

// Load existing data if editing
if (!$isNew) {
    switch ($type) {
        case 'transfer':
            $data = Database::fetchOne("SELECT * FROM vouchers WHERE id = ?", [$id]);
            break;
        case 'tour':
            $data = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);
            break;
        case 'hotel':
            $data = Database::fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$id]);
            break;
    }
    if (!$data) {
        header('Location: index.php');
        exit;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Gecersiz istek';
    } else {
        // Get form data
        $formData = array_map('trim', $_POST);
        
        // Validate required fields
        $required = ['company_name'];
        foreach ($required as $field) {
            if (empty($formData[$field])) {
                $errors[] = ucfirst($field) . ' zorunludur';
            }
        }
        
        if (empty($errors)) {
            try {
                if ($type == 'transfer') {
                    if ($isNew) {
                        $voucherNo = generate_voucher_no();
                        Database::execute(
                            "INSERT INTO vouchers (voucher_no, company_name, hotel_name, pickup_location, dropoff_location, 
                             pickup_date, pickup_time, return_date, return_time, flight_number, total_pax, passengers, notes) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $voucherNo, $formData['company_name'], $formData['hotel_name'] ?? '',
                                $formData['pickup_location'], $formData['dropoff_location'],
                                $formData['pickup_date'], $formData['pickup_time'],
                                $formData['return_date'] ?? null, $formData['return_time'] ?? null,
                                $formData['flight_number'] ?? '', $formData['total_pax'] ?? 1,
                                $formData['passengers'] ?? '', $formData['notes'] ?? ''
                            ]
                        );
                        $id = Database::getInstance()->lastInsertId();
                    } else {
                        Database::execute(
                            "UPDATE vouchers SET company_name = ?, hotel_name = ?, pickup_location = ?, dropoff_location = ?,
                             pickup_date = ?, pickup_time = ?, return_date = ?, return_time = ?, flight_number = ?,
                             total_pax = ?, passengers = ?, notes = ? WHERE id = ?",
                            [
                                $formData['company_name'], $formData['hotel_name'] ?? '',
                                $formData['pickup_location'], $formData['dropoff_location'],
                                $formData['pickup_date'], $formData['pickup_time'],
                                $formData['return_date'] ?? null, $formData['return_time'] ?? null,
                                $formData['flight_number'] ?? '', $formData['total_pax'] ?? 1,
                                $formData['passengers'] ?? '', $formData['notes'] ?? '', $id
                            ]
                        );
                    }
                } elseif ($type == 'tour') {
                    if ($isNew) {
                        Database::execute(
                            "INSERT INTO tours (company_name, tour_name, tour_date, meeting_time, meeting_point, 
                             total_pax, passengers, tour_guide_name, vehicle_plate, total_amount, notes) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $formData['company_name'], $formData['tour_name'], $formData['tour_date'],
                                $formData['meeting_time'], $formData['meeting_point'],
                                $formData['total_pax'] ?? 1, $formData['passengers'] ?? '',
                                $formData['tour_guide_name'] ?? '', $formData['vehicle_plate'] ?? '',
                                $formData['total_amount'] ?? 0, $formData['notes'] ?? ''
                            ]
                        );
                        $id = Database::getInstance()->lastInsertId();
                    } else {
                        Database::execute(
                            "UPDATE tours SET company_name = ?, tour_name = ?, tour_date = ?, meeting_time = ?,
                             meeting_point = ?, total_pax = ?, passengers = ?, tour_guide_name = ?, vehicle_plate = ?,
                             total_amount = ?, notes = ? WHERE id = ?",
                            [
                                $formData['company_name'], $formData['tour_name'], $formData['tour_date'],
                                $formData['meeting_time'], $formData['meeting_point'],
                                $formData['total_pax'] ?? 1, $formData['passengers'] ?? '',
                                $formData['tour_guide_name'] ?? '', $formData['vehicle_plate'] ?? '',
                                $formData['total_amount'] ?? 0, $formData['notes'] ?? '', $id
                            ]
                        );
                    }
                }
                
                $success = true;
                header("Location: view.php?type=$type&id=$id&saved=1");
                exit;
                
            } catch (Exception $e) {
                $errors[] = 'Kayit hatasi: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = ($isNew ? 'Yeni ' : 'Duzenle: ') . ($type == 'transfer' ? 'Transfer' : 'Tur');
$activePage = $type == 'transfer' ? 'transfers' : 'tours';
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo $pageTitle; ?></h1>
        <div class="page-actions">
            <a href="<?php echo $type == 'transfer' ? 'transfer-voucher-form.php' : 'tour-voucher-form.php'; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Geri
            </a>
        </div>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <?php foreach ($errors as $error): ?>
    <p><?php echo $error; ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <?php if ($type == 'transfer'): ?>
        <!-- Transfer Form -->
        <div class="form-row">
            <div class="form-group">
                <label>Sirket *</label>
                <input type="text" name="company_name" value="<?php echo htmlspecialchars($data['company_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Otel</label>
                <input type="text" name="hotel_name" value="<?php echo htmlspecialchars($data['hotel_name'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Alis Yeri *</label>
                <input type="text" name="pickup_location" value="<?php echo htmlspecialchars($data['pickup_location'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Birakis Yeri *</label>
                <input type="text" name="dropoff_location" value="<?php echo htmlspecialchars($data['dropoff_location'] ?? ''); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Alis Tarihi *</label>
                <input type="date" name="pickup_date" value="<?php echo $data['pickup_date'] ?? date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Alis Saati *</label>
                <input type="time" name="pickup_time" value="<?php echo $data['pickup_time'] ?? '10:00'; ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Donus Tarihi</label>
                <input type="date" name="return_date" value="<?php echo $data['return_date'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label>Donus Saati</label>
                <input type="time" name="return_time" value="<?php echo $data['return_time'] ?? ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Ucus Numarasi</label>
                <input type="text" name="flight_number" value="<?php echo htmlspecialchars($data['flight_number'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Kisi Sayisi</label>
                <input type="number" name="total_pax" value="<?php echo $data['total_pax'] ?? 1; ?>" min="1">
            </div>
        </div>
        
        <div class="form-group">
            <label>Yolcu Isimleri</label>
            <textarea name="passengers" rows="4"><?php echo htmlspecialchars($data['passengers'] ?? ''); ?></textarea>
        </div>
        
        <?php else: ?>
        <!-- Tour Form -->
        <div class="form-row">
            <div class="form-group">
                <label>Sirket *</label>
                <input type="text" name="company_name" value="<?php echo htmlspecialchars($data['company_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Tur Adi *</label>
                <input type="text" name="tour_name" value="<?php echo htmlspecialchars($data['tour_name'] ?? ''); ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Tur Tarihi *</label>
                <input type="date" name="tour_date" value="<?php echo $data['tour_date'] ?? date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Bulusma Saati *</label>
                <input type="time" name="meeting_time" value="<?php echo $data['meeting_time'] ?? '09:00'; ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Bulusma Yeri *</label>
            <input type="text" name="meeting_point" value="<?php echo htmlspecialchars($data['meeting_point'] ?? ''); ?>" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Kisi Sayisi</label>
                <input type="number" name="total_pax" value="<?php echo $data['total_pax'] ?? 1; ?>" min="1">
            </div>
            <div class="form-group">
                <label>Tutar</label>
                <input type="number" name="total_amount" value="<?php echo $data['total_amount'] ?? 0; ?>" step="0.01">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Rehber</label>
                <input type="text" name="tour_guide_name" value="<?php echo htmlspecialchars($data['tour_guide_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Arac Plaka</label>
                <input type="text" name="vehicle_plate" value="<?php echo htmlspecialchars($data['vehicle_plate'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Yolcu Isimleri</label>
            <textarea name="passengers" rows="4"><?php echo htmlspecialchars($data['passengers'] ?? ''); ?></textarea>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Notlar</label>
            <textarea name="notes" rows="3"><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $isNew ? 'Olustur' : 'Kaydet'; ?>
            </button>
            <a href="<?php echo $type == 'transfer' ? 'transfer-voucher-form.php' : 'tour-voucher-form.php'; ?>" class="btn btn-secondary">Iptal</a>
        </div>
    </form>
</div>

<style>
.form-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
.form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
.form-group input:focus, .form-group textarea:focus { border-color: #2196f3; outline: none; }
.form-actions { display: flex; gap: 10px; margin-top: 30px; }
.alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
.alert-error { background: #f8d7da; color: #721c24; }
</style>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
