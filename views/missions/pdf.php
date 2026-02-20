<?php
/**
 * Mission PDF Template — dark navy header, professional layout
 * Rendered standalone via Dompdf
 */
$statusLabels = [
    'pending'     => 'Pending',
    'assigned'    => 'Assigned',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
];
$typeLabels = [
    'tour'          => 'Tour',
    'transfer'      => 'Transfer',
    'hotel_service' => 'Hotel Service',
];

$logoPath   = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath  = ROOT_PATH . '/stamp.png';
$logoBase64   = file_exists($logoPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))   : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64  = file_exists($stampPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))  : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mission #<?= (int)$m['id'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .page { padding: 0; }
        .header-bar { background: #0f172a; color: #fff; padding: 22px 36px; margin-bottom: 0; }
        .header-bar table { width: 100%; border: none; }
        .header-bar td { border: none; padding: 0; vertical-align: middle; }
        .header-bar .title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: #fff; }
        .header-bar .subtitle { font-size: 10px; color: #94a3b8; margin-top: 3px; }
        .header-bar .co-name { font-size: 12px; font-weight: 600; color: #e2e8f0; text-align: right; }
        .header-bar .co-info { font-size: 9px; color: #94a3b8; text-align: right; margin-top: 2px; }
        .accent-bar { height: 4px; background: linear-gradient(90deg, #6366f1, #8b5cf6); }
        .body { padding: 24px 36px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pending    { background: #fef3c7; color: #92400e; }
        .status-assigned   { background: #dbeafe; color: #1e40af; }
        .status-in_progress { background: #cffafe; color: #164e63; }
        .status-completed  { background: #d1fae5; color: #065f46; }
        .status-cancelled  { background: #fee2e2; color: #7f1d1d; }
        .info-grid { display: table; width: 100%; margin-bottom: 18px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 12px; }
        .info-col:last-child { padding-right: 0; padding-left: 12px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; background: #f8fafc; height: 100%; }
        .info-box h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #6366f1; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e0e7ff; }
        .info-row { display: table; width: 100%; }
        .info-label { display: table-cell; width: 110px; font-size: 10px; color: #64748b; padding: 2px 0; font-weight: 600; }
        .info-value { display: table-cell; font-size: 11px; color: #1e293b; padding: 2px 0; }
        .notes-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; background: #fffbeb; margin-bottom: 18px; }
        .notes-box h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #d97706; margin-bottom: 6px; }
        .notes-box p { font-size: 10px; color: #78350f; white-space: pre-wrap; }
        .footer { margin-top: 24px; padding: 12px 36px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
        .stamp { position: absolute; right: 50px; bottom: 80px; opacity: 0.35; width: 100px; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header-bar">
        <table>
            <tr>
                <td>
                    <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" style="height:40px; margin-bottom:6px; display:block; filter:brightness(0) invert(1);">
                    <?php endif; ?>
                    <div class="title">MISSION BRIEF</div>
                    <div class="subtitle">
                        #<?= (int)$m['id'] ?> &nbsp;·&nbsp;
                        <?= $typeLabels[$m['mission_type']] ?? ucfirst($m['mission_type']) ?> &nbsp;·&nbsp;
                        <?= $m['mission_date'] ? date('d M Y', strtotime($m['mission_date'])) : '—' ?>
                        &nbsp;·&nbsp;
                        <span class="status-badge status-<?= e($m['status']) ?>"><?= $statusLabels[$m['status']] ?? ucfirst($m['status']) ?></span>
                    </div>
                </td>
                <td style="text-align:right; vertical-align:top;">
                    <div class="co-name"><?= e($companyName ?? '') ?></div>
                    <div class="co-info"><?= e($companyAddress ?? '') ?></div>
                    <div class="co-info"><?= e($companyPhone ?? '') ?> · <?= e($companyEmail ?? '') ?></div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-bar"></div>

    <div class="body">
        <!-- Schedule & Route | Guest Info -->
        <div class="info-grid">
            <div class="info-col">
                <div class="info-box">
                    <h3>Schedule &amp; Route</h3>
                    <div class="info-row"><span class="info-label">Date</span><span class="info-value"><?= $m['mission_date'] ? date('d M Y', strtotime($m['mission_date'])) : '—' ?></span></div>
                    <div class="info-row"><span class="info-label">Time</span><span class="info-value">
                        <?= $m['start_time'] ? date('H:i', strtotime($m['start_time'])) : '—' ?>
                        <?= $m['end_time'] ? ' — ' . date('H:i', strtotime($m['end_time'])) : '' ?>
                    </span></div>
                    <div class="info-row"><span class="info-label">Pickup</span><span class="info-value"><?= e($m['pickup_location'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Drop-off</span><span class="info-value"><?= e($m['dropoff_location'] ?: '—') ?></span></div>
                    <?php if ((int)$m['reference_id'] > 0): ?>
                    <div class="info-row"><span class="info-label">Reference</span><span class="info-value">#<?= (int)$m['reference_id'] ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <h3>Guest Information</h3>
                    <div class="info-row"><span class="info-label">Guest Name</span><span class="info-value"><?= e($m['guest_name'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Passport</span><span class="info-value"><?= e($m['guest_passport'] ?: '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Pax Count</span><span class="info-value"><?= (int)$m['pax_count'] ?></span></div>
                </div>
            </div>
        </div>

        <!-- Assignment -->
        <div class="info-grid">
            <div class="info-col">
                <div class="info-box">
                    <h3>Driver &amp; Vehicle</h3>
                    <div class="info-row"><span class="info-label">Driver</span><span class="info-value"><?= e($m['driver_name'] ?: '—') ?><?php if (!empty($m['driver_phone'])): ?> <span style="color:#64748b;font-size:9px;">(<?= e($m['driver_phone']) ?>)</span><?php endif; ?></span></div>
                    <div class="info-row"><span class="info-label">Vehicle</span><span class="info-value">
                        <?php if (!empty($m['plate_number'])): ?>
                        <?= e($m['plate_number']) ?> — <?= e(($m['vehicle_make'] ?? '') . ' ' . ($m['vehicle_model'] ?? '')) ?>
                        <?php else: ?>—<?php endif; ?>
                    </span></div>
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <h3>Guide</h3>
                    <div class="info-row"><span class="info-label">Guide</span><span class="info-value"><?= e($m['guide_name'] ?: '—') ?><?php if (!empty($m['guide_phone'])): ?> <span style="color:#64748b;font-size:9px;">(<?= e($m['guide_phone']) ?>)</span><?php endif; ?></span></div>
                </div>
            </div>
        </div>

        <?php if (!empty($m['notes'])): ?>
        <!-- Notes -->
        <div class="notes-box">
            <h3>Notes</h3>
            <p><?= e($m['notes']) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($stampBase64): ?>
        <img src="<?= $stampBase64 ?>" class="stamp">
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <?php if ($tursabBase64): ?>
        <img src="<?= $tursabBase64 ?>" style="height:18px; margin-bottom:4px;"><br>
        <?php endif; ?>
        <?= e($companyName ?? '') ?> · <?= e($companyAddress ?? '') ?> · Tel: <?= e($companyPhone ?? '') ?> · <?= e($companyEmail ?? '') ?><br>
        Generated on <?= date('d M Y H:i') ?>
    </div>
</div>
</body>
</html>
