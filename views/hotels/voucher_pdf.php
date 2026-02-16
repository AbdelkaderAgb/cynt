<?php
/**
 * Hotel Voucher PDF — Professional Official Document
 * Variables: $voucher, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$v = $voucher;
$logoPath = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath = ROOT_PATH . '/stamp.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64 = file_exists($stampPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath)) : '';
$customers = json_decode($v['customers'] ?? '[]', true) ?: [];
$roomTypes = ['SNG'=>'Single','DBL'=>'Double','TRP'=>'Triple','QUAD'=>'Quad','SUIT'=>'Suite','VILLA'=>'Villa','STUDIO'=>'Studio','APART'=>'Apart'];
$boardTypes = ['room_only'=>'Room Only','bed_breakfast'=>'Bed & Breakfast','half_board'=>'Half Board','full_board'=>'Full Board','all_inclusive'=>'All Inclusive','ultra_all_inclusive'=>'Ultra All Inclusive'];
$transferTypes = ['without'=>'Without Transfer','one_way'=>'One Way','round_trip'=>'Round Trip'];
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','checked_in'=>'Checked In','completed'=>'Completed','cancelled'=>'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hotel Voucher <?= htmlspecialchars($v['voucher_no']) ?></title>
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

    .guest-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .guest-table th { background: #f5f5f5; border: 1px solid #ccc; padding: 6px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #333; text-align: left; }
    .guest-table td { border: 1px solid #ccc; padding: 6px 10px; font-size: 11px; }

    .stamp { display: inline-block; border: 2px solid; padding: 4px 16px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transform: rotate(-5deg); }
    .stamp-pending { border-color: #e65100; color: #e65100; }
    .stamp-confirmed { border-color: #1565c0; color: #1565c0; }
    .stamp-checked_in { border-color: #00695c; color: #00695c; }
    .stamp-completed { border-color: #2e7d32; color: #2e7d32; }
    .stamp-cancelled { border-color: #c62828; color: #c62828; }

    .notes { margin-top: 12px; padding: 8px 10px; border: 1px solid #ddd; background: #fafafa; font-size: 10px; }
    .notes-hd { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 3px; }

    .footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; text-align: center; font-size: 9px; color: #888; }
    .footer-tursab { margin-top: 6px; }
    .footer-tursab img { height: 28px; vertical-align: middle; margin-right: 6px; }
    .footer-tursab span { font-size: 8px; color: #999; font-style: italic; vertical-align: middle; }
    .r { text-align: right; }
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
                    <div class="doc-type">Hotel Voucher</div>
                    <div class="doc-no"><?= htmlspecialchars($v['voucher_no']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- BOOKING DETAILS -->
    <div class="section-title">Booking Details</div>
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Company</span>
                <span class="val"><?= htmlspecialchars($v['company_name'] ?? '') ?></span>
            </td>
            <td style="width:50%;">
                <span class="lbl">Hotel</span>
                <span class="val"><?= htmlspecialchars($v['hotel_name'] ?? '') ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Address</span>
                <span class="val"><?= htmlspecialchars($v['address'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Telephone</span>
                <span class="val"><?= htmlspecialchars($v['telephone'] ?? '—') ?></span>
            </td>
        </tr>
    </table>

    <!-- STAY DETAILS -->
    <div class="section-title">Stay Details</div>
    <table class="info-block">
        <tr>
            <td style="width:25%;">
                <span class="lbl">Check-in</span>
                <span class="val"><?= date('d.m.Y', strtotime($v['check_in'])) ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Check-out</span>
                <span class="val"><?= date('d.m.Y', strtotime($v['check_out'])) ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Nights</span>
                <span class="val"><?= $v['nights'] ?? '—' ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Room Count</span>
                <span class="val"><?= $v['room_count'] ?? 1 ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Room Type</span>
                <span class="val"><?= htmlspecialchars($roomTypes[$v['room_type']] ?? $v['room_type'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Board Type</span>
                <span class="val"><?= htmlspecialchars($boardTypes[$v['board_type']] ?? $v['board_type'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Transfer</span>
                <span class="val"><?= htmlspecialchars($transferTypes[$v['transfer_type']] ?? $v['transfer_type'] ?? '—') ?></span>
            </td>
            <td>
                <span class="lbl">Pax (Adult/Child/Infant)</span>
                <span class="val"><?= ($v['adults'] ?? 0) ?> / <?= ($v['children'] ?? 0) ?> / <?= ($v['infants'] ?? 0) ?></span>
            </td>
        </tr>
    </table>

    <!-- GUEST LIST -->
    <?php if (!empty($customers)): ?>
    <div class="section-title">Guest List</div>
    <table class="guest-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:12%;">Title</th>
                <th style="width:43%;">Guest Name</th>
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

    <!-- STATUS (vouchers do not include prices) -->
    <table style="width:100%; margin-bottom: 14px;">
        <tr>
            <td style="width:50%; vertical-align:middle;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Total Guests</div>
                <div style="font-size:22px; font-weight:bold; color:#111;"><?= ($v['adults'] ?? 0) + ($v['children'] ?? 0) + ($v['infants'] ?? 0) ?></div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                <?php $st = $v['status'] ?? 'pending'; ?>
                <span class="stamp stamp-<?= $st ?>"><?= htmlspecialchars($statusLabels[$st] ?? ucfirst(str_replace('_', ' ', $st))) ?></span>
            </td>
        </tr>
    </table>

    <!-- GUEST PROGRAM (linked tours/transfers — no prices) -->
    <?php
    $guestProgram = $guestProgram ?? [];
    if (!empty($guestProgram)):
    ?>
    <div class="section-title">Guest Program</div>
    <table class="guest-table">
        <thead>
            <tr>
                <th style="width:18%;">Date</th>
                <th style="width:12%;">Time</th>
                <th style="width:45%;">Service</th>
                <th style="width:25%;">Pickup</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($guestProgram as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['date'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['time'] ?? '—') ?></td>
                <td style="font-weight:bold;"><?= htmlspecialchars($row['service'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['pickup'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else:
    $additionalServices = json_decode($v['additional_services'] ?? '[]', true) ?: [];
    if (!empty($additionalServices)):
    ?>
    <div class="section-title">Additional Services</div>
    <table class="info-block">
        <tbody>
            <?php foreach ($additionalServices as $svc): ?>
            <tr>
                <td>
                    <span class="lbl"><?= htmlspecialchars(ucfirst($svc['type'] ?? 'Service')) ?></span>
                    <span class="val"><?= nl2br(htmlspecialchars($svc['description'] ?? $svc['name'] ?? '')) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; endif; ?>

    <!-- SPECIAL REQUESTS -->
    <?php if (!empty($v['special_requests'])): ?>
    <div class="notes">
        <div class="notes-hd">Special Requests</div>
        <?= nl2br(htmlspecialchars($v['special_requests'])) ?>
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
