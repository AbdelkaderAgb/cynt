<?php
/**
 * Transfer Voucher PDF — Professional Official Document
 * RULE 2: Vouchers do NOT include prices — prices only on invoices/reports.
 * Variables: $voucher, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$v = $voucher;
$logoPath = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath = ROOT_PATH . '/stamp.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64 = file_exists($stampPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath)) : '';
$partnerLogoBase64 = '';
if (!empty($partnerLogo) && file_exists(ROOT_PATH . '/' . $partnerLogo)) {
    $ext = strtolower(pathinfo($partnerLogo, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['png']) ? 'image/png' : (in_array($ext, ['gif']) ? 'image/gif' : 'image/jpeg');
    $partnerLogoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents(ROOT_PATH . '/' . $partnerLogo));
}
$typeLabels = ['one_way'=>'One Way','round_trip'=>'Round Trip','multi_stop'=>'Multi Stop'];
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'];
$pdfLang = $currentLang ?? 'en';
$pdfDir = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Transfer Voucher <?= htmlspecialchars($v['voucher_no']) ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #222; line-height: 1.5; }
    .page { padding: 25px 35px; }

    .header { border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 18px; }
    .header table { width: 100%; }
    .header .logo-cell { width: 50%; vertical-align: bottom; }
    .header .logo-cell img { height: 60px; vertical-align: middle; margin-right: 8px; }
    .header .doc-cell { width: 50%; text-align: right; vertical-align: bottom; }
    .header .doc-type { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; color: #111; }
    .header .doc-no { font-size: 11px; color: #555; margin-top: 2px; }

    .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #333; border-bottom: 1px solid #999; padding-bottom: 3px; margin: 16px 0 8px 0; }

    .info-block { width: 100%; margin-bottom: 14px; border: 1px solid #ccc; border-collapse: collapse; }
    .info-block td { padding: 6px 10px; vertical-align: top; border: 1px solid #ccc; }
    .info-block .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; display: block; margin-bottom: 1px; }
    .info-block .val { font-size: 11px; color: #111; font-weight: bold; }

    .route-box { width: 100%; margin-bottom: 14px; }
    .route-box td { padding: 10px 14px; vertical-align: top; }
    .route-box .route-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 2px; }
    .route-box .route-value { font-size: 14px; font-weight: bold; color: #111; }
    .route-box .route-time { font-size: 10px; color: #555; margin-top: 2px; }
    .route-arrow { text-align: center; vertical-align: middle; font-size: 18px; color: #999; }

    .stamp { display: inline-block; border: 2px solid; padding: 4px 16px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transform: rotate(-5deg); }
    .stamp-pending { border-color: #e65100; color: #e65100; }
    .stamp-confirmed { border-color: #1565c0; color: #1565c0; }
    .stamp-completed { border-color: #2e7d32; color: #2e7d32; }
    .stamp-cancelled { border-color: #c62828; color: #c62828; }
    .stamp-no_show { border-color: #616161; color: #616161; }

    .notes { margin-top: 12px; padding: 8px 10px; border: 1px solid #ddd; background: #fafafa; font-size: 10px; }
    .notes-hd { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 3px; }

    .footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; text-align: center; font-size: 9px; color: #888; }
    .footer-tursab { margin-top: 6px; }
    .footer-tursab img { height: 28px; vertical-align: middle; margin-right: 6px; }
    .footer-tursab span { font-size: 8px; color: #999; font-style: italic; vertical-align: middle; }
</style>
</head>
<body>
<div class="page">

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td class="logo-cell">
                    <?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="Logo"><?php endif; ?>
                    <?php if ($partnerLogoBase64): ?><img src="<?= $partnerLogoBase64 ?>" alt="Partner" style="height:40px; margin-left:10px; vertical-align:middle;"><?php endif; ?>
                </td>
                <td class="doc-cell">
                    <div class="doc-type">Transfer Voucher</div>
                    <div class="doc-no"><?= htmlspecialchars($v['voucher_no']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TRANSFER DETAILS -->
    <div class="section-title">Transfer Details</div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Company</span>
                <span class="val"><?= htmlspecialchars($v['company_name'] ?? '') ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">Hotel</span>
                <span class="val"><?= htmlspecialchars($v['hotel_name'] ?? '—') ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Transfer Type</span>
                <span class="val"><?= $typeLabels[$v['transfer_type']] ?? $v['transfer_type'] ?></span>
            </td>
            <td>
                <span class="lbl">Total Passengers</span>
                <span class="val"><?= $v['total_pax'] ?? 0 ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Guest Name</span>
                <span class="val"><?= htmlspecialchars($v['guest_name'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Passport No.</span>
                <span class="val"><?= htmlspecialchars($v['passenger_passport'] ?? '—') ?></span>
            </td>
        </tr>
        <?php if (!empty($v['flight_number'])): ?>
        <tr>
            <td colspan="2">
                <span class="lbl">Flight Number</span>
                <span class="val"><?= htmlspecialchars($v['flight_number']) ?></span>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- ROUTE -->
    <div class="section-title">Route &amp; Schedule</div>
    <table class="route-box" style="border: 1px solid #ccc; border-collapse: collapse;">
        <tr>
            <td style="width:42%; border: 1px solid #ccc; padding: 12px;">
                <div class="route-label">Pickup Location</div>
                <div class="route-value"><?= htmlspecialchars($v['pickup_location'] ?? '') ?></div>
                <?php if (!empty($v['pickup_city'])): ?>
                <div class="route-time"><?= htmlspecialchars($v['pickup_city'] . ($v['pickup_country'] ? ', ' . $v['pickup_country'] : '')) ?></div>
                <?php endif; ?>
                <div class="route-time"><?= $v['pickup_date'] ? date('d.m.Y', strtotime($v['pickup_date'])) : '—' ?> · <?= htmlspecialchars($v['pickup_time'] ?? '—') ?></div>
            </td>
            <td class="route-arrow" style="width:16%; border: 1px solid #ccc; text-align: center;">→</td>
            <td style="width:42%; border: 1px solid #ccc; padding: 12px;">
                <div class="route-label">Drop-off Location</div>
                <div class="route-value"><?= htmlspecialchars($v['dropoff_location'] ?? '') ?></div>
                <?php if (!empty($v['dropoff_city'])): ?>
                <div class="route-time"><?= htmlspecialchars($v['dropoff_city'] . ($v['dropoff_country'] ? ', ' . $v['dropoff_country'] : '')) ?></div>
                <?php endif; ?>
                <?php if (!empty($v['return_date'])): ?>
                <div class="route-time"><?= date('d.m.Y', strtotime($v['return_date'])) ?> · <?= htmlspecialchars($v['return_time'] ?? '—') ?></div>
                <?php endif; ?>
            </td>
        </tr>
        <?php if (!empty($v['estimated_duration_min']) || !empty($v['distance_km'])): ?>
        <tr>
            <td colspan="3" style="border: 1px solid #ccc; padding: 6px 12px; text-align: center;">
                <?php if (!empty($v['estimated_duration_min'])): ?>
                <span style="margin-right: 20px;"><span class="route-label" style="display:inline;">Est. Duration:</span> <strong><?= $v['estimated_duration_min'] ?> min</strong></span>
                <?php endif; ?>
                <?php if (!empty($v['distance_km'])): ?>
                <span><span class="route-label" style="display:inline;">Distance:</span> <strong><?= $v['distance_km'] ?> km</strong></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if (!empty($v['description'])): ?>
    <div class="notes" style="margin-top: 0; margin-bottom: 14px;">
        <div class="notes-hd">Description</div>
        <?= nl2br(htmlspecialchars($v['description'])) ?>
    </div>
    <?php endif; ?>

    <!-- PASSENGERS -->
    <?php if (!empty($v['passengers'])): ?>
    <div class="section-title">Passengers</div>
    <div style="border: 1px solid #ccc; padding: 8px 10px; font-size: 11px; margin-bottom: 14px;">
        <?= nl2br(htmlspecialchars($v['passengers'])) ?>
    </div>
    <?php endif; ?>

    <!-- STATUS (vouchers do not include prices) -->
    <table style="width:100%; margin-bottom: 14px;">
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Total Passengers</div>
                <div style="font-size:22px; font-weight:bold; color:#111;"><?= $v['total_pax'] ?? 0 ?></div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                <?php $st = $v['status'] ?? 'pending'; ?>
                <span class="stamp stamp-<?= $st ?>"><?= htmlspecialchars($statusLabels[$st] ?? ucfirst(str_replace('_', ' ', $st))) ?></span>
            </td>
        </tr>
    </table>

    <!-- NOTES -->
    <?php if (!empty($v['notes'])): ?>
    <div class="notes">
        <div class="notes-hd">Notes</div>
        <?= nl2br(htmlspecialchars($v['notes'])) ?>
    </div>
    <?php endif; ?>

    <!-- STAMP IMAGE -->
    <?php if ($stampBase64 && ($v['status'] ?? '') === 'confirmed'): ?>
    <div style="text-align: right; margin-top: 10px;">
        <img src="<?= $stampBase64 ?>" alt="Stamp" style="height: 80px; opacity: 0.7;">
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="footer">
        <div><?= htmlspecialchars($companyName ?? COMPANY_NAME) ?> · <?= htmlspecialchars($companyAddress ?? '') ?></div>
        <div>Tel: <?= htmlspecialchars($companyPhone ?? '') ?> · Email: <?= htmlspecialchars($companyEmail ?? '') ?></div>
        <?php if ($tursabBase64): ?>
        <div class="footer-tursab">
            <img src="<?= $tursabBase64 ?>" alt="TURSAB">
            <span>Licensed Travel Agency</span>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
