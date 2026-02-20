<?php
/**
 * Hotel Voucher PDF — Same design language as Transfer Voucher PDF.
 * Identical: header-band, accent-strip, meta-row, sec-head, info-grid, notes-box, sig-area, footer.
 * Hotel-specific: Stay Hero banner, Room Breakdown table, Guest List (with title/age), Guest Program.
 */
$v          = $voucher;
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

/* ── Reference maps ── */
$customers = json_decode($v['customers'] ?? '[]', true) ?: [];

$roomTypeLabels = [
    'SNG'=>'Single','DBL'=>'Double','TRP'=>'Triple','QUAD'=>'Quad',
    'SUIT'=>'Suite','VILLA'=>'Villa','STUDIO'=>'Studio','APART'=>'Apart',
    // legacy long names
    'standard'=>'Standard','superior'=>'Superior','deluxe'=>'Deluxe',
    'suite'=>'Suite','family'=>'Family','economy'=>'Economy',
];

/* Unified board resolver: handles both short (BB,HB) and long (bed_breakfast) keys */
$boardBadgeMap = [
    'room_only'           => ['RO',  '#4b5563', '#f3f4f6', 'Room Only'],
    'bed_breakfast'       => ['BB',  '#1d4ed8', '#dbeafe', 'Bed & Breakfast'],
    'half_board'          => ['HB',  '#0f766e', '#ccfbf1', 'Half Board'],
    'full_board'          => ['FB',  '#7c3aed', '#ede9fe', 'Full Board'],
    'all_inclusive'       => ['AI',  '#b45309', '#fef3c7', 'All Inclusive'],
    'ultra_all_inclusive' => ['UAI', '#9a3412', '#ffedd5', 'Ultra All Inclusive'],
    'RO'  => ['RO',  '#4b5563', '#f3f4f6', 'Room Only'],
    'BB'  => ['BB',  '#1d4ed8', '#dbeafe', 'Bed & Breakfast'],
    'HB'  => ['HB',  '#0f766e', '#ccfbf1', 'Half Board'],
    'FB'  => ['FB',  '#7c3aed', '#ede9fe', 'Full Board'],
    'AI'  => ['AI',  '#b45309', '#fef3c7', 'All Inclusive'],
    'UAI' => ['UAI', '#9a3412', '#ffedd5', 'Ultra All Inclusive'],
];
$getBoardInfo = function(string $key) use ($boardBadgeMap): array {
    return $boardBadgeMap[$key] ?? [$key ?: '—', '#374151', '#f3f4f6', ucwords(str_replace('_', ' ', $key)) ?: '—'];
};
// Keep alias for backward-compat references
$boardTypes = array_column($boardBadgeMap, 3, 0) + ['room_only'=>'Room Only','bed_breakfast'=>'Bed & Breakfast','half_board'=>'Half Board','full_board'=>'Full Board','all_inclusive'=>'All Inclusive','ultra_all_inclusive'=>'Ultra All Inclusive'];
$boardBadge = $boardBadgeMap;
$transferTypes = ['without'=>'Without Transfer','one_way'=>'One Way Transfer','round_trip'=>'Round Trip Transfer'];
$statusLabels  = ['pending'=>'PENDING','confirmed'=>'CONFIRMED','checked_in'=>'CHECKED IN','checked_out'=>'CHECKED OUT','cancelled'=>'CANCELLED','no_show'=>'NO SHOW'];
$statusColors  = ['pending'=>'#b45309','confirmed'=>'#1d4ed8','checked_in'=>'#0f766e','checked_out'=>'#166534','cancelled'=>'#991b1b','no_show'=>'#374151'];
$statusBg      = ['pending'=>'#fef3c7','confirmed'=>'#dbeafe','checked_in'=>'#ccfbf1','checked_out'=>'#dcfce7','cancelled'=>'#fee2e2','no_show'=>'#f3f4f6'];

$pdfLang = $currentLang ?? 'en';
$pdfDir  = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';

$st      = $v['status'] ?? 'pending';
$stLabel = $statusLabels[$st] ?? strtoupper(str_replace('_', ' ', $st));
$stColor = $statusColors[$st] ?? '#374151';
$stBg    = $statusBg[$st]    ?? '#f3f4f6';

/* ── Pax totals ── */
$totalAdults   = (int)($v['adults']   ?? 0);
$totalChildren = (int)($v['children'] ?? 0);
$totalInfants  = (int)($v['infants']  ?? 0);
$totalGuests   = $totalAdults + $totalChildren + $totalInfants;

/* ── Rooms — handle both new multi-hotel format and legacy flat format ── */
$hotelsData = [];  // multi-hotel structure: [{hotel_id, hotel_name, city, rooms:[...]}, ...]
$rooms      = [];  // flat list of all rooms (for backward-compat sections)

if (!empty($v['rooms_json'])) {
    $decoded = json_decode($v['rooms_json'], true) ?: [];
    if (!empty($decoded)) {
        if (isset($decoded[0]['rooms'])) {
            // New multi-hotel format
            $hotelsData = $decoded;
            foreach ($hotelsData as $ht) {
                foreach ((array)($ht['rooms'] ?? []) as $r) $rooms[] = $r;
            }
        } else {
            // Legacy flat rooms array
            $rooms = $decoded;
        }
    }
}
if (empty($rooms)) {
    $rooms = [[
        'type'     => $v['room_type']  ?? '',
        'board'    => $v['board_type'] ?? '',
        'adults'   => $totalAdults,
        'children' => $totalChildren,
    ]];
}
// If multi-hotel, use first hotel block for primary hotel name and dates
if (!empty($hotelsData)) {
    if (empty($v['hotel_name'])) $v['hotel_name'] = $hotelsData[0]['hotel_name'] ?? '';
    // Use first hotel's per-hotel dates if they exist
    $ht0 = $hotelsData[0];
    if (!empty($ht0['checkIn']))  $v['check_in']  = $ht0['checkIn'];
    if (!empty($ht0['checkOut'])) $v['check_out'] = $ht0['checkOut'];
    if (!empty($ht0['nights']))   $v['nights']    = (int)$ht0['nights'];
}
$isMultiHotel = count($hotelsData) > 1;
$mainBoardKey = $rooms[0]['board'] ?? ($v['board_type'] ?? '');
$bbData       = $boardBadge[$mainBoardKey] ?? ['?', '#374151', '#f3f4f6'];

/* ── Stay dates ── */
$checkIn  = $v['check_in']  ?? '';
$checkOut = $v['check_out'] ?? '';
$nights   = (int)($v['nights'] ?? 1);
$ciDay    = !empty($checkIn)  ? date('l', strtotime($checkIn))  : '';
$coDay    = !empty($checkOut) ? date('l', strtotime($checkOut)) : '';

/* ── Transfer ── */
$transferType  = $v['transfer_type'] ?? 'without';
$hasTransfer   = $transferType !== 'without';
$transferLabel = $transferTypes[$transferType] ?? $transferType;

/* ── Guest program ── */
$guestProgram       = $guestProgram ?? [];
$additionalServices = json_decode($v['additional_services'] ?? '[]', true) ?: [];
$hasProgram         = !empty($guestProgram);
$hasAdditional      = !$hasProgram && !empty($additionalServices);
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Hotel Voucher · <?= htmlspecialchars($v['voucher_no']) ?></title>
<style>
/* ── IDENTICAL to Transfer Voucher PDF ── */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"DejaVu Sans",Arial,Helvetica,sans-serif; font-size:11px; color:#1a2332; line-height:1.55; background:#ffffff; }

.page { padding:0; }

/* ══ HEADER BAND — white background ══ */
.header-band {
    background:#ffffff;
    padding:20px 36px 0;
    position:relative;
}
.header-band-inner { width:100%; border-collapse:collapse; }
.hd-logo-cell  { vertical-align:middle; width:50%; padding-bottom:20px; }
.hd-title-cell { vertical-align:middle; text-align:right; width:50%; padding-bottom:20px; }

.hd-doctype {
    font-size:24px;
    font-weight:bold;
    letter-spacing:4px;
    text-transform:uppercase;
    color:#1a5276;
}
.hd-subtitle {
    font-size:9px;
    letter-spacing:2px;
    color:#6b7280;
    margin-top:4px;
    text-transform:uppercase;
}

/* ══ ACCENT STRIP (single gold — same as transfer voucher) ══ */
.accent-strip {
    height:4px;
    background:linear-gradient(90deg, #9a7c3f 0%, #d4af37 50%, #9a7c3f 100%);
}

/* ══ BODY ══ */
.body { padding:0 36px 36px; }

/* ══ META ROW (same as transfer voucher) ══ */
.meta-row {
    width:100%;
    border-collapse:collapse;
    margin-top:18px;
    margin-bottom:18px;
}
.meta-row td { padding:0; vertical-align:middle; }
.vno-number {
    font-size:22px;
    font-weight:bold;
    letter-spacing:2.5px;
    color:#1a5276;
    font-family:"Courier New",monospace;
}
.vno-meta {
    font-size:9px;
    color:#6b7280;
    margin-top:3px;
    letter-spacing:.3px;
}
.status-pill {
    display:inline-block;
    padding:5px 16px;
    font-size:9.5px;
    font-weight:bold;
    letter-spacing:2.5px;
    text-transform:uppercase;
    border-radius:20px;
    border:1.5px solid;
}
.divider { border:none; border-top:1.5px solid #e5e7eb; margin:0 0 18px; }

/* ══ SECTION HEADING (same as transfer voucher) ══ */
.sec-head {
    font-size:8px;
    font-weight:bold;
    text-transform:uppercase;
    letter-spacing:2.5px;
    color:#1a5276;
    border-bottom:2px solid #1a5276;
    padding-bottom:4px;
    margin-bottom:10px;
}
.sec-note {
    font-size:7.5px;
    font-weight:normal;
    letter-spacing:0;
    color:#6b7280;
    margin-left:8px;
}

/* ══ INFO GRID (same as transfer voucher) ══ */
.info-grid { width:100%; border-collapse:collapse; }
.info-grid td { padding:9px 12px; border:1px solid #e5e7eb; vertical-align:top; }
.info-grid tr:first-child td { background:#f8fafc; }
.lbl    { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#6b7280; font-weight:bold; display:block; margin-bottom:3px; }
.val    { font-size:13px;   font-weight:bold; color:#1a2332; }
.val-sm { font-size:11.5px; font-weight:bold; color:#1a2332; }
.passport-mono { font-family:"Courier New",monospace; font-size:10.5px; letter-spacing:.5px; color:#1a5276; }

/* ══ STAY HERO BANNER (hotel-specific, uses same border/bg palette) ══ */
.stay-hero { width:100%; border-collapse:collapse; margin-bottom:18px; }
.stay-hero td { border:1px solid #e5e7eb; padding:0; vertical-align:middle; }
.stay-in-cell  { width:34%; background:#f8fafc; padding:14px 16px; vertical-align:top; }
.stay-out-cell { width:34%; background:#f8fafc; padding:14px 16px; vertical-align:top; }
.stay-arr-cell { width:4%;  text-align:center;  vertical-align:middle; background:#ffffff; border-left:none; border-right:none; padding:0 4px; }
.stay-nts-cell { width:17%; background:#eaf4fb; border:1px solid #bee3f8; text-align:center; padding:14px 8px; vertical-align:middle; }
.stay-brd-cell { width:11%; text-align:center;  padding:14px 8px; vertical-align:middle; background:#f8fafc; }

.stay-lbl   { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#6b7280; font-weight:bold; margin-bottom:5px; }
.stay-day   { font-size:32px; font-weight:bold; color:#1a5276; line-height:1; }
.stay-month { font-size:13px; font-weight:bold; color:#1a5276; margin-left:2px; }
.stay-sub   { font-size:9px; color:#6b7280; margin-top:4px; }

.nights-num { font-size:38px; font-weight:bold; color:#1a5276; line-height:1; display:block; }
.nights-lbl { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#5a8aaa; font-weight:bold; margin-top:4px; }

.board-code { font-size:17px; font-weight:bold; display:block; margin-bottom:3px; }
.board-lbl  { font-size:8px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; font-weight:bold; }

/* ══ ROOMS TABLE (same dark-navy th as passenger manifest) ══ */
.pax-tbl { width:100%; border-collapse:collapse; }
.pax-tbl th { padding:7px 10px; background:#eaf4fb; color:#1a5276; font-size:8px; text-transform:uppercase; letter-spacing:1.2px; font-weight:bold; border:1px solid #bee3f8; border-bottom:2px solid #1a5276; text-align:left; }
.pax-tbl td { padding:7px 10px; border:1px solid #e5e7eb; font-size:11px; vertical-align:middle; }
.pax-tbl tr:nth-child(even) td { background:#f8fafc; }
.lead-tag   { display:inline-block; font-size:7px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; padding:1px 5px; border-radius:2px; background:#fef9c3; color:#854d0e; margin-left:5px; vertical-align:middle; }
.room-badge { display:inline-block; font-size:8px; font-weight:bold; letter-spacing:.8px; text-transform:uppercase; padding:2px 8px; border-radius:3px; background:#dbeafe; color:#1e40af; }
.board-pill { display:inline-block; font-size:8px; font-weight:bold; padding:2px 8px; border-radius:10px; }

/* ══ NOTES (same gold scheme as transfer voucher) ══ */
.notes-box {
    border-left:3px solid #9a7c3f;
    padding:9px 14px;
    background:#fffdf5;
    font-size:11px;
    color:#4a3a1a;
    margin-top:0;
}
.notes-lbl { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:1.2px; color:#9a7c3f; margin-bottom:4px; }

/* ══ TRANSFER DETAILS (same info-grid style) ══ */
.transfer-tbl { width:100%; border-collapse:collapse; }
.transfer-tbl td { padding:9px 12px; border:1px solid #e5e7eb; vertical-align:top; background:#f8fafc; }

/* ══ SIGNATURE & STAMP (same as transfer voucher) ══ */
.sig-area { width:100%; border-collapse:collapse; margin-top:30px; }
.sig-area td { padding:0; vertical-align:bottom; }
.sig-line {
    border-top:1px solid #9ca3af;
    padding-top:6px;
    margin-top:40px;
}
.sig-lbl  { font-size:7px; text-transform:uppercase; letter-spacing:1px; color:#6b7280; font-weight:bold; }
.sig-name { font-size:10px; font-weight:bold; color:#1a5276; margin-top:2px; }

/* ══ FOOTER (same as transfer voucher) ══ */
.footer {
    margin-top:22px;
    padding-top:8px;
    border-top:1px solid #e5e7eb;
    text-align:center;
    font-size:8.5px;
    color:#6b7280;
}
.footer img { height:18px; vertical-align:middle; margin-left:6px; }
</style>
</head>
<body>
<div class="page">

<!-- ════════════════════════════════════
     1. HEADER BAND (dark navy — identical to transfer voucher)
════════════════════════════════════ -->
<div class="header-band">
    <table class="header-band-inner">
        <tr>
            <td class="hd-logo-cell">
                <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="height:76px; vertical-align:middle;">
                    <?php if ($partnerLogoBase64): ?>
                    <img src="<?= $partnerLogoBase64 ?>" alt="Partner" style="height:28px; vertical-align:middle; margin-left:10px; opacity:.85;">
                    <?php endif; ?>
                <?php else: ?>
                    <div style="font-size:18px; font-weight:bold; color:#1a5276; letter-spacing:2px;"><?= htmlspecialchars($companyName ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td class="hd-title-cell">
                <div class="hd-doctype">Hotel Voucher</div>
                <div class="hd-subtitle">Official Accommodation Document</div>
            </td>
        </tr>
    </table>
</div>
<!-- Single gold accent strip — identical to transfer voucher -->
<div class="accent-strip"></div>

<div class="body">

<!-- ════════════════════════════════════
     2. VOUCHER META ROW (identical to transfer voucher)
════════════════════════════════════ -->
<table class="meta-row">
    <tr>
        <td>
            <div class="vno-number"><?= htmlspecialchars($v['voucher_no']) ?></div>
            <div class="vno-meta">
                Issued <?= date('d F Y', strtotime($v['created_at'] ?? 'now')) ?>
                &nbsp;·&nbsp; <?= $nights ?> night<?= $nights !== 1 ? 's' : '' ?>
                &nbsp;·&nbsp; <?= $totalGuests ?> guest<?= $totalGuests !== 1 ? 's' : '' ?>
                &nbsp;·&nbsp; <?= count($rooms) ?> room<?= count($rooms) !== 1 ? 's' : '' ?>
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
     3. BOOKING DETAILS (info-grid — identical to transfer voucher)
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:0;">Booking Details</div>
<table class="info-grid" style="margin-bottom:18px;">
    <tr>
        <td style="width:40%;">
            <span class="lbl">Travel Agency / Company</span>
            <span class="val"><?= htmlspecialchars($v['company_name'] ?? '—') ?></span>
            <?php if (!empty($v['telephone'])): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:3px;">Tel:&nbsp;<?= htmlspecialchars($v['telephone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($v['address'])): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:2px;">&#9679;&nbsp;<?= htmlspecialchars($v['address']) ?></div>
            <?php endif; ?>
        </td>
        <td style="width:30%;">
            <span class="lbl">Lead Guest</span>
            <span class="val"><?= htmlspecialchars($v['guest_name'] ?? '—') ?></span>
            <?php if (!empty($v['passenger_passport'])): ?>
            <div style="font-size:8.5px; color:#6b7280; margin-top:3px; font-family:'Courier New',monospace; letter-spacing:.5px;">
                ID:&nbsp;<?= htmlspecialchars($v['passenger_passport']) ?>
            </div>
            <?php endif; ?>
        </td>
        <td style="width:30%;">
            <span class="lbl">Hotel<?= count($hotelsData) > 1 ? 's' : '' ?></span>
            <?php if (count($hotelsData) > 1): ?>
                <?php foreach ($hotelsData as $hb): ?>
                <div style="font-size:9px; font-weight:bold; color:#1a5276; line-height:1.6;">
                    &#9632;&nbsp;<?= htmlspecialchars($hb['hotel_name'] ?? '—') ?>
                    <?php if (!empty($hb['city'])): ?><span style="font-size:7.5px; color:#6b7280; font-weight:normal;">&nbsp;·&nbsp;<?= htmlspecialchars($hb['city']) ?></span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="val-sm" style="color:#1a5276;"><?= htmlspecialchars($v['hotel_name'] ?? '—') ?></span>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- ════════════════════════════════════
     4. HOTEL STAYS — one block per hotel with dates + rooms
════════════════════════════════════ -->
<div class="sec-head">
    Hotel Stay<?= count($hotelsData) > 1 ? 's' : '' ?> &amp; Room Allocation
    <span class="sec-note">
        <?= count($hotelsData) > 1 ? count($hotelsData) . ' hotels · ' : '' ?><?= count($rooms) ?> room<?= count($rooms) !== 1 ? 's' : '' ?>
        &nbsp;·&nbsp; <?= $nights ?> night<?= $nights !== 1 ? 's' : '' ?>
    </span>
</div>

<?php
/* Build the list of hotel blocks to render. If no multi-hotel data, wrap single hotel. */
$renderBlocks = !empty($hotelsData) ? $hotelsData : [[
    'hotel_id'   => $v['hotel_id']  ?? '',
    'hotel_name' => $v['hotel_name'] ?? '',
    'city'       => '',
    'country'    => '',
    'checkIn'    => $checkIn,
    'checkOut'   => $checkOut,
    'nights'     => $nights,
    'rooms'      => $rooms,
]];
foreach ($renderBlocks as $hIdx => $htBlock):
    $htRooms  = (array)($htBlock['rooms'] ?? []);
    $htCi     = $htBlock['checkIn']  ?? $htBlock['check_in']  ?? '';
    $htCo     = $htBlock['checkOut'] ?? $htBlock['check_out'] ?? '';
    $htNights = (int)($htBlock['nights'] ?? 0);
    if (!$htNights && $htCi && $htCo) {
        $htNights = max(1, (int)round((strtotime($htCo) - strtotime($htCi)) / 86400));
    }
    $htName   = $htBlock['hotel_name'] ?? '';
    $htCity   = $htBlock['city'] ?? '';
    // First room board for the Stay Hero row
    $htMainBd = $htRooms[0]['board'] ?? '';
    $htBdInfo = $getBoardInfo($htMainBd);
?>
<!-- Hotel block header -->
<div style="background:#eaf4fb; color:#1a5276; padding:7px 12px; font-size:8px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; border-radius:5px 5px 0 0; border:1px solid #bee3f8; border-bottom:2px solid #1a5276; margin-top:<?= $hIdx > 0 ? '14px' : '0' ?>; display:table; width:100%; box-sizing:border-box;">
    <span style="display:table-cell; vertical-align:middle;">
        <span style="color:#9a7c3f; margin-right:6px; font-size:10px;">&#9632;</span>
        <?= htmlspecialchars($htName ?: ('Hotel ' . ($hIdx + 1))) ?>
        <?php if ($htCity): ?>
        <span style="color:#5a8aaa; font-weight:normal; letter-spacing:0;">&nbsp;&#183;&nbsp;<?= htmlspecialchars($htCity) ?></span>
        <?php endif; ?>
    </span>
    <span style="display:table-cell; text-align:right; vertical-align:middle; font-weight:normal; letter-spacing:0; text-transform:none; color:#1a5276; font-size:7.5px;">
        <?php if ($htCi): ?>
        <span style="color:#9a7c3f;">&#9658;</span>&nbsp;<?= date('d M Y', strtotime($htCi)) ?>
        <?php if (!empty(date('l', strtotime($htCi)))): ?><span style="color:#5a8aaa;">&nbsp;<?= date('l', strtotime($htCi)) ?></span><?php endif; ?>
        <?php endif; ?>
        <?php if ($htCo): ?>&nbsp;&nbsp;<span style="color:#9ca3af;">&#8594;</span>&nbsp;&nbsp;<?= date('d M Y', strtotime($htCo)) ?>
        <?php if (!empty(date('l', strtotime($htCo)))): ?><span style="color:#5a8aaa;">&nbsp;<?= date('l', strtotime($htCo)) ?></span><?php endif; ?>
        <?php endif; ?>
        <?php if ($htNights): ?>&nbsp;&nbsp;<span style="background:#9a7c3f; color:#ffffff; padding:1px 7px; border-radius:3px; font-weight:bold; font-size:8px;"><?= $htNights ?>N</span><?php endif; ?>
    </span>
</div>

<!-- Rooms table -->
<table class="pax-tbl" style="border-radius:0 0 5px 5px; margin-bottom:0;">
    <thead>
        <tr>
            <th style="width:4%; text-align:center;">#</th>
            <th style="width:18%;">Room Code</th>
            <th style="width:22%;">Room Name</th>
            <th style="width:28%;">Board Plan</th>
            <th style="width:14%; text-align:center;">Adults</th>
            <th style="width:14%; text-align:center;">Children</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($htRooms)): ?>
        <tr><td colspan="6" style="text-align:center; color:#9ca3af; font-style:italic;">No room data</td></tr>
        <?php else: ?>
        <?php foreach ($htRooms as $ri => $room):
            $rtKey   = strtoupper(trim($room['type'] ?? ''));
            $bdKey   = $room['board'] ?? '';
            $rtLabel = $roomTypeLabels[$rtKey] ?? $roomTypeLabels[strtolower($rtKey)] ?? ucwords(strtolower($rtKey)) ?: '—';
            $bdInfo  = $getBoardInfo($bdKey);
        ?>
        <tr>
            <td style="text-align:center; color:#6b7280; font-size:8px; font-weight:bold;"><?= $ri + 1 ?></td>
            <td><span class="room-badge"><?= htmlspecialchars($rtKey ?: '—') ?></span></td>
            <td style="font-size:9.5px; color:#1f2937; font-weight:500;"><?= htmlspecialchars($rtLabel) ?></td>
            <td>
                <span class="board-pill" style="background:<?= $bdInfo[2] ?>; color:<?= $bdInfo[1] ?>; font-weight:bold;"><?= htmlspecialchars($bdInfo[0]) ?></span>
                <span style="font-size:9px; color:#374151; margin-left:5px;"><?= htmlspecialchars($bdInfo[3]) ?></span>
            </td>
            <td style="text-align:center; font-weight:bold; font-size:11px;"><?= (int)($room['adults'] ?? 0) ?></td>
            <td style="text-align:center; font-weight:bold; font-size:11px;"><?= (int)($room['children'] ?? 0) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php endforeach; ?>
<div style="margin-bottom:18px;"></div>

<?php if ($hasTransfer): ?>
<!-- ════════════════════════════════════
     6. TRANSFER DETAILS (same info-grid shading)
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">Transfer Details</div>
<table class="transfer-tbl" style="margin-bottom:18px;">
    <tr>
        <td style="width:100%;">
            <span class="lbl">Transfer Type</span>
            <span class="val-sm"><?= htmlspecialchars($transferLabel) ?></span>
        </td>
    </tr>
    <?php if (!empty($v['transfer_date']) || !empty($v['transfer_time'])): ?>
    <tr>
        <td style="width:30%;">
            <span class="lbl">Transfer Date</span>
            <span class="val-sm"><?= !empty($v['transfer_date']) ? date('d M Y', strtotime($v['transfer_date'])) : '—' ?></span>
        </td>
    </tr>
    <tr>
        <td style="width:30%;">
            <span class="lbl">Transfer Time</span>
            <span class="val-sm"><?= htmlspecialchars($v['transfer_time'] ?? '—') ?></span>
        </td>
    </tr>
    <?php if (!empty($v['transfer_notes'])): ?>
    <tr>
        <td>
            <span class="lbl">Notes</span>
            <span class="val-sm"><?= htmlspecialchars($v['transfer_notes']) ?></span>
        </td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
</table>
<?php endif; ?>

<?php if (!empty($customers)): ?>
<!-- ════════════════════════════════════
     7. GUEST LIST (pax-tbl — same as passenger manifest)
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">
    Guest List
    <span class="sec-note"><?= count($customers) ?> guest<?= count($customers) !== 1 ? 's' : '' ?></span>
</div>
<table class="pax-tbl" style="margin-bottom:18px;">
    <thead>
        <tr>
            <th style="width:5%; text-align:center;">#</th>
            <th style="width:10%;">Title</th>
            <th>Full Name</th>
            <th style="width:13%;">Type</th>
            <th style="width:9%; text-align:center;">Age</th>
            <th style="width:22%;">Passport / ID No.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $ci => $c): 
            $cType = strtolower($c['type'] ?? 'adult');
            $cTypeBg = $cType === 'adult' ? ['#dbeafe','#1d4ed8'] : ($cType === 'child' ? ['#dcfce7','#15803d'] : ['#fce7f3','#9d174d']);
        ?>
        <tr>
            <td style="text-align:center; color:#6b7280; font-size:8px; font-weight:bold;"><?= $ci + 1 ?></td>
            <td style="font-size:9px; color:#374151;"><?= htmlspecialchars($c['title'] ?? '—') ?></td>
            <td style="font-weight:<?= $ci === 0 ? 'bold' : '500' ?>; font-size:10.5px;">
                <?= htmlspecialchars($c['name'] ?? '') ?>
                <?php if ($ci === 0): ?><span class="lead-tag">Lead</span><?php endif; ?>
            </td>
            <td>
                <span style="display:inline-block; font-size:7px; font-weight:bold; padding:2px 7px; border-radius:10px; background:<?= $cTypeBg[0] ?>; color:<?= $cTypeBg[1] ?>; text-transform:uppercase; letter-spacing:.5px;">
                    <?= htmlspecialchars(ucfirst($cType)) ?>
                </span>
            </td>
            <td style="text-align:center; font-size:10px; font-weight:bold; color:#374151;"><?= htmlspecialchars($c['age'] ?? '—') ?></td>
            <td class="passport-mono" style="font-size:9px;">
                <?= !empty($c['passport'])
                    ? htmlspecialchars($c['passport'])
                    : '<span style="color:#d1d5db; font-style:italic; font-family:Arial; font-size:8px;">not provided</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if ($hasProgram): ?>
<!-- ════════════════════════════════════
     8a. GUEST PROGRAM (pax-tbl — same dark navy header)
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">Guest Program</div>
<table class="pax-tbl" style="margin-bottom:18px;">
    <thead>
        <tr>
            <th style="width:18%;">Date</th>
            <th style="width:12%;">Time</th>
            <th>Service / Activity</th>
            <th style="width:28%;">Pickup Point</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($guestProgram as $pr): ?>
        <tr>
            <td><?= htmlspecialchars($pr['date'] ?? '—') ?></td>
            <td><?= htmlspecialchars($pr['time'] ?? '—') ?></td>
            <td style="font-weight:bold;"><?= htmlspecialchars($pr['service'] ?? '—') ?></td>
            <td><?= htmlspecialchars($pr['pickup'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php elseif ($hasAdditional): ?>
<!-- ════════════════════════════════════
     8b. ADDITIONAL SERVICES (info-grid)
════════════════════════════════════ -->
<div class="sec-head" style="margin-top:18px;">Additional Services</div>
<table class="info-grid" style="margin-bottom:18px;">
    <?php foreach ($additionalServices as $svc): ?>
    <tr>
        <td style="width:25%;"><span class="lbl"><?= htmlspecialchars(ucfirst($svc['type'] ?? 'Service')) ?></span></td>
        <td><span class="val-sm"><?= nl2br(htmlspecialchars($svc['description'] ?? $svc['name'] ?? '')) ?></span></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (!empty($v['special_requests'])): ?>
<!-- ════════════════════════════════════
     9. SPECIAL REQUESTS (notes-box — same gold as transfer voucher)
════════════════════════════════════ -->
<div class="notes-box" style="margin-top:18px; margin-bottom:18px;">
    <div class="notes-lbl">Special Requests</div>
    <?= nl2br(htmlspecialchars($v['special_requests'])) ?>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════
     10. SIGNATURE & STAMP (identical to transfer voucher)
════════════════════════════════════ -->
<table class="sig-area">
    <tr>
        <td style="width:50%;"></td>
        <td style="width:50%; text-align:center; vertical-align:bottom;">
            <?php if ($stampBase64): ?>
            <img src="<?= $stampBase64 ?>" alt="Seal" style="height:88px; opacity:.88; display:block; margin:0 auto;">
            <?php endif; ?>
            <div class="sig-line" style="margin-top:<?= $stampBase64 ? '8' : '40' ?>px; text-align:center;">
                <div class="sig-lbl">Authorized Signature</div>
                <div class="sig-name"><?= htmlspecialchars($companyName ?? '') ?></div>
            </div>
        </td>
    </tr>
</table>

<!-- ════════════════════════════════════
     FOOTER (identical to transfer voucher)
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
</body>
</html>
