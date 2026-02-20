<?php
/**
 * Invoice PDF — Professional Corporate Document
 * Transfer invoices read main_leg_json + stops_json + guests_json directly.
 *
 * Section order:
 *   1. Header (white)        5. Passenger Manifest
 *   2. Invoice meta bar      6. Notes / Terms
 *   3. Bill To               7. Financial Summary  ← last
 *   4. Route / Line Items    8. Signatures + Footer
 */
$inv        = $invoice;
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

$payMethods   = ['cash'=>'Cash','bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card',
                 'paypal'=>'PayPal','check'=>'Check','other'=>'Other','card'=>'Credit Card','transfer'=>'Bank Transfer'];
$statusLabels = ['draft'=>'DRAFT','sent'=>'SENT','paid'=>'PAID','partial'=>'PARTIAL',
                 'overdue'=>'OVERDUE','pending'=>'PENDING','cancelled'=>'CANCELLED'];
$statusColors = ['draft'=>'#6b7280','sent'=>'#1d4ed8','paid'=>'#166534','partial'=>'#b45309',
                 'overdue'=>'#991b1b','pending'=>'#b45309','cancelled'=>'#6b7280'];

$pdfLang = $currentLang ?? 'en';
$pdfDir  = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';

$st      = $inv['status'] ?? 'draft';
$stLabel = $statusLabels[$st] ?? strtoupper($st);
$stColor = $statusColors[$st] ?? '#6b7280';
$curr    = htmlspecialchars($inv['currency'] ?? 'USD');

$isTransfer = ($inv['type'] ?? '') === 'transfer';
$isHotel    = ($inv['type'] ?? '') === 'hotel';

/* ── Hotel JSON data ── */
$hotelsData = [];
if ($isHotel) {
    $hotelsData = json_decode($inv['hotels_json'] ?? '[]', true) ?: [];
}

/* ── Transfer JSON data (read directly — reliable even when $invoiceStops not passed) ── */
$ml = $stops = $guests = [];
if ($isTransfer) {
    $ml    = json_decode($inv['main_leg_json'] ?? '{}', true) ?: [];
    $stops = array_values(array_filter(
        json_decode($inv['stops_json']  ?? '[]', true) ?: [],
        fn($s) => !empty($s['from']) || !empty($s['to'])
    ));
}
/* guests_json applies to transfer AND hotel invoices */
$guests = array_values(array_filter(
    json_decode($inv['guests_json'] ?? '[]', true) ?: [],
    fn($g) => !empty($g['name'])
));

/* ── Build ordered legs array ── */
$allLegs = [];
if ($isTransfer) {
    if (!empty($ml['from']) || !empty($ml['to'])) {
        $allLegs[] = [
            'from'    => $ml['from']       ?? '',
            'to'      => $ml['to']         ?? '',
            'date'    => $ml['date']       ?? '',
            'time'    => $ml['time']       ?? '',
            'type'    => $ml['type']       ?? 'one_way',
            'flight'  => $ml['flight']     ?? '',
            'retDate' => $ml['returnDate'] ?? '',
            'retTime' => $ml['returnTime'] ?? '',
            'price'   => (float)($ml['price'] ?? 0),
        ];
    }
    foreach ($stops as $s) {
        $sRetDate = $s['returnDate'] ?? $s['retDate'] ?? '';
        $sRetTime = $s['returnTime'] ?? $s['retTime'] ?? '';
        $sIsRT    = ($s['type'] ?? 'one_way') === 'round_trip';
        $allLegs[] = [
            'from'    => $s['from']  ?? '',
            'to'      => $s['to']    ?? '',
            'date'    => $s['date']  ?? '',
            'time'    => $s['time']  ?? '',
            'type'    => $s['type']  ?? 'one_way',
            'flight'  => '',
            'retDate' => $sIsRT ? $sRetDate : '',
            'retTime' => $sIsRT ? $sRetTime : '',
            'price'   => (float)($s['price'] ?? 0),
        ];
    }
    if (empty($allLegs) && !empty($invoiceItems)) {
        foreach ($invoiceItems as $item) {
            $allLegs[] = ['from'=>'','to'=>'','date'=>'','time'=>'','type'=>'one_way',
                          'flight'=>'','retDate'=>'','retTime'=>'',
                          'price'=>(float)($item['total_price'] ?? 0),
                          '_desc' => $item['description'] ?? ''];
        }
    }
}

$totalLegs  = count($allLegs);
$guestCount = count($guests);
$mainFlight = trim($ml['flight'] ?? '');
$hasReturn  = !empty($ml['returnDate']) && $ml['returnDate'] !== '1970-01-01' && $ml['returnDate'] !== '';
$mainType   = $ml['type'] ?? 'one_way';
$balance    = (float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0);
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title><?= $isTransfer ? 'Transfer Invoice' : ($isHotel ? 'Hotel Invoice' : 'Invoice') ?> · <?= htmlspecialchars($inv['invoice_no']) ?></title>
<style>
/* hard-coded hex — no CSS variables, ensures PDF renderer compatibility */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:"DejaVu Sans",Arial,Helvetica,sans-serif; font-size:10px; color:#1a2332; line-height:1.52; background:#fff; }
.page { padding:0 34px 34px; }

/* ── HEADER ── */
.hd-band { background:#ffffff; margin:0 -34px; padding:22px 34px 18px; border-bottom:3px solid #1a5276; }
.hd-doctype { font-size:22px; font-weight:bold; letter-spacing:3px; text-transform:uppercase; color:#1a5276; }
.hd-sub { font-size:8.5px; letter-spacing:.5px; color:#5a6272; margin-top:3px; }
.hd-company { font-size:10px; font-weight:bold; color:#1a2332; margin-top:6px; }
.hd-contact { font-size:8.5px; color:#5a6272; margin-top:1px; }

/* ── Invoice meta strip ── */
.meta-strip { background:#f8f9fc; border:1px solid #e2e4e9; padding:10px 14px; margin-top:16px; margin-bottom:0; }
.inv-no { font-size:16px; font-weight:bold; letter-spacing:2px; color:#1a2332; font-family:"Courier New",monospace; }
.inv-meta { font-size:8.5px; color:#5a6272; margin-top:2px; }
.status-pill { display:inline-block; padding:4px 14px; font-size:8.5px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; border-radius:20px; border:1.5px solid; }

/* ── Section headings ── */
.sec { font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:2px; color:#1a5276; border-bottom:2px solid #1a5276; padding-bottom:4px; margin:18px 0 0; }
.sec-note { font-weight:normal; font-size:7px; letter-spacing:0; color:#5a6272; margin-left:8px; }

/* ── Info grid ── */
.igrid { width:100%; border-collapse:collapse; margin-top:8px; }
.igrid td { padding:8px 11px; border:1px solid #e2e4e9; vertical-align:top; }
.igrid tr:first-child td { background:#f8f9fc; }
.lbl { font-size:7px; text-transform:uppercase; letter-spacing:.9px; color:#5a6272; font-weight:bold; display:block; margin-bottom:3px; }
.val { font-size:11px; font-weight:bold; color:#1a2332; }
.val-sm { font-size:10px; font-weight:bold; color:#1a2332; }

/* ── Transfer summary bar ── */
.tsummary { background:#f8f9fc; border:1px solid #e2e4e9; padding:8px 12px; margin-top:8px; }
.tsum-lbl { font-size:7px; text-transform:uppercase; letter-spacing:.8px; color:#5a6272; font-weight:bold; }
.tsum-val { font-size:13px; font-weight:bold; color:#1a2332; margin-top:1px; }
.badge-ow { display:inline-block; font-size:7.5px; font-weight:bold; letter-spacing:.6px; text-transform:uppercase; padding:2px 9px; border-radius:3px; background:#e8f0fe; color:#1d4ed8; }
.badge-rt { display:inline-block; font-size:7.5px; font-weight:bold; letter-spacing:.6px; text-transform:uppercase; padding:2px 9px; border-radius:3px; background:#fef3e2; color:#b45309; }

/* ── Route table ── */
.route-tbl { width:100%; border-collapse:collapse; margin-top:8px; }
.route-tbl th { padding:7px 9px; background:#eaf4fb; color:#1a5276; font-size:7px; text-transform:uppercase; letter-spacing:1px; font-weight:bold; border:1px solid #c8dff0; border-bottom:2px solid #1a5276; text-align:left; }
.route-tbl th.rc { text-align:right; }
.route-tbl td { padding:9px 9px; border:1px solid #e2e4e9; font-size:9.5px; vertical-align:top; }
.route-tbl tr:nth-child(even) td { background:#f8f9fc; }
.leg-num { display:inline-block; width:20px; height:20px; line-height:20px; background:#1a5276; color:#ffffff; border-radius:50%; text-align:center; font-size:8.5px; font-weight:bold; }
.leg-num-gold { background:#9a7c3f; }
.loc-lbl { font-size:7px; color:#5a6272; text-transform:uppercase; letter-spacing:.6px; font-weight:bold; margin-bottom:2px; }
.loc-val { font-size:11px; font-weight:bold; color:#1a2332; }
.leg-meta { font-size:8px; color:#5a6272; margin-top:3px; }
.leg-return { font-size:8.5px; color:#9a7c3f; font-weight:bold; margin-top:3px; }

/* ── Generic items table ── */
.items-tbl { width:100%; border-collapse:collapse; margin-top:8px; }
.items-tbl th { padding:7px 9px; background:#eaf4fb; color:#1a5276; font-size:7px; text-transform:uppercase; letter-spacing:1px; font-weight:bold; border:1px solid #c8dff0; border-bottom:2px solid #1a5276; }
.items-tbl th.r { text-align:right; }
.items-tbl td { padding:7px 9px; border:1px solid #e2e4e9; font-size:10px; vertical-align:top; }
.items-tbl tr:nth-child(even) td { background:#f8f9fc; }
.items-tbl .r { text-align:right; }

/* ── Hotel booking table ── */
.hotel-section { margin-top:12px; }
.hotel-hdr { background:#1a5276; color:#ffffff; padding:7px 11px; font-size:9px; font-weight:bold; letter-spacing:.5px; }
.hotel-hdr-sub { font-size:7.5px; font-weight:normal; opacity:.85; margin-top:1px; }
.hotel-tbl { width:100%; border-collapse:collapse; }
.hotel-tbl th { padding:6px 9px; background:#eaf4fb; color:#1a5276; font-size:7px; text-transform:uppercase; letter-spacing:1px; font-weight:bold; border:1px solid #c8dff0; border-bottom:2px solid #1a5276; text-align:left; }
.hotel-tbl th.r { text-align:right; }
.hotel-tbl td { padding:8px 9px; border:1px solid #e2e4e9; font-size:9.5px; vertical-align:top; }
.hotel-tbl tr:nth-child(even) td { background:#f8f9fc; }
.hotel-tbl .r { text-align:right; }
.hotel-subtotal td { background:#f0f4ff !important; border-top:2px solid #1a5276 !important; font-weight:bold; font-size:10px; }
.occ-badge { display:inline-block; font-size:6.5px; font-weight:bold; letter-spacing:.7px; text-transform:uppercase; padding:1px 5px; border-radius:2px; margin-right:2px; }
.occ-adl { background:#dbeafe; color:#1d4ed8; }
.occ-chd { background:#fef3c7; color:#b45309; }
.occ-inf { background:#ede9fe; color:#7c3aed; }

/* ── Passenger manifest ── */
.pax-tbl { width:100%; border-collapse:collapse; margin-top:8px; }
.pax-tbl th { padding:7px 9px; background:#eaf4fb; color:#1a5276; font-size:7px; text-transform:uppercase; letter-spacing:1px; font-weight:bold; border:1px solid #c8dff0; border-bottom:2px solid #1a5276; text-align:left; }
.pax-tbl td { padding:7px 9px; border:1px solid #e2e4e9; font-size:10px; }
.pax-tbl tr:nth-child(even) td { background:#f8f9fc; }
.lead-badge { display:inline-block; font-size:6.5px; font-weight:bold; letter-spacing:.8px; text-transform:uppercase; padding:1px 5px; border-radius:2px; background:#fef9c3; color:#854d0e; margin-left:5px; vertical-align:middle; }

/* ── Notes / Terms ── */
.notes-box { border-left:3px solid #9a7c3f; padding:8px 12px; background:#fffdf7; font-size:9.5px; color:#4a3a1a; margin-top:10px; }
.notes-lbl { font-size:7px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#9a7c3f; margin-bottom:3px; }
.terms-box { border-left:3px solid #e2e4e9; padding:8px 12px; background:#f9f9fb; font-size:9px; color:#5a6272; margin-top:8px; }
.terms-lbl { font-size:7px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#5a6272; margin-bottom:3px; }

/* ── Financial summary ── */
.fin-outer { width:100%; border-collapse:collapse; margin-top:8px; }
.fin-outer td.fin-note { border:none; padding:0 14px 0 0; vertical-align:top; width:44%; }
.fin-outer td.fin-table { border:none; padding:0; vertical-align:top; width:56%; }
.fin-tbl { width:100%; border-collapse:collapse; }
.fin-tbl td { padding:6px 11px; border:1px solid #e2e4e9; font-size:10px; }
.fin-tbl .r { text-align:right; font-weight:bold; }
.fin-tbl .muted-row td { background:#f8f9fc; }
.fin-total-row td { padding:8px 11px; border:1px solid #e2e4e9; border-top:2px solid #1a5276; background:#eef2ff; font-weight:bold; font-size:13px; }
.fin-total-row .r { font-size:15px; color:#1a2332; }
.fin-paid-row td { padding:6px 11px; border:1px solid #e2e4e9; background:#f0fdf4; font-size:10px; }
.fin-paid-row .r { font-weight:bold; color:#166534; }
.fin-bal-row td { padding:8px 11px; border:1px solid #e2e4e9; border-top:2px solid #dc2626; background:#fff1f2; font-weight:bold; font-size:13px; color:#dc2626; }
.fin-bal-row .r { font-size:15px; }
.fin-done-row td { padding:7px 11px; border:1px solid #e2e4e9; border-top:2px solid #16a34a; background:#f0fdf4; font-weight:bold; font-size:10px; color:#166534; }

/* ── Signatures ── */
.sig-tbl { width:100%; border-collapse:collapse; margin-top:32px; }
.sig-line { border-top:1px solid #b0b8c8; margin-top:32px; padding-top:5px; }
.sig-lbl { font-size:7.5px; text-transform:uppercase; letter-spacing:.6px; color:#5a6272; }

/* ── Footer ── */
.footer { margin-top:24px; padding-top:8px; border-top:1px solid #e2e4e9; text-align:center; font-size:8px; color:#5a6272; }
.footer img { height:20px; vertical-align:middle; margin-left:6px; }
</style>
</head>
<body>
<div class="page">

<!-- ════════ 1. WHITE HEADER ════════ -->
<div class="hd-band">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="vertical-align:middle; width:50%;">
                <?php if ($logoBase64): ?>
                    <img src="<?= $logoBase64 ?>" alt="Logo" style="height:64px; vertical-align:middle;">
                <?php endif; ?>
                <?php if ($partnerLogoBase64): ?>
                    <img src="<?= $partnerLogoBase64 ?>" alt="Partner"
                         style="height:32px; vertical-align:middle; margin-left:12px; opacity:.85;">
                <?php endif; ?>
                <?php if (!$logoBase64): ?>
                    <div class="hd-company"><?= htmlspecialchars($companyName ?? '') ?></div>
                <?php endif; ?>

            </td>
            <td style="vertical-align:top; text-align:right; width:50%;">
                <div class="hd-doctype"><?= $isTransfer ? 'Transfer Invoice' : ($isHotel ? 'Hotel Invoice' : 'Invoice') ?></div>
                <div class="hd-sub">Official Tax Document</div>
            </td>
        </tr>
    </table>
</div>

<!-- ════════ 2. INVOICE META STRIP ════════ -->
<div class="meta-strip">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:0; vertical-align:middle;">
                <div class="inv-no"><?= htmlspecialchars($inv['invoice_no']) ?></div>
                <div class="inv-meta">
                    Issued:&nbsp;<?= isset($inv['invoice_date']) ? date('d F Y', strtotime($inv['invoice_date'])) : '—' ?>
                    &nbsp;·&nbsp;
                    Due:&nbsp;<?= isset($inv['due_date']) ? date('d F Y', strtotime($inv['due_date'])) : '—' ?>
                    &nbsp;·&nbsp; <?= $curr ?>
                    <?php if (!empty($inv['payment_method'])): ?>
                    &nbsp;·&nbsp; <?= htmlspecialchars($payMethods[$inv['payment_method']] ?? ucfirst($inv['payment_method'])) ?>
                    <?php endif; ?>
                </div>
            </td>
            <td style="padding:0; text-align:right; vertical-align:middle;">
                <span class="status-pill" style="border-color:<?= $stColor ?>; color:<?= $stColor ?>;"><?= $stLabel ?></span>
            </td>
        </tr>
    </table>
</div>

<!-- ════════ 3. BILL TO ════════ -->
<div class="sec">Bill To</div>
<table class="igrid">
    <tr>
        <td style="width:40%;">
            <span class="lbl">Company / Client</span>
            <span class="val"><?= htmlspecialchars($inv['company_name'] ?? '—') ?></span>
        </td>
        <td style="width:20%;">
            <span class="lbl">Currency</span>
            <span class="val-sm"><?= $curr ?></span>
        </td>
        <td style="width:20%;">
            <span class="lbl">Payment Method</span>
            <span class="val-sm"><?= htmlspecialchars($payMethods[$inv['payment_method'] ?? ''] ?? (empty($inv['payment_method']) ? '—' : ucfirst($inv['payment_method']))) ?></span>
        </td>
        <td style="width:20%;">
            <span class="lbl">Status</span>
            <span style="display:inline-block; padding:3px 10px; font-size:8px; font-weight:bold; letter-spacing:1.5px; text-transform:uppercase; border-radius:12px; border:1.5px solid <?= $stColor ?>; color:<?= $stColor ?>;"><?= $stLabel ?></span>
        </td>
    </tr>
    <tr>
        <td>
            <span class="lbl">Invoice Date</span>
            <span class="val-sm"><?= !empty($inv['invoice_date']) ? date('d M Y', strtotime($inv['invoice_date'])) : '—' ?></span>
        </td>
        <td>
            <span class="lbl">Due Date</span>
            <span class="val-sm"><?= !empty($inv['due_date']) ? date('d M Y', strtotime($inv['due_date'])) : '—' ?></span>
        </td>
        <td colspan="2">
            <span class="lbl">Payment Date</span>
            <span class="val-sm"><?= !empty($inv['payment_date']) ? date('d M Y', strtotime($inv['payment_date'])) : '—' ?></span>
        </td>
    </tr>
<?php
$pContact  = trim($inv['partner_contact'] ?? '');
$pPhone    = trim($inv['partner_phone']   ?? '');
$pEmail    = trim($inv['partner_email']   ?? '');
$pAddress  = trim($inv['partner_address'] ?? '');
$pCity     = trim($inv['partner_city']    ?? '');
$pCountry  = trim($inv['partner_country'] ?? '');
$pLocation = trim(implode(', ', array_filter([$pCity, $pCountry])));
$hasContact = $pContact || $pPhone || $pEmail || $pAddress || $pLocation;
if ($hasContact):
?>
    <tr>
        <td style="width:25%;">
            <?php if ($pContact): ?>
            <span class="lbl">Contact Person</span>
            <span class="val-sm"><?= htmlspecialchars($pContact) ?></span>
            <?php endif; ?>
        </td>
        <td style="width:25%;">
            <?php if ($pPhone): ?>
            <span class="lbl">Phone</span>
            <span class="val-sm"><?= htmlspecialchars($pPhone) ?></span>
            <?php endif; ?>
            <?php if ($pEmail): ?>
            <span class="lbl" style="margin-top:3px; display:block;">Email</span>
            <span class="val-sm"><?= htmlspecialchars($pEmail) ?></span>
            <?php endif; ?>
        </td>
        <td colspan="2" style="width:50%;">
            <?php if ($pAddress || $pLocation): ?>
            <span class="lbl">Address</span>
            <span class="val-sm">
                <?= $pAddress ? htmlspecialchars($pAddress) . ($pLocation ? ', ' : '') : '' ?><?= htmlspecialchars($pLocation) ?>
            </span>
            <?php endif; ?>
        </td>
    </tr>
<?php endif; ?>
</table>

<?php if ($isTransfer): ?>
<!-- Transfer quick-summary bar -->
<div class="tsummary">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="border:none; padding:4px 8px; width:16%;">
                <div class="tsum-lbl">Legs</div>
                <div class="tsum-val"><?= $totalLegs ?></div>
            </td>
            <td style="border:none; padding:4px 8px; width:16%;">
                <div class="tsum-lbl">Passengers</div>
                <div class="tsum-val"><?= max($guestCount, 1) ?></div>
            </td>
            <td style="border:none; padding:4px 8px; width:20%;">
                <div class="tsum-lbl">Main Type</div>
                <div style="margin-top:3px;">
                    <?php if ($mainType === 'round_trip'): ?>
                        <span class="badge-rt">&#8646; Round Trip</span>
                    <?php else: ?>
                        <span class="badge-ow">&#8594; One Way</span>
                    <?php endif; ?>
                </div>
            </td>
            <?php if ($mainFlight): ?>
            <td style="border:none; padding:4px 8px; border-left:1px solid #e2e4e9; width:22%;">
                <div class="tsum-lbl">Flight No.</div>
                <div style="font-size:12px; font-weight:bold; color:#1a2332; font-family:'Courier New',monospace; letter-spacing:1px; margin-top:2px;">
                    FL:&nbsp;<?= htmlspecialchars($mainFlight) ?>
                </div>
            </td>
            <?php endif; ?>
            <?php if ($hasReturn): ?>
            <td style="border:none; padding:4px 8px; border-left:1px solid #e2e4e9;">
                <div class="tsum-lbl">Return Date</div>
                <div style="font-size:10.5px; font-weight:bold; color:#9a7c3f; margin-top:2px;">
                    &#8646;&nbsp;<?= date('d M Y', strtotime($ml['returnDate'])) ?>
                    <?= !empty($ml['returnTime']) ? ' · ' . $ml['returnTime'] : '' ?>
                </div>
            </td>
            <?php endif; ?>
        </tr>
    </table>
</div>
<?php endif; ?>

<?php if ($isTransfer && !empty($allLegs)): ?>
<!-- ════════ 4a. TRANSFER ROUTE & PRICING ════════ -->
<div class="sec">Transfer Route &amp; Pricing
    <span class="sec-note"><?= $totalLegs ?> leg<?= $totalLegs > 1 ? 's' : '' ?> &nbsp;·&nbsp; all prices in <?= $curr ?></span>
</div>
<table class="route-tbl">
    <thead>
        <tr>
            <th style="width:4%; text-align:center;">#</th>
            <th style="width:44%;">Route</th>
            <th style="width:13%; text-align:center;">Type</th>
            <th style="width:22%;">Date &amp; Time</th>
            <th class="rc" style="width:17%;">Price (<?= $curr ?>)</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($allLegs as $li => $leg):
        $isRT    = ($leg['type'] ?? 'one_way') === 'round_trip';
        $typeBg  = $isRT ? '#fef3e2' : '#e8f0fe';
        $typeClr = $isRT ? '#b45309'  : '#1d4ed8';
        $typeLbl = $isRT ? 'Round Trip' : 'One Way';
        $d = $leg['date'] ?? '';
        $t = $leg['time'] ?? '';
        $hasRetLeg = $isRT && !empty($leg['retDate']) && $leg['retDate'] !== '1970-01-01' && $leg['retDate'] !== '';
    ?>
        <tr>
            <td style="text-align:center; vertical-align:top; padding-top:11px;">
                <span class="leg-num <?= $li === 0 ? 'leg-num-gold' : '' ?>"><?= $li + 1 ?></span>
            </td>
            <td style="vertical-align:top;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="border:none; padding:0; width:43%; vertical-align:top;">
                            <div class="loc-lbl">From</div>
                            <div class="loc-val"><?= htmlspecialchars($leg['from'] ?: '—') ?></div>
                        </td>
                        <td style="border:none; padding:0 5px; width:14%; text-align:center; vertical-align:middle;">
                            <span style="font-size:18px; color:#9a7c3f; font-weight:bold;">&#8594;</span>
                        </td>
                        <td style="border:none; padding:0; width:43%; vertical-align:top;">
                            <div class="loc-lbl">To</div>
                            <div class="loc-val"><?= htmlspecialchars($leg['to'] ?: '—') ?></div>
                        </td>
                    </tr>
                </table>
                <?php if (!empty($leg['flight'])): ?>
                <div class="leg-meta">FL:&nbsp;<?= htmlspecialchars($leg['flight']) ?></div>
                <?php endif; ?>
                <?php if (!empty($leg['_desc'])): ?>
                <div class="leg-meta"><?= htmlspecialchars($leg['_desc']) ?></div>
                <?php endif; ?>
            </td>
            <td style="text-align:center; vertical-align:top; padding-top:11px;">
                <span style="display:inline-block; font-size:7px; font-weight:bold; letter-spacing:.6px; text-transform:uppercase;
                             padding:2px 8px; border-radius:3px; background:<?= $typeBg ?>; color:<?= $typeClr ?>;">
                    <?= $typeLbl ?>
                </span>
            </td>
            <td style="vertical-align:top; padding-top:8px;">
                <!-- Departure -->
                <div style="margin-bottom:<?= $hasRetLeg ? '6px' : '0' ?>;">
                    <div style="font-size:7px; text-transform:uppercase; letter-spacing:.6px; color:#5a6272; font-weight:bold; margin-bottom:1px;">Depart</div>
                    <div style="font-size:10px; font-weight:bold; color:#1a2332;">
                        <?= (!empty($d) && $d !== '1970-01-01') ? date('d M Y', strtotime($d)) : '—' ?>
                    </div>
                    <div style="font-size:9px; color:#5a6272; margin-top:1px;">
                        <?= !empty($t) ? htmlspecialchars($t) : '—' ?>
                    </div>
                </div>
                <?php if ($hasRetLeg): ?>
                <!-- Return -->
                <div style="border-top:1px dashed #e2e4e9; padding-top:5px;">
                    <div style="font-size:7px; text-transform:uppercase; letter-spacing:.6px; color:#9a7c3f; font-weight:bold; margin-bottom:1px;">&#8646; Return</div>
                    <div style="font-size:10px; font-weight:bold; color:#1a2332;">
                        <?= date('d M Y', strtotime($leg['retDate'])) ?>
                    </div>
                    <div style="font-size:9px; color:#5a6272; margin-top:1px;">
                        <?= !empty($leg['retTime']) ? htmlspecialchars($leg['retTime']) : '—' ?>
                    </div>
                </div>
                <?php endif; ?>
            </td>
            <td style="text-align:right; vertical-align:top; padding-top:11px; font-weight:bold; font-size:12px; color:#1a2332; white-space:nowrap;">
                <?= $leg['price'] > 0 ? number_format($leg['price'], 2) : '<span style="color:#5a6272; font-weight:normal;">—</span>' ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php elseif ($isHotel && !empty($hotelsData)): ?>
<!-- ════════ 4b. HOTEL BOOKING DETAILS ════════ -->
<div class="sec">Hotel Booking Details
    <span class="sec-note"><?= count($hotelsData) ?> propert<?= count($hotelsData) > 1 ? 'ies' : 'y' ?> &nbsp;·&nbsp; all prices in <?= $curr ?></span>
</div>
<?php foreach ($hotelsData as $hotel):
    $hotelRooms    = $hotel['rooms'] ?? [];
    $hotelSubtotal = 0;
    $checkIn  = !empty($hotel['checkIn'])  ? date('d M Y', strtotime($hotel['checkIn']))  : '—';
    $checkOut = !empty($hotel['checkOut']) ? date('d M Y', strtotime($hotel['checkOut'])) : '—';
    $nights   = (int)($hotel['nights'] ?? 1);
?>
<div class="hotel-section">
    <div class="hotel-hdr">
        <?= htmlspecialchars($hotel['name'] ?? 'Hotel') ?>
        <?php if (!empty($hotel['city']) || !empty($hotel['country'])): ?>
            &nbsp;·&nbsp; <?= htmlspecialchars(trim(($hotel['city'] ?? '') . ', ' . ($hotel['country'] ?? ''), ', ')) ?>
        <?php endif; ?>
        <div class="hotel-hdr-sub">
            Check-in: <?= $checkIn ?> &nbsp;·&nbsp; Check-out: <?= $checkOut ?> &nbsp;·&nbsp; <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>
        </div>
    </div>
    <table class="hotel-tbl">
        <thead>
            <tr>
                <th style="width:24%;">Room Type</th>
                <th style="width:10%;">Board</th>
                <th style="width:18%;">Occupancy</th>
                <th class="r" style="width:8%;">Rooms</th>
                <th class="r" style="width:8%;">Nights</th>
                <th class="r" style="width:14%;">Price/Night</th>
                <th class="r" style="width:18%;">Line Total (<?= $curr ?>)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($hotelRooms as $room):
            $adults    = (int)($room['adults']    ?? 2);
            $children  = (int)($room['children']  ?? 0);
            $infants   = (int)($room['infants']   ?? 0);
            $count     = (int)($room['count']     ?? 1);
            $rNights   = (int)($room['nights']    ?? $nights);
            $basePrice = (float)($room['price']      ?? 0);
            $childRate = (float)($room['childPrice'] ?? 0);
            if ($adults === 0) {
                $lineTotal = $basePrice * $count * $rNights;
            } else {
                $lineTotal = ($basePrice * $count + $childRate * $children * $count) * $rNights;
            }
            $hotelSubtotal += $lineTotal;
            $boardLabel = match(strtoupper($room['board'] ?? 'BB')) {
                'BB'  => 'B&amp;B',
                'HB'  => 'Half Board',
                'FB'  => 'Full Board',
                'AI'  => 'All-Inclusive',
                'RO'  => 'Room Only',
                default => htmlspecialchars($room['board'] ?? 'BB')
            };
        ?>
        <tr>
            <td style="font-weight:bold;"><?= htmlspecialchars($room['roomType'] ?? '—') ?></td>
            <td><?= $boardLabel ?></td>
            <td>
                <?php if ($adults > 0): ?><span class="occ-badge occ-adl"><?= $adults ?>ADL</span><?php endif; ?>
                <?php if ($children > 0): ?><span class="occ-badge occ-chd"><?= $children ?>CHD</span><?php endif; ?>
                <?php if ($infants > 0): ?><span class="occ-badge occ-inf"><?= $infants ?>INF</span><?php endif; ?>
                <?php if ($adults === 0 && $children === 0 && $infants === 0): ?><span style="color:#5a6272; font-size:9px;">—</span><?php endif; ?>
                <?php if ($adults > 0 && $children > 0 && $childRate > 0): ?>
                    <div style="font-size:8px; color:#b45309; margin-top:2px;">+<?= number_format($childRate, 2) ?>/CHD</div>
                <?php endif; ?>
            </td>
            <td class="r"><?= $count ?></td>
            <td class="r"><?= $rNights ?></td>
            <td class="r"><?= number_format($basePrice, 2) ?></td>
            <td class="r" style="font-weight:bold;"><?= number_format($lineTotal, 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="hotel-subtotal">
            <td colspan="6" style="text-align:right; color:#1a5276;"><?= htmlspecialchars($hotel['name'] ?? 'Hotel') ?> Subtotal</td>
            <td class="r" style="color:#1a5276;"><?= number_format($hotelSubtotal, 2) ?> <?= $curr ?></td>
        </tr>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<?php elseif (!empty($invoiceItems)): ?>
<!-- ════════ 4c. GENERIC LINE ITEMS ════════ -->
<div class="sec">Line Items</div>
<table class="items-tbl">
    <thead>
        <tr>
            <th style="text-align:left;">Description</th>
            <th class="r" style="width:9%;">Qty</th>
            <th class="r" style="width:20%;">Unit Price</th>
            <th class="r" style="width:20%;">Total (<?= $curr ?>)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($invoiceItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['description'] ?? '') ?></td>
            <td class="r"><?= (int)($item['quantity'] ?? 1) ?></td>
            <td class="r"><?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
            <td class="r" style="font-weight:bold;"><?= number_format((float)($item['total_price'] ?? 0), 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($guests)): ?>
<!-- ════════ 5. PASSENGER MANIFEST ════════ -->
<div class="sec">Passenger Manifest
    <span class="sec-note"><?= $guestCount ?> passenger<?= $guestCount > 1 ? 's' : '' ?></span>
</div>
<table class="pax-tbl">
    <thead>
        <tr>
            <th style="width:6%; text-align:center;">#</th>
            <th style="width:50%;">Full Name</th>
            <th style="width:44%;">Passport / ID No.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($guests as $gi => $g): ?>
        <tr>
            <td style="text-align:center; color:#5a6272; font-size:9px; font-weight:bold;">
                <?= $gi + 1 ?>
            </td>
            <td>
                <?= htmlspecialchars($g['name']) ?>
                <?php if ($gi === 0): ?><span class="lead-badge">Lead</span><?php endif; ?>
            </td>
            <td style="font-family:'Courier New',monospace; font-size:9.5px; color:#1a2332; letter-spacing:.5px;">
                <?= !empty($g['passport'])
                    ? htmlspecialchars($g['passport'])
                    : '<span style="color:#5a6272; font-style:italic;">—</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- ════════ 7. FINANCIAL SUMMARY ════════ -->
<div class="sec">Financial Summary</div>
<table class="fin-outer">
    <tr>
        <td class="fin-note">
            <?php if ($isTransfer && !empty($allLegs)): ?>
            <div style="background:#f8f9fc; border:1px solid #e2e4e9; padding:10px 12px;">
                <div style="font-size:9px; color:#5a6272; margin-bottom:6px;">
                    <strong style="color:#1a2332;"><?= $totalLegs ?> transfer leg<?= $totalLegs > 1 ? 's' : '' ?></strong>
                    &nbsp;·&nbsp; prices in <strong style="color:#1a2332;"><?= $curr ?></strong>
                </div>
                <?php foreach ($allLegs as $li => $leg): if ($leg['price'] <= 0) continue; ?>
                <div style="display:table; width:100%; font-size:9px; margin-bottom:3px;">
                    <span style="display:table-cell;">
                        <span style="display:inline-block; width:16px; height:16px; line-height:16px; background:<?= $li===0 ? '#9a7c3f' : '#1a5276' ?>; color:#fff; border-radius:50%; text-align:center; font-size:7.5px; font-weight:bold; margin-right:4px; vertical-align:middle;"><?= $li+1 ?></span>
                        <span style="color:#374151;"><?= htmlspecialchars(mb_strimwidth(($leg['from']?:'—').' → '.($leg['to']?:'—'), 0, 40, '…')) ?></span>
                    </span>
                    <span style="display:table-cell; text-align:right; font-weight:bold; color:#1a2332;"><?= number_format($leg['price'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (!empty($guests)): ?>
                <div style="font-size:8px; color:#5a6272; margin-top:8px; border-top:1px solid #e2e4e9; padding-top:5px;">
                    <?= $guestCount ?> passenger<?= $guestCount > 1 ? 's' : '' ?> · see manifest above
                </div>
                <?php endif; ?>
            </div>
            <?php elseif ($isHotel && !empty($hotelsData)): ?>
            <div style="background:#f8f9fc; border:1px solid #e2e4e9; padding:10px 12px; font-size:9px; color:#5a6272;">
                <?php
                    $totalRoomLines = array_sum(array_map(fn($h) => count($h['rooms'] ?? []), $hotelsData));
                ?>
                <strong style="color:#1a2332;"><?= count($hotelsData) ?> hotel propert<?= count($hotelsData) > 1 ? 'ies' : 'y' ?></strong>
                &nbsp;·&nbsp; <?= $totalRoomLines ?> room line<?= $totalRoomLines !== 1 ? 's' : '' ?>
                &nbsp;·&nbsp; <?= $curr ?>
                <?php if (!empty($guests)): ?>
                <div style="margin-top:6px; border-top:1px solid #e2e4e9; padding-top:5px;">
                    <?= $guestCount ?> guest<?= $guestCount > 1 ? 's' : '' ?> · see manifest above
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="background:#f8f9fc; border:1px solid #e2e4e9; padding:10px 12px; font-size:9px; color:#5a6272;">
                <?= !empty($invoiceItems) ? count($invoiceItems) . ' line item' . (count($invoiceItems) > 1 ? 's' : '') : 'No items' ?>
                &nbsp;·&nbsp; <?= $curr ?>
            </div>
            <?php endif; ?>
        </td>
        <td class="fin-table">
            <table class="fin-tbl">
                <tr class="muted-row">
                    <td>Subtotal</td>
                    <td class="r"><?= number_format((float)($inv['subtotal'] ?? $inv['total_amount'] ?? 0), 2) ?> <?= $curr ?></td>
                </tr>
                <?php if ((float)($inv['tax_rate'] ?? 0) > 0): ?>
                <tr>
                    <td>Tax (<?= htmlspecialchars((string)($inv['tax_rate'])) ?>%)</td>
                    <td class="r">+ <?= number_format((float)($inv['tax_amount'] ?? 0), 2) ?> <?= $curr ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float)($inv['discount'] ?? 0) > 0): ?>
                <tr>
                    <td>Discount</td>
                    <td class="r" style="color:#dc2626;">− <?= number_format((float)$inv['discount'], 2) ?> <?= $curr ?></td>
                </tr>
                <?php endif; ?>
                <tr class="fin-total-row">
                    <td>Total Due</td>
                    <td class="r"><?= number_format((float)($inv['total_amount'] ?? 0), 2) ?> <?= $curr ?></td>
                </tr>
                <tr class="fin-paid-row">
                    <td>Amount Paid</td>
                    <td class="r"><?= number_format((float)($inv['paid_amount'] ?? 0), 2) ?> <?= $curr ?></td>
                </tr>
                <?php if ($balance > 0.005): ?>
                <tr class="fin-bal-row">
                    <td>Balance Due</td>
                    <td class="r"><?= number_format($balance, 2) ?> <?= $curr ?></td>
                </tr>
                <?php else: ?>
                <tr class="fin-done-row">
                    <td colspan="2">&#10003;&nbsp; Fully Paid — Thank you!</td>
                </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>

<?php if (!empty($inv['notes'])): ?>
<!-- ════════ 8a. SPECIAL INSTRUCTIONS / NOTES ════════ -->
<div class="notes-box">
    <div class="notes-lbl"><?= $isTransfer ? 'Special Instructions' : 'Notes' ?></div>
    <?= nl2br(htmlspecialchars($inv['notes'])) ?>
</div>
<?php endif; ?>

<?php if (!empty($inv['terms'])): ?>
<!-- ════════ 8b. TERMS & CONDITIONS ════════ -->
<div class="terms-box">
    <div class="terms-lbl">Terms &amp; Conditions</div>
    <?= nl2br(htmlspecialchars($inv['terms'])) ?>
</div>
<?php endif; ?>

<!-- ════════ 9. SIGNATURES ════════ -->
<table class="sig-tbl">
    <tr>
        <?php if ($stampBase64): ?>
        <td style="width:100%; text-align:center; vertical-align:bottom; padding-top:24px;">
            <img src="<?= $stampBase64 ?>" alt="Seal" style="height:90px; opacity:.88; display:block; margin:0 auto;">
            <div style="margin-top:6px; border-top:1px solid #b0b8c8; padding-top:5px; text-align:center; width:140px; margin-left:auto; margin-right:auto;">
                <span style="font-size:7.5px; font-weight:bold; text-transform:uppercase; letter-spacing:1.2px; color:#5a6272;">Authorized Signature</span>
            </div>
        </td>
        <?php else: ?>
        <td style="width:100%; text-align:center; vertical-align:bottom;">
            <div style="display:inline-block; width:200px; border-top:1px solid #b0b8c8; margin-top:40px; padding-top:5px;">
                <span style="font-size:7.5px; text-transform:uppercase; letter-spacing:.6px; color:#5a6272;">Authorized Signature</span>
            </div>
        </td>
        <?php endif; ?>
    </tr>
</table>

<!-- ════════ FOOTER ════════ -->
<div class="footer">
    <strong><?= htmlspecialchars($companyName ?? '') ?></strong>
    <?php if (!empty($companyPhone)): ?>&nbsp;·&nbsp;<?= htmlspecialchars($companyPhone) ?><?php endif; ?>
    <?php if (!empty($companyEmail)): ?>&nbsp;·&nbsp;<?= htmlspecialchars($companyEmail) ?><?php endif; ?>
    <?php if ($tursabBase64): ?>
    &nbsp;&nbsp;<img src="<?= $tursabBase64 ?>" alt="TURSAB"> Licensed Travel Agency
    <?php endif; ?>
    <br>
    <span style="font-size:7px; color:#aaa;">
        Generated <?= date('d F Y · H:i') ?> &nbsp;·&nbsp;
        <?= $isTransfer ? 'Transfer Invoice' : ($isHotel ? 'Hotel Invoice' : 'Invoice') ?>: <?= htmlspecialchars($inv['invoice_no']) ?>
    </span>
</div>

</div><!-- .page -->
<?php if (!empty($_GET['print'])): ?>
<script>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 400); });</script>
<?php endif; ?>
</body>
</html>
