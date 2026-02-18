<?php
/**
 * Transfer Voucher PDF — Professional Official Document
 * Variables: $voucher, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$v = $voucher;
$logoPath = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath = ROOT_PATH . '/stamp.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64 = file_exists($stampPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath)) : '';
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

    .route-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 14px; }
    .route-box table { width: 100%; }
    .route-box .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 2px; }
    .route-box .loc { font-size: 13px; font-weight: bold; color: #111; }
    .route-box .arrow { text-align: center; font-size: 18px; color: #555; vertical-align: middle; }

    .pax-box { border: 1px solid #ccc; padding: 8px 10px; margin-bottom: 14px; line-height: 1.6; }


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
                </td>
                <td class="doc-cell">
                    <div class="doc-type">Transfer Voucher</div>
                    <div class="doc-no"><?= htmlspecialchars($v['voucher_no']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <?php
    // Parse transfer legs — fall back to main fields for old vouchers
    $legs = json_decode($v['transfer_legs'] ?? '[]', true) ?: [];
    if (empty($legs)) {
        $legs = [[
            'pickup_location' => $v['pickup_location'] ?? '',
            'dropoff_location' => $v['dropoff_location'] ?? '',
            'pickup_date' => $v['pickup_date'] ?? '',
            'pickup_time' => $v['pickup_time'] ?? '',
            'return_date' => $v['return_date'] ?? '',
            'return_time' => $v['return_time'] ?? '',
            'transfer_type' => $v['transfer_type'] ?? 'one_way',
            'total_pax' => $v['total_pax'] ?? 1,
            'flight_number' => $v['flight_number'] ?? '',
            'price' => $v['price'] ?? 0,
        ]];
    }
    ?>

    <!-- TRANSFER DETAILS -->
    <div class="section-title">Transfer Details</div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Company</span>
                <span class="val"><?= htmlspecialchars($v['company_name']) ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">Hotel</span>
                <span class="val"><?= htmlspecialchars($v['hotel_name'] ?: '—') ?></span>
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
    </table>

    <!-- ROUTES (one per leg) -->
    <?php foreach ($legs as $li => $leg): ?>
    <div class="section-title"><?= count($legs) > 1 ? 'Transfer #' . ($li + 1) : 'Route' ?></div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Pickup Date &amp; Time</span>
                <span class="val"><?= !empty($leg['pickup_date']) ? date('d.m.Y', strtotime($leg['pickup_date'])) : '—' ?> — <?= $leg['pickup_time'] ?? '' ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">Return Date &amp; Time</span>
                <span class="val"><?= !empty($leg['return_date']) ? date('d.m.Y', strtotime($leg['return_date'])) . ' — ' . ($leg['return_time'] ?? '') : '—' ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Transfer Type</span>
                <span class="val"><?= htmlspecialchars(ucfirst($leg['transfer_type'] ?? 'one_way')) ?></span>
            </td>
            <td>
                <span class="lbl">Flight Number</span>
                <span class="val"><?= htmlspecialchars($leg['flight_number'] ?? '') ?: '—' ?></span>
            </td>
        </tr>
    </table>
    <div class="route-box">
        <table>
            <tr>
                <td style="width:40%;">
                    <div class="lbl">Pickup Location</div>
                    <div class="loc"><?= htmlspecialchars($leg['pickup_location'] ?? '') ?></div>
                </td>
                <td style="width:20%;" class="arrow">→</td>
                <td style="width:40%;">
                    <div class="lbl">Drop-off Location</div>
                    <div class="loc"><?= htmlspecialchars($leg['dropoff_location'] ?? '') ?></div>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- PASSENGERS -->
    <?php if (!empty($v['passengers'])): ?>
    <div class="section-title">Passengers</div>
    <div class="pax-box">
        <?= nl2br(htmlspecialchars($v['passengers'])) ?>
    </div>
    <?php endif; ?>

    <!-- Vouchers do not include prices — pricing is shown only in invoices and receipts -->

    <!-- STATUS -->
    <table style="width:100%; margin-bottom: 14px;">
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Total Passengers</div>
                <div style="font-size:24px; font-weight:bold; color:#111;"><?= $v['total_pax'] ?></div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                <?php $st = $v['status'] ?? 'pending'; ?>
                <span class="stamp stamp-<?= $st ?>"><?= htmlspecialchars($statusLabels[$st] ?? ucfirst($st)) ?></span>
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

    <!-- AUTHORIZATION & STAMP -->
    <?php if ($stampBase64): ?>
    <table style="width:100%; margin-top: 30px;">
        <tr>
            <td style="width:50%; vertical-align:bottom; padding:0;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Authorized By</div>
                <div style="margin-top:4px; font-size:11px; font-weight:bold; color:#222;"><?= htmlspecialchars($companyName) ?></div>
            </td>
            <td style="width:50%; text-align:right; vertical-align:bottom; padding:0;">
                <img src="<?= $stampBase64 ?>" alt="Company Seal" style="height:120px; opacity:0.9;">
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="footer">
        <?= htmlspecialchars($companyName) ?> · <?= htmlspecialchars($companyAddress) ?><br>
        Tel: <?= htmlspecialchars($companyPhone) ?> · <?= htmlspecialchars($companyEmail) ?> · Generated <?= date('d.m.Y H:i') ?>
        <?php if ($tursabBase64): ?>
        <div class="footer-tursab">
            <img src="<?= $tursabBase64 ?>" alt="TURSAB">
            <span>TURSAB Üyesi — Belge No: 11738</span>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
