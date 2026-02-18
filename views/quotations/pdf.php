<?php
/**
 * Quotation PDF Template — No prices on voucher (this IS a quotation so prices shown)
 * Rendered standalone via Dompdf
 */
$typeLabels = ['hotel' => 'Hotel', 'tour' => 'Tour', 'transfer' => 'Transfer', 'other' => 'Other'];

// Group items by day
$days = [];
foreach ($items as $item) {
    $d = (int)($item['day_number'] ?? 1);
    $days[$d][] = $item;
}
ksort($days);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation <?= e($q['quote_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .page { padding: 30px 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #f97316; padding-bottom: 15px; }
        .header h1 { font-size: 22px; color: #f97316; font-weight: 800; letter-spacing: -0.5px; }
        .header .meta { text-align: right; font-size: 10px; color: #64748b; }
        .header .meta strong { color: #1e293b; font-size: 12px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: 700; color: #f97316; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #fed7aa; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 120px; font-size: 10px; color: #64748b; padding: 3px 0; font-weight: 600; }
        .info-value { display: table-cell; font-size: 11px; color: #1e293b; padding: 3px 0; }
        .day-header { background: #fff7ed; padding: 6px 10px; font-size: 11px; font-weight: 700; color: #ea580c; border-left: 3px solid #f97316; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table th { background: #f8fafc; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; padding: 6px 8px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        table td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        table td.num { text-align: right; font-family: 'Courier New', monospace; }
        .totals { width: 250px; margin-left: auto; margin-top: 15px; }
        .totals tr td { padding: 4px 8px; font-size: 11px; }
        .totals tr td:last-child { text-align: right; font-family: 'Courier New', monospace; }
        .totals .grand { font-size: 14px; font-weight: 800; color: #f97316; border-top: 2px solid #f97316; }
        .terms { margin-top: 25px; padding: 15px; background: #fafafa; border: 1px solid #e2e8f0; border-radius: 4px; }
        .terms h4 { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .terms p { font-size: 10px; color: #475569; white-space: pre-wrap; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-type { background: #eff6ff; color: #2563eb; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <table style="width:100%; margin-bottom: 25px; border-bottom: 3px solid #f97316; padding-bottom: 10px;">
        <tr>
            <td style="border:none; padding: 0;">
                <div style="font-size: 22px; color: #f97316; font-weight: 800; letter-spacing: -0.5px;">QUOTATION</div>
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 4px;"><?= e(defined('COMPANY_NAME') ? COMPANY_NAME : 'CYN Tourism') ?></div>
                <?php if (defined('COMPANY_ADDRESS')): ?>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;"><?= e(COMPANY_ADDRESS) ?></div>
                <?php endif; ?>
            </td>
            <td style="border:none; padding: 0; text-align: right;">
                <div style="font-size: 12px; font-weight: 700; color: #1e293b;"><?= e($q['quote_number']) ?></div>
                <div style="font-size: 10px; color: #64748b; margin-top: 3px;">Date: <?= date('d M Y', strtotime($q['created_at'])) ?></div>
                <?php if ($q['valid_until']): ?>
                <div style="font-size: 10px; color: #64748b;">Valid until: <?= date('d M Y', strtotime($q['valid_until'])) ?></div>
                <?php endif; ?>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Status: <?= ucfirst($q['status']) ?></div>
            </td>
        </tr>
    </table>

    <!-- Client Info -->
    <div class="section">
        <div class="section-title">Client Information</div>
        <div class="info-grid">
            <div class="info-row"><span class="info-label">Client Name</span><span class="info-value"><?= e($q['client_name']) ?></span></div>
            <?php if ($q['client_email']): ?>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= e($q['client_email']) ?></span></div>
            <?php endif; ?>
            <?php if ($q['client_phone']): ?>
            <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?= e($q['client_phone']) ?></span></div>
            <?php endif; ?>
            <?php if (!empty($q['partner_name'])): ?>
            <div class="info-row"><span class="info-label">Partner</span><span class="info-value"><?= e($q['partner_name']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Travel Details -->
    <div class="section">
        <div class="section-title">Travel Details</div>
        <div class="info-grid">
            <div class="info-row"><span class="info-label">Travel Dates</span><span class="info-value"><?= $q['travel_dates_from'] ? date('d M Y', strtotime($q['travel_dates_from'])) : '—' ?> → <?= $q['travel_dates_to'] ? date('d M Y', strtotime($q['travel_dates_to'])) : '—' ?></span></div>
            <div class="info-row"><span class="info-label">Passengers</span><span class="info-value"><?= (int)$q['adults'] ?> Adults<?php if ((int)$q['children']): ?>, <?= (int)$q['children'] ?> Children<?php endif; ?><?php if ((int)$q['infants']): ?>, <?= (int)$q['infants'] ?> Infants<?php endif; ?></span></div>
        </div>
    </div>

    <!-- Itinerary -->
    <div class="section">
        <div class="section-title">Itinerary</div>
        <?php if (empty($days)): ?>
        <p style="text-align:center; color:#94a3b8; padding:15px 0;">No items.</p>
        <?php else: ?>
        <?php foreach ($days as $dayNum => $dayItems): ?>
        <div class="day-header">Day <?= $dayNum ?></div>
        <table>
            <thead>
                <tr>
                    <th style="width:70px;">Type</th>
                    <th>Service</th>
                    <th style="width:35px; text-align:center;">Qty</th>
                    <th style="width:80px; text-align:right;">Unit Price</th>
                    <th style="width:80px; text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dayItems as $item): ?>
                <tr>
                    <td><span class="badge badge-type"><?= $typeLabels[$item['item_type']] ?? ucfirst($item['item_type']) ?></span></td>
                    <td>
                        <strong><?= e($item['item_name']) ?></strong>
                        <?php if ($item['description']): ?><br><span style="color:#64748b; font-size:9px;"><?= e($item['description']) ?></span><?php endif; ?>
                    </td>
                    <td style="text-align:center;"><?= (int)$item['quantity'] ?></td>
                    <td class="num"><?= number_format((float)$item['unit_price'], 2) ?></td>
                    <td class="num"><?= number_format((float)$item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Totals -->
    <table class="totals">
        <tr>
            <td style="color:#64748b;">Subtotal</td>
            <td><?= $q['currency'] ?> <?= number_format((float)$q['subtotal'], 2) ?></td>
        </tr>
        <?php if ((float)$q['discount_amount'] > 0): ?>
        <tr>
            <td style="color:#ef4444;">Discount (<?= $q['discount_percent'] ?>%)</td>
            <td style="color:#ef4444;">-<?= $q['currency'] ?> <?= number_format((float)$q['discount_amount'], 2) ?></td>
        </tr>
        <?php endif; ?>
        <?php if ((float)$q['tax_amount'] > 0): ?>
        <tr>
            <td style="color:#64748b;">Tax (<?= $q['tax_percent'] ?>%)</td>
            <td>+<?= $q['currency'] ?> <?= number_format((float)$q['tax_amount'], 2) ?></td>
        </tr>
        <?php endif; ?>
        <tr class="grand">
            <td>Total</td>
            <td><?= $q['currency'] ?> <?= number_format((float)$q['total'], 2) ?></td>
        </tr>
    </table>

    <!-- Terms & Notes -->
    <?php if ($q['payment_terms'] || $q['notes'] || $q['cancellation_policy']): ?>
    <div class="terms">
        <?php if ($q['payment_terms']): ?>
        <h4>Payment Terms</h4>
        <p><?= e($q['payment_terms']) ?></p>
        <?php endif; ?>
        <?php if ($q['cancellation_policy']): ?>
        <h4 style="margin-top:8px;">Cancellation Policy</h4>
        <p><?= e($q['cancellation_policy']) ?></p>
        <?php endif; ?>
        <?php if ($q['notes']): ?>
        <h4 style="margin-top:8px;">Notes</h4>
        <p><?= e($q['notes']) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <?= e(defined('COMPANY_NAME') ? COMPANY_NAME : 'CYN Tourism') ?> — This quotation is valid until <?= $q['valid_until'] ? date('d M Y', strtotime($q['valid_until'])) : 'further notice' ?>.<br>
        Generated on <?= date('d M Y H:i') ?>
    </div>
</div>
</body>
</html>
