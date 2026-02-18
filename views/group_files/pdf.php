<?php
/**
 * Group File PDF Dossier — No prices shown (voucher rule)
 * Timeline-based layout for group travel management
 */
$statusLabels = [
    'planning' => 'Planning', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress',
    'completed' => 'Completed', 'cancelled' => 'Cancelled', 'pending' => 'Pending',
    'checked_in' => 'Checked In',
];
$typeLabels = ['hotel_voucher' => 'Hotel', 'tour' => 'Tour', 'transfer' => 'Transfer'];

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
    <title>Group Dossier <?= e($g['file_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .page { padding: 30px 40px; }
        .header-bar { background: linear-gradient(135deg, #7c3aed, #a855f7); color: white; padding: 20px 30px; margin: -30px -40px 25px; }
        .header-bar h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        .header-bar .sub { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 15px; margin-bottom: 15px; background: #fafafa; }
        .info-box h3 { font-size: 10px; text-transform: uppercase; color: #7c3aed; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 6px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 110px; font-size: 10px; color: #64748b; padding: 2px 0; font-weight: 600; }
        .info-value { display: table-cell; font-size: 11px; color: #1e293b; padding: 2px 0; }
        .day-section { margin-bottom: 15px; page-break-inside: avoid; }
        .day-header { background: #f5f3ff; border-left: 4px solid #7c3aed; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #6d28d9; margin-bottom: 8px; }
        .item-card { border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 12px; margin-bottom: 6px; margin-left: 20px; position: relative; }
        .item-card::before { content: ''; position: absolute; left: -14px; top: 14px; width: 8px; height: 8px; border-radius: 50%; }
        .item-card.hotel::before { background: #14b8a6; }
        .item-card.tour::before { background: #3b82f6; }
        .item-card.transfer::before { background: #22c55e; }
        .item-type { font-size: 9px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 3px; }
        .item-type.hotel { color: #14b8a6; }
        .item-type.tour { color: #3b82f6; }
        .item-type.transfer { color: #22c55e; }
        .item-label { font-size: 11px; font-weight: 600; color: #1e293b; }
        .item-date { font-size: 10px; color: #64748b; margin-top: 2px; }
        .item-notes { font-size: 9px; color: #94a3b8; font-style: italic; margin-top: 3px; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: 700; }
        .badge-status { background: #f0fdf4; color: #16a34a; }
        .badge-pending { background: #fffbeb; color: #d97706; }
        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
        .two-col { display: table; width: 100%; }
        .two-col > div { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .two-col > div:last-child { padding-right: 0; padding-left: 10px; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header-bar">
        <h1><?= e($g['group_name']) ?></h1>
        <div class="sub">
            <?= e($g['file_number']) ?> &nbsp;|&nbsp;
            <?= (int)$g['total_pax'] ?> Passengers &nbsp;|&nbsp;
            <?= $g['arrival_date'] ? date('d M Y', strtotime($g['arrival_date'])) : '—' ?> → <?= $g['departure_date'] ? date('d M Y', strtotime($g['departure_date'])) : '—' ?>
            <?php if ($g['arrival_date'] && $g['departure_date']): ?>
            &nbsp;|&nbsp; <?= (int)((strtotime($g['departure_date']) - strtotime($g['arrival_date'])) / 86400) ?> Nights
            <?php endif; ?>
        </div>
    </div>

    <!-- Info -->
    <div class="two-col">
        <div>
            <div class="info-box">
                <h3>Group Details</h3>
                <div class="info-grid">
                    <div class="info-row"><span class="info-label">Arrival</span><span class="info-value"><?= $g['arrival_date'] ? date('d M Y', strtotime($g['arrival_date'])) : '—' ?></span></div>
                    <div class="info-row"><span class="info-label">Departure</span><span class="info-value"><?= $g['departure_date'] ? date('d M Y', strtotime($g['departure_date'])) : '—' ?></span></div>
                    <div class="info-row"><span class="info-label">Passengers</span><span class="info-value"><?= (int)$g['adults'] ?> Adults<?php if ((int)$g['children']): ?>, <?= (int)$g['children'] ?> Children<?php endif; ?><?php if ((int)$g['infants']): ?>, <?= (int)$g['infants'] ?> Infants<?php endif; ?></span></div>
                    <div class="info-row"><span class="info-label">Status</span><span class="info-value"><?= ucfirst(str_replace('_', ' ', $g['status'])) ?></span></div>
                </div>
            </div>
        </div>
        <div>
            <div class="info-box">
                <h3>Leader & Partner</h3>
                <div class="info-grid">
                    <div class="info-row"><span class="info-label">Leader</span><span class="info-value"><?= e($g['leader_name'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?= e($g['leader_phone'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Partner</span><span class="info-value"><?= e($g['partner_name'] ?? '—') ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <?php if (empty($days)): ?>
    <div style="text-align:center; color:#94a3b8; padding:30px 0; font-size:12px;">No bookings linked to this group file.</div>
    <?php else: ?>
    <?php foreach ($days as $dayNum => $dayItems): ?>
    <div class="day-section">
        <div class="day-header">Day <?= $dayNum ?></div>
        <?php foreach ($dayItems as $item): ?>
        <?php
            $cssType = str_replace('_voucher', '', $item['item_type']);
        ?>
        <div class="item-card <?= $cssType ?>">
            <div class="item-type <?= $cssType ?>"><?= $typeLabels[$item['item_type']] ?? ucfirst($item['item_type']) ?> #<?= (int)$item['reference_id'] ?></div>
            <div class="item-label"><?= e($item['detail']['label'] ?: 'Booking #' . $item['reference_id']) ?></div>
            <?php if (!empty($item['detail']['date'])): ?>
            <div class="item-date"><?= e($item['detail']['date']) ?></div>
            <?php endif; ?>
            <?php if (!empty($item['detail']['status'])): ?>
            <span class="badge <?= in_array($item['detail']['status'], ['confirmed','completed','checked_in']) ? 'badge-status' : 'badge-pending' ?>"><?= ucfirst($item['detail']['status']) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['notes'])): ?>
            <div class="item-notes"><?= e($item['notes']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($g['notes']): ?>
    <div class="info-box" style="margin-top: 15px;">
        <h3>Notes</h3>
        <p style="font-size:10px; color:#475569; white-space:pre-wrap;"><?= e($g['notes']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <?= e(defined('COMPANY_NAME') ? COMPANY_NAME : 'CYN Tourism') ?> — Group Dossier: <?= e($g['file_number']) ?><br>
        Generated on <?= date('d M Y H:i') ?>
    </div>
</div>
</body>
</html>
