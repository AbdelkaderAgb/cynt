<?php
/**
 * Tour Voucher PDF — Professional Corporate Document
 */
$t          = $tour;
$logoPath   = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath  = ROOT_PATH . '/stamp.png';
$logoBase64   = file_exists($logoPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))   : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64  = file_exists($stampPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))  : '';
$partnerLogoBase64 = '';
if (!empty($partnerLogo) && file_exists(ROOT_PATH . '/' . $partnerLogo)) {
    $ext  = strtolower(pathinfo($partnerLogo, PATHINFO_EXTENSION));
    $mime = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
    $partnerLogoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents(ROOT_PATH . '/' . $partnerLogo));
}

$customers    = json_decode($t['customers'] ?? '[]', true) ?: [];
$tourItems    = json_decode($t['tour_items'] ?? '[]', true) ?: [];
$statusLabels = ['pending'=>'PENDING','confirmed'=>'CONFIRMED','in_progress'=>'IN PROGRESS','completed'=>'COMPLETED','cancelled'=>'CANCELLED'];
$statusColors = ['pending'=>'#b45309','confirmed'=>'#1d4ed8','in_progress'=>'#0f766e','completed'=>'#166534','cancelled'=>'#991b1b'];

$pdfLang = $currentLang ?? 'en';
$pdfDir  = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';

$st      = $t['status'] ?? 'pending';
$stLabel = $statusLabels[$st] ?? strtoupper(str_replace('_', ' ', $st));
$stColor = $statusColors[$st] ?? '#374151';
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Tour Voucher · <?= htmlspecialchars($t['tour_code'] ?? $t['tour_name'] ?? '') ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"DejaVu Sans",Arial,Helvetica,sans-serif; font-size:10.5px; color:#1a1a1a; line-height:1.55; background:#fff; }
.page { padding:0 36px 36px; }
.hd-band { background:#fff; margin:0 -36px; padding:20px 36px 16px; border-bottom:3px solid #0d1b2a; }
.hd-band table { width:100%; border-collapse:collapse; }
.hd-right { text-align:right; vertical-align:middle; }
.hd-doctype { font-size:20px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#0d1b2a; }
.hd-issued { font-size:8.5px; letter-spacing:0.5px; color:#5a6272; margin-top:4px; }
.vno-num { font-size:15px; font-weight:bold; letter-spacing:1.5px; color:#0d1b2a; font-family:"Courier New",monospace; }
.vno-date { font-size:9px; color:#5a6272; margin-top:2px; }
.status-badge { display:inline-block; border:1.5px solid; padding:3px 12px; font-size:9px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; transform:rotate(-2deg); }
.sec { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:1.8px; color:#0d1b2a; border-bottom:1px solid #0d1b2a; padding-bottom:3px; margin:18px 0 10px; }
.grid { width:100%; border-collapse:collapse; margin-bottom:14px; }
.grid td { padding:7px 10px; border:1px solid #d4d4d4; vertical-align:top; }
.lbl { font-size:7.5px; text-transform:uppercase; letter-spacing:.8px; color:#5a6272; font-weight:bold; display:block; margin-bottom:2px; }
.val { font-size:11px; font-weight:bold; color:#1a1a1a; }
.val-sm { font-size:10px; font-weight:bold; color:#1a1a1a; }
/* Tour name hero */
.tour-hero { background:#f7f7f7; border:1px solid #d4d4d4; border-left:4px solid #0d1b2a; padding:12px 16px; margin-bottom:14px; }
.tour-hero-name { font-size:16px; font-weight:bold; color:#0d1b2a; }
.tour-hero-code { font-size:9px; color:#5a6272; font-family:"Courier New",monospace; letter-spacing:1px; margin-top:2px; }
/* Data tables */
.data-table { width:100%; border-collapse:collapse; margin-bottom:14px; }
.data-table th { padding:6px 9px; background:#0d1b2a; color:#fff; font-size:7.5px; text-transform:uppercase; letter-spacing:1px; font-weight:bold; border:1px solid #0d1b2a; text-align:left; }
.data-table th.c { text-align:center; }
.data-table td { padding:6px 9px; border:1px solid #d4d4d4; font-size:10.5px; vertical-align:middle; }
.data-table tr:nth-child(even) td { background:#f7f7f7; }
.data-table .c { text-align:center; }
/* Includes/Excludes */
.incl-table { width:100%; border-collapse:collapse; margin-bottom:14px; }
.incl-table td { padding:10px 14px; border:1px solid #d4d4d4; vertical-align:top; width:50%; }
.incl-head { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:.8px; margin-bottom:5px; }
.incl-yes { color:#166534; }
.incl-no  { color:#991b1b; }
.incl-body { font-size:10px; color:#374151; line-height:1.6; }
/* Notes */
.notes-box { border-left:2px solid #9a7c3f; padding:8px 12px; background:#fffdf7; margin-bottom:14px; font-size:10px; color:#4a3a1a; }
.notes-lbl { font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#9a7c3f; margin-bottom:3px; }
.sig-table { width:100%; border-collapse:collapse; margin-top:32px; }
.sig-line { border-top:1px solid #1a1a1a; margin-top:32px; padding-top:4px; }
.sig-lbl { font-size:8px; text-transform:uppercase; letter-spacing:.6px; color:#5a6272; }
.sig-name { font-size:10px; font-weight:bold; color:#0d1b2a; margin-top:2px; }
.footer { margin-top:28px; padding-top:8px; border-top:1px solid #d4d4d4; text-align:center; font-size:8.5px; color:#5a6272; }
.footer img { height:22px; vertical-align:middle; margin-left:8px; }
</style>
</head>
<body>

<div class="hd-band">
    <table>
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" alt="Logo" style="height:72px; vertical-align:middle;"><?php endif; ?>
                <?php if ($partnerLogoBase64): ?><img src="<?= $partnerLogoBase64 ?>" alt="Partner" style="height:36px; vertical-align:middle; margin-left:12px; opacity:.85;"><?php endif; ?>
                <?php if (!$logoBase64): ?><span style="font-size:18px; font-weight:bold; color:#0d1b2a; letter-spacing:2px;"><?= htmlspecialchars($companyName ?? '') ?></span><?php endif; ?>
            </td>
            <td class="hd-right" style="width:50%;">
                <div class="hd-doctype">Tour Voucher</div>
                <div class="hd-issued">Official Tour Document · <?= htmlspecialchars($companyName ?? '') ?></div>
            </td>
        </tr>
    </table>
</div>

<div class="page">

<div style="margin-top:14px; margin-bottom:18px;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:middle; padding:0;">
                <div class="vno-num"><?= htmlspecialchars($t['tour_code'] ?? '') ?></div>
                <div class="vno-date">Tour Date: <?= !empty($t['tour_date']) ? date('d F Y', strtotime($t['tour_date'])) : '—' ?></div>
            </td>
            <td style="text-align:right; vertical-align:middle; padding:0;">
                <span class="status-badge" style="border-color:<?= $stColor ?>; color:<?= $stColor ?>;"><?= $stLabel ?></span>
            </td>
        </tr>
    </table>
    <div style="border-bottom:1px solid #d4d4d4; margin-top:10px;"></div>
</div>

<!-- TOUR NAME HERO -->
<div class="tour-hero">
    <div class="tour-hero-name"><?= htmlspecialchars($t['tour_name'] ?? '—') ?></div>
    <?php if (!empty($t['destination'])): ?>
    <div class="tour-hero-code">Destination: <?= htmlspecialchars($t['destination']) ?><?= !empty($t['duration_hours']) ? '  ·  Duration: ' . $t['duration_hours'] . ' hrs' : '' ?></div>
    <?php endif; ?>
</div>

<!-- BOOKING -->
<div class="sec">Booking Details</div>
<table class="grid">
    <tr>
        <td style="width:50%;">
            <span class="lbl">Travel Agency / Company</span>
            <span class="val"><?= htmlspecialchars($t['company_name'] ?? '—') ?></span>
        </td>
        <td style="width:50%;">
            <span class="lbl">Customer Phone</span>
            <span class="val-sm"><?= htmlspecialchars($t['customer_phone'] ?? '—') ?></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="lbl">Lead Guest</span>
            <span class="val"><?= htmlspecialchars($t['guest_name'] ?? '—') ?></span>
        </td>
        <td>
            <span class="lbl">Passport / ID No.</span>
            <span class="val" style="font-family:'Courier New',monospace; letter-spacing:1px;"><?= htmlspecialchars($t['passenger_passport'] ?? '—') ?></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="lbl">Passengers</span>
            <span style="font-size:11px; font-weight:bold; color:#0d1b2a;">
                <?= (int)($t['adults'] ?? 0) ?> <?= (int)($t['adults'] ?? 0) == 1 ? 'Adult' : 'Adults' ?>
                <?php if ((int)($t['children'] ?? 0) > 0): ?> &nbsp;·&nbsp; <?= (int)$t['children'] ?> <?= (int)$t['children'] == 1 ? 'Child' : 'Children' ?><?php endif; ?>
                <?php if ((int)($t['infants'] ?? 0) > 0): ?> &nbsp;·&nbsp; <?= (int)$t['infants'] ?> <?= (int)$t['infants'] == 1 ? 'Infant' : 'Infants' ?><?php endif; ?>
            </span>
            <br><span style="font-size:9px; color:#5a6272;">Total: <?= (int)($t['total_pax'] ?? 0) ?> pax</span>
        </td>
        <td></td>
    </tr>
</table>

<!-- INCLUDES / EXCLUDES -->
<?php if (!empty($t['includes']) || !empty($t['excludes'])): ?>
<div class="sec">Includes &amp; Excludes</div>
<table class="incl-table">
    <tr>
        <td>
            <div class="incl-head incl-yes">&#10003; Included</div>
            <div class="incl-body"><?= nl2br(htmlspecialchars($t['includes'] ?? '—')) ?></div>
        </td>
        <td>
            <div class="incl-head incl-no">&#10007; Not Included</div>
            <div class="incl-body"><?= nl2br(htmlspecialchars($t['excludes'] ?? '—')) ?></div>
        </td>
    </tr>
</table>
<?php endif; ?>

<!-- TOUR ITEMS -->
<?php if (!empty($tourItems)): ?>
<div class="sec">Tour Programme</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:28px; text-align:center;">#</th>
            <th>Tour Name</th>
            <th style="width:18%;">Date</th>
            <th style="width:16%;">Duration</th>
            <th class="c" style="width:8%;">Adults</th>
            <th class="c" style="width:8%;">Children</th>
            <th class="c" style="width:8%;">Infants</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tourItems as $i => $item): ?>
        <tr>
            <td style="text-align:center; font-size:9px; color:#5a6272; font-weight:bold;"><?= $i + 1 ?></td>
            <td style="font-weight:bold;"><?= htmlspecialchars($item['name'] ?? $item['description'] ?? '—') ?></td>
            <td><?= !empty($item['date']) ? htmlspecialchars(date('d/m/Y', strtotime($item['date']))) : '—' ?></td>
            <td><?= htmlspecialchars($item['duration'] ?? '—') ?></td>
            <td class="c"><?= (int)($item['adults'] ?? 0) ?></td>
            <td class="c"><?= (int)($item['children'] ?? 0) ?></td>
            <td class="c"><?= (int)($item['infants'] ?? 0) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>



<!-- NOTES -->
<?php if (!empty($t['notes'])): ?>
<div class="notes-box">
    <div class="notes-lbl">Notes</div>
    <?= nl2br(htmlspecialchars($t['notes'])) ?>
</div>
<?php endif; ?>

<table class="sig-table">
    <tr>
        <td style="width:45%;"><div class="sig-line"><div class="sig-lbl">Authorized Signature</div><div class="sig-name"><?= htmlspecialchars($companyName ?? '') ?></div></div></td>
        <td style="width:10%;"></td>
        <?php if ($stampBase64): ?>
        <td style="width:45%; text-align:right; vertical-align:bottom;"><img src="<?= $stampBase64 ?>" alt="Seal" style="height:90px; opacity:0.85;"></td>
        <?php else: ?>
        <td style="width:45%;"><div class="sig-line"><div class="sig-lbl">Company Seal</div></div></td>
        <?php endif; ?>
    </tr>
</table>

<div class="footer">
    <strong><?= htmlspecialchars($companyName ?? '') ?></strong> &nbsp;·&nbsp;
    <?= htmlspecialchars($companyAddress ?? '') ?> &nbsp;·&nbsp;
    <?= htmlspecialchars($companyPhone ?? '') ?> &nbsp;·&nbsp;
    <?= htmlspecialchars($companyEmail ?? '') ?>
    <?php if ($tursabBase64): ?>
    &nbsp;&nbsp;<img src="<?= $tursabBase64 ?>" alt="TURSAB"> Licensed Travel Agency
    <?php endif; ?>
    <br><span style="font-size:7.5px; color:#aaa;">Generated <?= date('d F Y · H:i') ?> &nbsp;·&nbsp; Tour Code: <?= htmlspecialchars($t['tour_code'] ?? '') ?></span>
</div>

</div>
</body>
</html>
