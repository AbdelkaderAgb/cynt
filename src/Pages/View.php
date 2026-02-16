<?php
/**
 * CYN Tourism - View System (Consolidated)
 * Merged: view-transfer.php + view-tour.php + view-hotel.php + view-invoice.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

// Get type and ID
$type = $_GET['type'] ?? 'transfer';
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

// Load data based on type
$data = null;
$title = '';

switch ($type) {
    case 'transfer':
        $data = Database::fetchOne("SELECT * FROM vouchers WHERE id = ?", [$id]);
        $title = 'Transfer Detayi';
        $page = 'transfers';
        break;
    case 'tour':
        $data = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);
        $title = 'Tur Detayi';
        $page = 'tours';
        break;
    case 'hotel':
        $data = Database::fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$id]);
        $title = 'Otel Detayi';
        $page = 'hotels';
        break;
    case 'invoice':
        $data = Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]);
        $title = 'Fatura Detayi';
        $page = 'invoices';
        break;
    default:
        header('Location: index.php');
        exit;
}

if (!$data) {
    echo '<div class="alert alert-error">Kayit bulunamadi</div>';
    exit;
}

$pageTitle = $title;
$activePage = $page;
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo $title; ?></h1>
        <div class="page-actions">
            <a href="edit.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Duzenle
            </a>
            <a href="export.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>&format=pdf" class="btn btn-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Yazdir
            </button>
        </div>
    </div>
</div>

<div class="view-container">
    <?php if ($type == 'transfer'): ?>
    <!-- Transfer View -->
    <div class="view-card">
        <div class="view-header">
            <h2><?php echo htmlspecialchars($data['voucher_no']); ?></h2>
            <span class="status-badge status-<?php echo $data['status'] ?? 'confirmed'; ?>">
                <?php echo $data['status'] ?? 'Onaylandi'; ?>
            </span>
        </div>
        <div class="view-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Sirket</label>
                    <value><?php echo htmlspecialchars($data['company_name']); ?></value>
                </div>
                <div class="info-item">
                    <label>Otel</label>
                    <value><?php echo htmlspecialchars($data['hotel_name'] ?? '-'); ?></value>
                </div>
                <div class="info-item">
                    <label>Alis Yeri</label>
                    <value><?php echo htmlspecialchars($data['pickup_location']); ?></value>
                </div>
                <div class="info-item">
                    <label>Birakis Yeri</label>
                    <value><?php echo htmlspecialchars($data['dropoff_location']); ?></value>
                </div>
                <div class="info-item">
                    <label>Alis Tarihi</label>
                    <value><?php echo format_date($data['pickup_date']); ?></value>
                </div>
                <div class="info-item">
                    <label>Alis Saati</label>
                    <value><?php echo $data['pickup_time']; ?></value>
                </div>
                <div class="info-item">
                    <label>Donus Tarihi</label>
                    <value><?php echo $data['return_date'] ? format_date($data['return_date']) : '-'; ?></value>
                </div>
                <div class="info-item">
                    <label>Donus Saati</label>
                    <value><?php echo $data['return_time'] ?? '-'; ?></value>
                </div>
                <div class="info-item">
                    <label>Ucus Numarasi</label>
                    <value><?php echo htmlspecialchars($data['flight_number'] ?? '-'); ?></value>
                </div>
                <div class="info-item">
                    <label>Kisi Sayisi</label>
                    <value><?php echo $data['total_pax']; ?> kisi</value>
                </div>
                <div class="info-item full-width">
                    <label>Yolcu Isimleri</label>
                    <value><?php echo nl2br(htmlspecialchars($data['passengers'] ?? '-')); ?></value>
                </div>
                <?php if ($data['notes']): ?>
                <div class="info-item full-width">
                    <label>Notlar</label>
                    <value><?php echo nl2br(htmlspecialchars($data['notes'])); ?></value>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php elseif ($type == 'tour'): ?>
    <!-- Tour View -->
    <div class="view-card">
        <div class="view-header">
            <h2><?php echo htmlspecialchars($data['tour_name']); ?></h2>
            <span class="status-badge status-<?php echo $data['status'] ?? 'confirmed'; ?>">
                <?php echo $data['status'] ?? 'Onaylandi'; ?>
            </span>
        </div>
        <div class="view-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Sirket</label>
                    <value><?php echo htmlspecialchars($data['company_name']); ?></value>
                </div>
                <div class="info-item">
                    <label>Tur Tarihi</label>
                    <value><?php echo format_date($data['tour_date']); ?></value>
                </div>
                <div class="info-item">
                    <label>Bulusma Saati</label>
                    <value><?php echo $data['meeting_time']; ?></value>
                </div>
                <div class="info-item">
                    <label>Bulusma Yeri</label>
                    <value><?php echo htmlspecialchars($data['meeting_point']); ?></value>
                </div>
                <div class="info-item">
                    <label>Kisi Sayisi</label>
                    <value><?php echo $data['total_pax']; ?> kisi</value>
                </div>
                <div class="info-item">
                    <label>Rehber</label>
                    <value><?php echo htmlspecialchars($data['tour_guide_name'] ?? '-'); ?></value>
                </div>
                <div class="info-item">
                    <label>Arac</label>
                    <value><?php echo htmlspecialchars($data['vehicle_plate'] ?? '-'); ?></value>
                </div>
                <div class="info-item">
                    <label>Tutar</label>
                    <value><?php echo format_currency($data['total_amount'] ?? 0); ?></value>
                </div>
                <div class="info-item full-width">
                    <label>Yolcu Isimleri</label>
                    <value><?php echo nl2br(htmlspecialchars($data['passengers'] ?? '-')); ?></value>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($type == 'hotel'): ?>
    <!-- Hotel View -->
    <div class="view-card">
        <div class="view-header">
            <h2><?php echo htmlspecialchars($data['hotel_name']); ?></h2>
            <span class="status-badge status-<?php echo $data['status'] ?? 'confirmed'; ?>">
                <?php echo $data['status'] ?? 'Onaylandi'; ?>
            </span>
        </div>
        <div class="view-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Sirket</label>
                    <value><?php echo htmlspecialchars($data['company_name']); ?></value>
                </div>
                <div class="info-item">
                    <label>Giris Tarihi</label>
                    <value><?php echo format_date($data['check_in']); ?></value>
                </div>
                <div class="info-item">
                    <label>Cikis Tarihi</label>
                    <value><?php echo format_date($data['check_out']); ?></value>
                </div>
                <div class="info-item">
                    <label>Gece</label>
                    <value><?php echo calculate_nights($data['check_in'], $data['check_out']); ?> gece</value>
                </div>
                <div class="info-item">
                    <label>Oda Tipi</label>
                    <value><?php echo htmlspecialchars($data['room_type']); ?></value>
                </div>
                <div class="info-item">
                    <label>Yemek Plani</label>
                    <value><?php echo htmlspecialchars($data['meal_plan']); ?></value>
                </div>
                <div class="info-item">
                    <label>Kisi Sayisi</label>
                    <value><?php echo $data['total_pax']; ?> kisi</value>
                </div>
                <div class="info-item">
                    <label>Tutar</label>
                    <value><?php echo format_currency($data['total_amount'] ?? 0); ?></value>
                </div>
                <div class="info-item full-width">
                    <label>Yolcu Isimleri</label>
                    <value><?php echo nl2br(htmlspecialchars($data['passengers'] ?? '-')); ?></value>
                </div>
            </div>
        </div>
    </div>
    
    <?php elseif ($type == 'invoice'): ?>
    <!-- Invoice View -->
    <div class="view-card">
        <div class="view-header">
            <h2><?php echo htmlspecialchars($data['invoice_no']); ?></h2>
            <span class="status-badge status-<?php echo $data['status']; ?>">
                <?php echo $data['status'] == 'paid' ? 'Odendi' : 'Beklemede'; ?>
            </span>
        </div>
        <div class="view-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Sirket</label>
                    <value><?php echo htmlspecialchars($data['company_name']); ?></value>
                </div>
                <div class="info-item">
                    <label>Fatura Tarihi</label>
                    <value><?php echo format_date($data['created_at']); ?></value>
                </div>
                <div class="info-item">
                    <label>Tutar</label>
                    <value><?php echo format_currency($data['amount']); ?></value>
                </div>
                <div class="info-item">
                    <label>Toplam Tutar</label>
                    <value><?php echo format_currency($data['total_amount']); ?></value>
                </div>
                <div class="info-item">
                    <label>Durum</label>
                    <value><?php echo $data['status'] == 'paid' ? 'Odendi' : 'Beklemede'; ?></value>
                </div>
                <?php if ($data['notes']): ?>
                <div class="info-item full-width">
                    <label>Notlar</label>
                    <value><?php echo nl2br(htmlspecialchars($data['notes'])); ?></value>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.view-container { max-width: 900px; margin: 0 auto; }
.view-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
.view-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
.view-header h2 { margin: 0; }
.view-body { padding: 20px; }
.info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.info-item { display: flex; flex-direction: column; }
.info-item.full-width { grid-column: span 2; }
.info-item label { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
.info-item value { font-size: 16px; font-weight: 500; }
.status-badge { padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.status-confirmed, .status-paid { background: #d4edda; color: #155724; }
.status-pending { background: #fff3cd; color: #856404; }
.status-cancelled { background: #f8d7da; color: #721c24; }
</style>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
