<?php
/**
 * Tour Voucher PDF — Professional Official Document
 * Variables: $tour, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$t = $tour;
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
$customers = json_decode($t['customers'] ?? '[]', true) ?: [];
$tourItems = json_decode($t['tour_items'] ?? '[]', true) ?: [];
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'];
$pdfLang = $currentLang ?? 'en';
$pdfDir = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Tour Voucher <?= htmlspecialchars($t['tour_code'] ?? $t['tour_name'] ?? '') ?></title>
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

    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .data-table th { background: #f5f5f5; border: 1px solid #ccc; padding: 6px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #333; text-align: left; }
    .data-table th.r { text-align: right; }
    .data-table th.c { text-align: center; }
    .data-table td { border: 1px solid #ccc; padding: 6px 10px; font-size: 11px; }
    .r { text-align: right; }
    .c { text-align: center; }


    .stamp { display: inline-block; border: 2px solid; padding: 4px 16px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transform: rotate(-5deg); }
    .stamp-pending { border-color: #e65100; color: #e65100; }
    .stamp-confirmed { border-color: #1565c0; color: #1565c0; }
    .stamp-in_progress { border-color: #00695c; color: #00695c; }
    .stamp-completed { border-color: #2e7d32; color: #2e7d32; }
    .stamp-cancelled { border-color: #c62828; color: #c62828; }

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
                    <div class="doc-type">Tour Voucher</div>
                    <div class="doc-no"><?= htmlspecialchars($t['tour_code'] ?? '') ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TOUR DETAILS -->
    <div class="section-title">Tour Details</div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Tour Name</span>
                <span class="val"><?= htmlspecialchars($t['tour_name'] ?? '') ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">Company</span>
                <span class="val"><?= htmlspecialchars($t['company_name'] ?? '') ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Tour Date</span>
                <span class="val"><?= $t['tour_date'] ? date('d.m.Y', strtotime($t['tour_date'])) : '—' ?></span>
            </td>
            <td>
                <span class="lbl">Customer Phone</span>
                <span class="val"><?= htmlspecialchars($t['customer_phone'] ?? '—') ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Destination</span>
                <span class="val"><?= htmlspecialchars($t['destination'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Hotel</span>
                <span class="val"><?= htmlspecialchars($t['hotel_name'] ?? '—') ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Guest Name</span>
                <span class="val"><?= htmlspecialchars($t['guest_name'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Passport No.</span>
                <span class="val"><?= htmlspecialchars($t['passenger_passport'] ?? '—') ?></span>
            </td>
        </tr>
        <?php if (!empty($t['meeting_point'])): ?>
        <tr>
            <td>
                <span class="lbl">Meeting Point</span>
                <span class="val"><?= htmlspecialchars($t['meeting_point']) ?></span>
            </td>
            <td>
                <span class="lbl">Duration</span>
                <span class="val"><?= $t['duration_hours'] ? $t['duration_hours'] . ' hours' : '—' ?></span>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($t['city']) || !empty($t['country']) || !empty($t['address'])): ?>
        <tr>
            <td>
                <span class="lbl">Location</span>
                <span class="val"><?= htmlspecialchars(trim(($t['city'] ?? '') . ($t['country'] ? ', ' . $t['country'] : ''))) ?: '—' ?></span>
            </td>
            <td>
                <span class="lbl">Address</span>
                <span class="val"><?= htmlspecialchars($t['address'] ?? '—') ?></span>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if (!empty($t['includes']) || !empty($t['excludes'])): ?>
    <div class="section-title">Includes / Excludes</div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">✓ Includes</span>
                <span class="val" style="font-weight:normal;"><?= nl2br(htmlspecialchars($t['includes'] ?? '—')) ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">✗ Excludes</span>
                <span class="val" style="font-weight:normal;"><?= nl2br(htmlspecialchars($t['excludes'] ?? '—')) ?></span>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- TOUR ITEMS (vouchers do not include prices) -->
    <?php if (!empty($tourItems)): ?>
    <div class="section-title">Tour Items</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:70%;">Description</th>
                <th class="c" style="width:25%;">Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tourItems as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($item['description'] ?? $item['name'] ?? '') ?></td>
                <td class="c"><?= $item['quantity'] ?? 1 ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- CUSTOMER LIST -->
    <?php if (!empty($customers)): ?>
    <div class="section-title">Customers</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:12%;">Title</th>
                <th style="width:43%;">Name</th>
                <th style="width:20%;">Type</th>
                <th style="width:20%;">Age</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $i => $c): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($c['title'] ?? '') ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($c['name'] ?? '') ?></td>
                <td><?= htmlspecialchars(ucfirst($c['type'] ?? 'adult')) ?></td>
                <td><?= htmlspecialchars($c['age'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Vouchers do not include prices — pricing is shown only in invoices and receipts -->

    <!-- STATUS -->
    <table style="width:100%; margin-bottom: 14px;">
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Total Passengers</div>
                <div style="font-size:22px; font-weight:bold; color:#111;"><?= $t['total_pax'] ?? 0 ?></div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                <?php $st = $t['status'] ?? 'pending'; ?>
                <span class="stamp stamp-<?= $st ?>"><?= htmlspecialchars($statusLabels[$st] ?? ucfirst(str_replace('_', ' ', $st))) ?></span>
            </td>
        </tr>
    </table>

    <!-- NOTES -->
    <?php if (!empty($t['notes'])): ?>
    <div class="notes">
        <div class="notes-hd">Notes</div>
        <?= nl2br(htmlspecialchars($t['notes'])) ?>
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
