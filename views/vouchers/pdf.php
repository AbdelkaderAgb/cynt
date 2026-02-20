<?php
/**
 * Transfer Voucher PDF — Premium Boarding-Pass Architecture
 * Enhanced design: timeline route, passenger manifest, fleet assignment, stamp.
 */
$v          = $voucher;
$logoPath   = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath  = ROOT_PATH . '/stamp.png';
$logoBase64   = file_exists($logoPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))   : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64  = file_exists($stampPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))  : '';

$typeLabels   = ['one_way' => 'One Way', 'round_trip' => 'Round Trip', 'multi_stop' => 'Multi Stop'];
$statusLabels = ['pending'=>'PENDING','confirmed'=>'CONFIRMED','completed'=>'COMPLETED','cancelled'=>'CANCELLED','no_show'=>'NO SHOW'];
$statusColors = ['pending'=>'#b45309','confirmed'=>'#1d4ed8','completed'=>'#166534','cancelled'=>'#991b1b','no_show'=>'#374151'];
$statusBg     = ['pending'=>'#fef3c7','confirmed'=>'#dbeafe','completed'=>'#dcfce7','cancelled'=>'#fee2e2','no_show'=>'#f3f4f6'];

$pdfLang = $currentLang ?? 'en';
$pdfDir  = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';

/* ── Partner info ── */
$partnerPhone = ''; $partnerAddress = '';
$partnerRow = null;
if (!empty($v['company_id'])) {
    $partnerRow = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE id = ? LIMIT 1", [(int)$v['company_id']]);
}
if (!$partnerRow && !empty($v['company_name'])) {
    $partnerRow = Database::fetchOne("SELECT phone, address, city, country FROM partners WHERE company_name = ? LIMIT 1", [$v['company_name']]);
}
if ($partnerRow) {
    $partnerPhone   = $partnerRow['phone'] ?? '';
    $partnerAddress = implode(', ', array_filter([$partnerRow['address'] ?? '', $partnerRow['city'] ?? '', $partnerRow['country'] ?? '']));
}

/* ── Stops & guests ── */
$rawStops    = json_decode($v['stops_json'] ?? '[]', true) ?: [];
$extraStops  = array_values(array_filter($rawStops, fn($s) => !empty($s['from']) || !empty($s['to'])));
$isMultiStop = count($extraStops) > 0;

$rawPass = $v['passengers'] ?? '';
$guests  = [];
if (!empty($rawPass) && substr(trim($rawPass), 0, 1) === '[') {
    $guests = json_decode($rawPass, true) ?: [];
}
if (empty($guests)) {
    if (!empty($v['guest_name'])) {
        $guests[] = ['name' => $v['guest_name'], 'passport' => $v['passenger_passport'] ?? ''];
    }
    if (!empty($rawPass) && substr(trim($rawPass), 0, 1) !== '[') {
        foreach (array_filter(array_map('trim', explode("\n", $rawPass))) as $line) {
            if ($line !== ($v['guest_name'] ?? '')) $guests[] = ['name' => $line, 'passport' => ''];
        }
    }
}
$mainGuest  = $guests[0] ?? ['name' => $v['guest_name'] ?? '', 'passport' => $v['passenger_passport'] ?? ''];
$guestCount = count($guests);

/* ── Fleet assignment ── */
$driverName = ''; $vehicleInfo = ''; $guideName = '';
if (!empty($v['driver_id'])) {
    $r = Database::fetchOne("SELECT first_name, last_name FROM drivers WHERE id = ?", [$v['driver_id']]);
    if ($r) $driverName = trim($r['first_name'] . ' ' . $r['last_name']);
}
if (!empty($v['vehicle_id'])) {
    $r = Database::fetchOne("SELECT plate_number, make, model FROM vehicles WHERE id = ?", [$v['vehicle_id']]);
    if ($r) $vehicleInfo = trim($r['plate_number'] . ' · ' . $r['make'] . ' ' . $r['model']);
}
if (!empty($v['guide_id'])) {
    $r = Database::fetchOne("SELECT first_name, last_name FROM tour_guides WHERE id = ?", [$v['guide_id']]);
    if ($r) $guideName = trim($r['first_name'] . ' ' . $r['last_name']);
}

/* ── Status & type ── */
$st        = $v['status'] ?? 'pending';
$stLabel   = $statusLabels[$st] ?? strtoupper($st);
$stColor   = $statusColors[$st] ?? '#374151';
$stBg      = $statusBg[$st] ?? '#f3f4f6';
$mainType  = $v['transfer_type'] ?? 'one_way';
$typeLabel = $typeLabels[$mainType] ?? $mainType;
$isRT      = $mainType === 'round_trip';
$hasReturn = $isRT && !empty($v['return_date']) && $v['return_date'] !== '1970-01-01';
$totalPax  = (int)($v['total_pax'] ?? max($guestCount, 1));

/* ── Build all legs ── */
$allLegs = [[
    'from'    => $v['pickup_location']  ?? '',
    'to'      => $v['dropoff_location'] ?? '',
    'date'    => $v['pickup_date']      ?? '',
    'time'    => $v['pickup_time']      ?? '',
    'type'    => $mainType,
    'retDate' => $v['return_date']      ?? '',
    'retTime' => $v['return_time']      ?? '',
    'flight'  => $v['flight_number']    ?? '',
    'label'   => 'Main Transfer',
    'isMain'  => true,
]];
foreach ($extraStops as $i => $s) {
    $sIsRT = ($s['type'] ?? 'one_way') === 'round_trip';
    $allLegs[] = [
        'from'    => $s['from']  ?? '',
        'to'      => $s['to']    ?? '',
        'date'    => $s['date']  ?? '',
        'time'    => $s['time']  ?? '',
        'type'    => $s['type']  ?? 'one_way',
        'retDate' => $sIsRT ? ($s['returnDate'] ?? $s['retDate'] ?? '') : '',
        'retTime' => $sIsRT ? ($s['returnTime'] ?? $s['retTime'] ?? '') : '',
        'flight'  => '',
        'price'   => (float)($s['price'] ?? 0),
        'label'   => ($i === count($extraStops) - 1) ? 'Final Transfer' : 'Transfer ' . ($i + 2),
        'isMain'  => false,
    ];
}
$totalLegs = count($allLegs);
$hasAssignment = ($driverName || $vehicleInfo || $guideName);
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Transfer Voucher · <?= htmlspecialchars($v['voucher_no']) ?></title>
<style>
/* hard-coded hex — no CSS variables, ensures PDF renderer compatibility */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"DejaVu Sans",Arial,Helvetica,sans-serif; font-size:11px; color:#1a2332; line-height:1.55; background:#ffffff; }
.page { padding:0; }

/* ══════════════════════════════════════
   HEADER BAND — clean white
══════════════════════════════════════ */
.header-band {
    background:#ffffff;
    padding:16px 36px 0;
    border-bottom:2px solid #d1dce8;
}
.header-band-inner { width:100%; border-collapse:collapse; }
.hd-logo-cell  { vertical-align:middle; width:55%; padding-bottom:16px; }
.hd-title-cell { vertical-align:middle; text-align:right; width:45%; padding-bottom:16px; }
.hd-doctype {
    font-size:24px; font-weight:bold; letter-spacing:3px;
    text-transform:uppercase; color:#2c5f7a;
}
.hd-subtitle {
    font-size:9px; letter-spacing:1.8px; color:#8a9ab0;
    margin-top:4px; text-transform:uppercase;
}
.hd-company-contact { font-size:8px; color:#8a9ab0; margin-top:6px; line-height:1.7; }

/* ── Accent strip — lighter gold ── */
.accent-strip { height:2px; background:#b8963e; }

/* ══════════════════════════════════════
   DOCUMENT BODY
══════════════════════════════════════ */
.body { padding:0 36px 36px; }

/* ── Voucher meta row ── */
.meta-row { width:100%; border-collapse:collapse; margin-top:18px; margin-bottom:18px; }
.meta-row td { padding:0; vertical-align:middle; }
.vno-number {
    font-size:22px; font-weight:bold; letter-spacing:2.5px;
    color:#1a2332; font-family:"Courier New",monospace;
}
.vno-meta { font-size:9px; color:#6b7280; margin-top:3px; letter-spacing:.3px; }
.status-pill {
    display:inline-block; padding:5px 16px; font-size:9.5px;
    font-weight:bold; letter-spacing:2.5px; text-transform:uppercase;
    border-radius:20px; border:1.5px solid;
}
.divider { border:none; border-top:1.5px solid #e5e7eb; margin:0 0 18px; }

/* ── Section heading ── */
.sec-head {
    font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:2px;
    color:#3d6b85; border-bottom:1.5px solid #b0cfe0; padding-bottom:4px; margin-bottom:8px;
}
.sec-note { font-size:7.5px; font-weight:normal; letter-spacing:0; color:#8a9ab0; margin-left:8px; }

/* ── Info grid ── */
.info-grid { width:100%; border-collapse:collapse; }
.info-grid td { padding:8px 11px; border:1px solid #e8eef3; vertical-align:top; }
.info-grid tr:first-child td { background:#f8fbfd; }
.lbl { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#8a9ab0; font-weight:bold; display:block; margin-bottom:3px; }
.val     { font-size:13px;   font-weight:bold; color:#1a2332; }
.val-sm  { font-size:11.5px; font-weight:bold; color:#1a2332; }
.val-mono{ font-size:11px;   font-weight:bold; color:#1a2332; font-family:"Courier New",monospace; letter-spacing:.5px; }

/* ── Type badges — lighter ── */
.badge    { display:inline-block; font-size:8px; font-weight:bold; letter-spacing:.8px; text-transform:uppercase; padding:2px 9px; border-radius:3px; }
.badge-ow { background:#e8f1fb; color:#3a6fa8; }
.badge-rt { background:#fdf4e3; color:#9a6f22; }
.badge-ms { background:#e8f6ef; color:#2e7a52; }

/* ══════════════════════════════════════
   ROUTE TIMELINE — compact multi-leg
══════════════════════════════════════ */
.route-wrap { margin-bottom:4px; }
.leg-card      { width:100%; border-collapse:collapse; margin-bottom:3px; border:1px solid #d8e9f5; }
.leg-card-main { border-color:#4a7fa0; border-left:3px solid #4a7fa0; }
.leg-num-cell       { width:24px; background:#4a7fa0; text-align:center; vertical-align:middle; padding:6px 0; }
.leg-num-cell-stop  { background:#6a96b0; }
.leg-num-badge {
    display:inline-block; width:17px; height:17px; line-height:17px;
    background:#ffffff; color:#4a7fa0;
    border-radius:50%; text-align:center; font-size:8.5px; font-weight:bold;
}
.leg-num-badge-stop { color:#6a96b0; }
.leg-content      { padding:6px 10px; vertical-align:top; background:#ffffff; }
.leg-content-main { background:#fafcfe; }
.leg-label      { font-size:7px; font-weight:bold; text-transform:uppercase; letter-spacing:1.5px; color:#8a9ab0; margin-bottom:4px; }
.leg-label-main { color:#4a7fa0; }
.route-inner    { width:100%; border-collapse:collapse; }
.route-inner td { border:none; padding:0; vertical-align:middle; }
.loc-lbl { font-size:7px; text-transform:uppercase; letter-spacing:.8px; color:#8a9ab0; font-weight:bold; margin-bottom:2px; }
.loc-val { font-size:11px; font-weight:bold; color:#1a2332; line-height:1.2; }
.arrow-cell { width:8%; text-align:center; vertical-align:middle; }
.arrow-icon { font-size:15px; color:#b8963e; font-weight:bold; }
.leg-dates { margin-top:5px; padding-top:5px; border-top:1px dashed #e8eef3; }
.leg-dates-inner    { width:100%; border-collapse:collapse; }
.leg-dates-inner td { border:none; padding:0; vertical-align:top; }
.date-lbl       { font-size:7px; text-transform:uppercase; letter-spacing:.8px; color:#8a9ab0; font-weight:bold; margin-bottom:2px; }
.date-val       { font-size:10.5px; font-weight:bold; color:#1a2332; }
.date-time      { font-size:9px; color:#8a9ab0; margin-top:1px; }
.date-return-lbl{ color:#b8963e; }
.date-return-val{ color:#9a6f22; }
.flight-badge {
    display:inline-block; font-size:6.5px; font-weight:bold;
    font-family:"Courier New",monospace; letter-spacing:1px;
    padding:1px 6px; background:#eef5fb; color:#3d6b85; border-radius:3px; margin-top:2px;
}
.leg-connector { text-align:center; font-size:10px; color:#d8e9f5; padding:1px 0; }

/* ══════════════════════════════════════
   PASSENGER MANIFEST
══════════════════════════════════════ */
.pax-tbl { width:100%; border-collapse:collapse; margin-top:0; }
.pax-tbl th { padding:7px 10px; background:#f0f6fb; color:#3d6b85; font-size:8px; text-transform:uppercase; letter-spacing:1.2px; font-weight:bold; border:1px solid #d8e9f5; border-bottom:1.5px solid #4a7fa0; text-align:left; }
.pax-tbl td { padding:7px 10px; border:1px solid #e8eef3; font-size:11px; vertical-align:middle; }
.pax-tbl tr:nth-child(even) td { background:#fafcfe; }
.lead-tag { display:inline-block; font-size:7px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; padding:1px 5px; border-radius:2px; background:#fef9c3; color:#854d0e; margin-left:5px; vertical-align:middle; }
.passport-mono { font-family:"Courier New",monospace; font-size:10px; letter-spacing:.5px; color:#1a2332; }

/* ══════════════════════════════════════
   FLEET ASSIGNMENT
══════════════════════════════════════ */
.assign-tbl { width:100%; border-collapse:collapse; }
.assign-tbl td { padding:9px 12px; border:1px solid #e5e7eb; vertical-align:top; background:#f4f9fd; }
.assign-icon { font-size:10px; font-weight:bold; color:#1a5276; display:block; margin-bottom:4px; }

/* ══════════════════════════════════════
   NOTES
══════════════════════════════════════ */
.notes-box { border-left:3px solid #9a7c3f; padding:9px 14px; background:#fffdf5; font-size:11px; color:#4a3a1a; margin-top:0; }
.notes-lbl { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:1.2px; color:#9a7c3f; margin-bottom:4px; }

/* ══════════════════════════════════════
   SIGNATURE & STAMP
══════════════════════════════════════ */
.sig-area    { width:100%; border-collapse:collapse; margin-top:30px; }
.sig-area td { padding:0; vertical-align:bottom; }
.sig-line    { border-top:1px solid #9ca3af; padding-top:6px; margin-top:40px; }
.sig-lbl     { font-size:7px; text-transform:uppercase; letter-spacing:1px; color:#6b7280; font-weight:bold; }
.sig-name    { font-size:10px; font-weight:bold; color:#1a2332; margin-top:2px; }

/* ══════════════════════════════════════
   FOOTER
══════════════════════════════════════ */
.footer { margin-top:22px; padding-top:8px; border-top:1px solid #e5e7eb; text-align:center; font-size:8.5px; color:#6b7280; }
.footer img { height:18px; vertical-align:middle; margin-left:6px; }
</style>
</head>
<body>
<div class="page">

<!-- ════════════════════════════════════
     1. HEADER BAND (dark navy)
════════════════════════════════════ -->
<div class="header-band">
    <table class="header-band-inner">
        <tr>
            <td class="hd-logo-cell">
                <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="height:76px; vertical-align:middle;">
                <?php else: ?>
                    <div style="font-size:18px; font-weight:bold; color:#1a5276; letter-spacing:2px;"><?= htmlspecialchars($companyName ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td class="hd-title-cell">
                <div class="hd-doctype">Transfer Voucher</div>
                <div class="hd-subtitle">Official Transportation Document</div>
            </td>
        </tr>
    </table>
</div>
<div class="accent-strip"></div>

<div class="body">

<!-- ════════════════════════════════════
     2. VOUCHER META ROW
════════════════════════════════════ -->
<table class="meta-row">
    <tr>
        <td>
            <div class="vno-number"><?= htmlspecialchars($v['voucher_no']) ?></div>
            <div class="vno-meta">
                Issued <?= date('d F Y', strtotime($v['created_at'] ?? 'now')) ?>
                &nbsp;·&nbsp; <?= htmlspecialchars($typeLabel) ?>
                &nbsp;·&nbsp; <?= $totalLegs ?> leg<?= $totalLegs > 1 ? 's' : '' ?>
                &nbsp;·&nbsp; <?= $totalPax ?> passenger<?= $totalPax > 1 ? 's' : '' ?>
            </div>
        </td>
        <td style="text-align:right;">
            <span class="status-pill" style="border-color:<?= $stColor ?>; color:<?= $stColor ?>; background:<?= $stBg ?>;">
                <?= $stLabel ?>
            </span>
        </td>
    </tr>
</table>
<hr class="divider">

<!-- ════════════════════════════════════
     3. BOOKING DETAILS
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:0;">Booking Details</div>
<table class="info-grid" style="margin-bottom:18px;">
    <tr>
        <td style="width:38%;">
            <span class="lbl">Travel Agency / Company</span>
            <span class="val"><?= htmlspecialchars($v['company_name'] ?? '—') ?></span>
            <?php if ($partnerPhone): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:3px;">&#9990;&nbsp;<?= htmlspecialchars($partnerPhone) ?></div>
            <?php endif; ?>
            <?php if ($partnerAddress): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:2px;">&#9632;&nbsp;<?= htmlspecialchars($partnerAddress) ?></div>
            <?php endif; ?>
        </td>
        <td style="width:19%;">
            <span class="lbl">Transfer Type</span>
            <div style="margin-top:4px;">
                <?php if ($isRT): ?>
                    <span class="badge badge-rt">&#8646; Round Trip</span>
                <?php elseif ($isMultiStop): ?>
                    <span class="badge badge-ms">&#9776; Multi Stop</span>
                <?php else: ?>
                    <span class="badge badge-ow">&#8594; One Way</span>
                <?php endif; ?>
            </div>
        </td>
        <td style="width:13%; text-align:center;">
            <span class="lbl">Total Pax</span>
            <span style="font-size:30px; font-weight:bold; color:#1a2332; line-height:1.1; display:block; margin-top:2px;"><?= $totalPax ?></span>
        </td>
        <td style="width:30%;">
            <span class="lbl">Lead Passenger</span>
            <span class="val-sm"><?= htmlspecialchars($mainGuest['name'] ?: '—') ?></span>
            <?php if (!empty($mainGuest['passport'])): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:3px; font-family:'Courier New',monospace;">
                ID:&nbsp;<?= htmlspecialchars($mainGuest['passport']) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['flight_number'])): ?>
            <div style="margin-top:5px;">
                <span class="flight-badge">FL:&nbsp;<?= htmlspecialchars($v['flight_number']) ?></span>
            </div>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- ════════════════════════════════════
     4. ROUTE & SCHEDULE — TIMELINE
════════════════════════════════════ -->
<div class="sec-head">
    Route &amp; Schedule
    <span class="sec-note"><?= $totalLegs ?> leg<?= $totalLegs > 1 ? 's' : '' ?></span>
</div>

<div class="route-wrap">
<?php foreach ($allLegs as $li => $leg):
    $isLegRT  = ($leg['type'] ?? 'one_way') === 'round_trip';
    $isMainLeg = $leg['isMain'] ?? false;
    $d = $leg['date'] ?? '';
    $t = $leg['time'] ?? '';
    $rDate = $leg['retDate'] ?? '';
    $rTime = $leg['retTime'] ?? '';
    $hasRetLeg = $isLegRT && !empty($rDate) && $rDate !== '1970-01-01';
    $legFlight = $leg['flight'] ?? '';
    $typeBg  = $isLegRT ? '#fef3c7' : '#dbeafe';
    $typeClr = $isLegRT ? '#b45309' : '#1d4ed8';
    $typeTxt = $isLegRT ? '&#8646; Round Trip' : '&#8594; One Way';
?>
    <table class="leg-card <?= $isMainLeg ? 'leg-card-main' : '' ?>">
        <tr>
            <!-- Number badge -->
            <td class="leg-num-cell <?= !$isMainLeg ? 'leg-num-cell-stop' : '' ?>" style="width:38px;">
                <span class="leg-num-badge <?= !$isMainLeg ? 'leg-num-badge-stop' : '' ?>"><?= $li + 1 ?></span>
            </td>
            <!-- Content -->
            <td class="leg-content <?= $isMainLeg ? 'leg-content-main' : '' ?>">
                <div class="leg-label <?= $isMainLeg ? 'leg-label-main' : '' ?>"><?= htmlspecialchars($leg['label'] ?? 'Transfer ' . ($li + 1)) ?></div>
                <!-- Route row -->
                <table class="route-inner">
                    <tr>
                        <td style="width:42%;">
                            <div class="loc-lbl">From</div>
                            <div class="loc-val"><?= htmlspecialchars(($leg['from'] ?? '') ?: '—') ?></div>
                        </td>
                        <td class="arrow-cell">
                            <div class="arrow-icon">&#8594;</div>
                            <div style="margin-top:2px;">
                                <span style="display:inline-block; font-size:6.5px; font-weight:bold; padding:1px 6px; border-radius:2px; background:<?= $typeBg ?>; color:<?= $typeClr ?>;"><?= $typeTxt ?></span>
                            </div>
                        </td>
                        <td style="width:42%;">
                            <div class="loc-lbl">To</div>
                            <div class="loc-val"><?= htmlspecialchars(($leg['to'] ?? '') ?: '—') ?></div>
                        </td>
                    </tr>
                </table>
                <!-- Dates & flight -->
                <div class="leg-dates">
                    <table class="leg-dates-inner">
                        <tr>
                            <td style="width:<?= $hasRetLeg ? '45%' : '60%' ?>;">
                                <div class="date-lbl">Departure</div>
                                <div class="date-val"><?= (!empty($d) && $d !== '1970-01-01') ? date('d M Y', strtotime($d)) : '—' ?></div>
                                <div class="date-time"><?= !empty($t) ? htmlspecialchars($t) : '—' ?></div>
                            </td>
                            <?php if ($hasRetLeg): ?>
                            <td style="width:45%;">
                                <div class="date-lbl date-return-lbl">&#8646; Return</div>
                                <div class="date-val date-return-val"><?= date('d M Y', strtotime($rDate)) ?></div>
                                <div class="date-time" style="color:#9a7c3f;"><?= !empty($rTime) ? htmlspecialchars($rTime) : '—' ?></div>
                            </td>
                            <?php endif; ?>
                            <?php if (!empty($legFlight)): ?>
                            <td style="width:<?= $hasRetLeg ? '10%' : '40%' ?>; text-align:<?= $hasRetLeg ? 'right' : 'left' ?>; vertical-align:bottom;">
                                <span class="flight-badge">FL:&nbsp;<?= htmlspecialchars($legFlight) ?></span>
                            </td>
                            <?php endif; ?>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    <?php if ($li < $totalLegs - 1): ?>
    <div class="leg-connector">&#8942;</div>
    <?php endif; ?>
<?php endforeach; ?>
</div>

<?php if ($hasAssignment): ?>
<!-- ════════════════════════════════════
     5. FLEET ASSIGNMENT
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">Fleet Assignment</div>
<table class="assign-tbl" style="margin-bottom:18px;">
    <tr>
        <td style="width:34%;">
            <span class="assign-icon">Driver</span>
            <span class="lbl">Driver</span>
            <span class="val-sm"><?= htmlspecialchars($driverName ?: '—') ?></span>
        </td>
        <td style="width:33%;">
            <span class="assign-icon">Vehicle</span>
            <span class="lbl">Vehicle</span>
            <span class="val-sm"><?= htmlspecialchars($vehicleInfo ?: '—') ?></span>
        </td>
        <td style="width:33%;">
            <span class="assign-icon">Guide</span>
            <span class="lbl">Tour Guide</span>
            <span class="val-sm"><?= htmlspecialchars($guideName ?: '—') ?></span>
        </td>
    </tr>
</table>
<?php endif; ?>

<?php if (!empty($guests)): ?>
<!-- ════════════════════════════════════
     6. PASSENGER MANIFEST
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">
    Passenger Manifest
    <span class="sec-note"><?= $guestCount ?> passenger<?= $guestCount > 1 ? 's' : '' ?></span>
</div>
<table class="pax-tbl" style="margin-bottom:18px;">
    <thead>
        <tr>
            <th style="width:5%; text-align:center;">#</th>
            <th style="width:52%;">Full Name</th>
            <th>Passport / ID No.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($guests as $gi => $g): ?>
        <tr>
            <td style="text-align:center; color:#6b7280; font-size:8.5px; font-weight:bold;"><?= $gi + 1 ?></td>
            <td>
                <?= htmlspecialchars($g['name'] ?? '') ?>
                <?php if ($gi === 0): ?><span class="lead-tag">Lead</span><?php endif; ?>
            </td>
            <td class="passport-mono">
                <?= !empty($g['passport'])
                    ? htmlspecialchars($g['passport'])
                    : '<span style="color:#9ca3af; font-style:italic; font-family:Arial,sans-serif;">—</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($v['notes'])): ?>
<!-- ════════════════════════════════════
     7. SPECIAL INSTRUCTIONS
════════════════════════════════════ -->
<div class="notes-box" style="margin-top:18px; margin-bottom:18px;">
    <div class="notes-lbl">Special Instructions</div>
    <?= nl2br(htmlspecialchars($v['notes'])) ?>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════
     8. STAMP
════════════════════════════════════ -->
<?php if ($stampBase64): ?>
<div style="text-align:center; margin-top:22px;">
    <img src="<?= $stampBase64 ?>" alt="Seal" style="height:80px; opacity:.88;">
    <div style="margin-top:4px; font-size:7px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#6b7280;">AUTHORIZED SIGNATURE By CYNTOURISM</div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════
     FOOTER
════════════════════════════════════ -->
<div class="footer">
    <strong><?= htmlspecialchars($companyName ?? '') ?></strong>
    <?php if (!empty($companyPhone)): ?>&nbsp;·&nbsp;<?= htmlspecialchars($companyPhone) ?><?php endif; ?>
    <?php if (!empty($companyEmail)): ?>&nbsp;·&nbsp;<?= htmlspecialchars($companyEmail) ?><?php endif; ?>
    <?php if ($tursabBase64): ?>
    &nbsp;&nbsp;<img src="<?= $tursabBase64 ?>" alt="TURSAB"> Licensed Travel Agency
    <?php endif; ?>
    <br>
    <span style="font-size:7px; color:#9ca3af;">
        Generated <?= date('d F Y · H:i') ?> &nbsp;·&nbsp; Voucher: <?= htmlspecialchars($v['voucher_no']) ?>
    </span>
</div>

</div><!-- .body -->
</div><!-- .page -->

<?php if (!empty($_GET['print'])): ?>
<script>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 400); });</script>
<?php endif; ?>
</body>
</html>
