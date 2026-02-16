<?php
/**
 * CYN Tourism - Export System (Consolidated)
 * Merged: export-excel.php + export-pdf.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

$type = $_GET['type'] ?? 'transfer';
$id = intval($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'pdf';

if (!$id) {
    header('Location: index.php');
    exit;
}

// Load data
$data = null;
switch ($type) {
    case 'transfer':
        $data = Database::fetchOne("SELECT * FROM vouchers WHERE id = ?", [$id]);
        $title = 'Transfer Voucher';
        break;
    case 'tour':
        $data = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);
        $title = 'Tour Voucher';
        break;
    case 'hotel':
        $data = Database::fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$id]);
        $title = 'Hotel Voucher';
        break;
    case 'invoice':
        $data = Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]);
        $title = 'Invoice';
        break;
    default:
        header('Location: index.php');
        exit;
}

if (!$data) {
    die('Kayit bulunamadi');
}

if ($format == 'excel') {
    // Excel Export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $type . '_' . $id . '.xls"');
    header('Pragma: no-cache');
    
    echo "<table border='1'>";
    echo "<tr><th colspan='2'>$title</th></tr>";
    foreach ($data as $key => $value) {
        echo "<tr><td>" . ucfirst(str_replace('_', ' ', $key)) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    exit;
} else {
    // PDF Export (HTML format for printing)
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title; ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
            .header h1 { margin: 0; }
            .header p { color: #666; }
            .content { margin-top: 30px; }
            .info-row { display: flex; border-bottom: 1px solid #ddd; padding: 10px 0; }
            .info-label { width: 200px; font-weight: bold; }
            .info-value { flex: 1; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?php echo defined('COMPANY_NAME') ? COMPANY_NAME : 'CYN TURIZM'; ?></h1>
            <p><?php echo $title; ?></p>
        </div>
        
        <div class="content">
            <?php foreach ($data as $key => $value): ?>
            <?php if (!empty($value) && $key != 'id' && $key != 'created_at' && $key != 'updated_at'): ?>
            <div class="info-row">
                <div class="info-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($value)); ?></div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="footer">
            <p>Olusturma Tarihi: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        
        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()">Yazdir</button>
            <button onclick="window.close()">Kapat</button>
        </div>
    </body>
    </html>
    <?php
    exit;
}
