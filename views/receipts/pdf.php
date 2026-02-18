<?php
/**
 * Receipt PDF — Professional Official Document
 * Variables: $receipt, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$r = $receipt;
$logoPath = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath = ROOT_PATH . '/stamp.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64 = file_exists($stampPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath)) : '';
$payMethods = ['cash'=>'Cash','bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','paypal'=>'PayPal','check'=>'Check','other'=>'Other','card'=>'Credit Card','transfer'=>'Bank Transfer'];
$pdfLang = $currentLang ?? 'en';
$pdfDir = (isset($langInfo) && ($langInfo['dir'] ?? 'ltr') === 'rtl') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>" dir="<?= $pdfDir ?>">
<head>
<meta charset="UTF-8">
<title>Receipt <?= htmlspecialchars($r['invoice_no']) ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #222; line-height: 1.5; }
    .page { padding: 25px 35px; }

    .header { border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 18px; }
    .header table { width: 100%; }
    .header .logo-cell { width: 50%; vertical-align: bottom; }
    .header .logo-cell img { height: 60px; vertical-align: middle; margin-right: 8px; }
    .header .doc-cell { width: 50%; text-align: right; vertical-align: bottom; }
    .header .doc-type { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; color: #2e7d32; }
    .header .doc-no { font-size: 11px; color: #555; margin-top: 2px; }

    .info-block { width: 100%; margin-bottom: 16px; border: 1px solid #ccc; border-collapse: collapse; }
    .info-block td { padding: 6px 10px; vertical-align: top; border: 1px solid #ccc; }
    .info-block .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; display: block; margin-bottom: 1px; }
    .info-block .val { font-size: 11px; color: #111; font-weight: bold; }

    .amount-box { width: 100%; border: 2px solid #2e7d32; border-collapse: collapse; margin: 20px 0; }
    .amount-box td { padding: 14px 18px; border: 1px solid #2e7d32; }
    .amount-box .total-label { font-size: 13px; font-weight: bold; color: #2e7d32; text-transform: uppercase; letter-spacing: 1px; }
    .amount-box .total-amount { font-size: 24px; font-weight: bold; color: #111; text-align: right; }

    .paid-stamp { text-align: center; margin: 16px 0; }
    .paid-stamp span { display: inline-block; border: 3px solid #2e7d32; color: #2e7d32; padding: 6px 28px; font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; transform: rotate(-5deg); }

    .notes { margin-top: 14px; padding: 8px 10px; border: 1px solid #ddd; background: #fafafa; font-size: 10px; }
    .notes-hd { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 3px; }

    .auth-section { margin-top: 30px; width: 100%; }
    .auth-section td { vertical-align: bottom; padding: 0; }

    .footer { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px; text-align: center; font-size: 9px; color: #888; }
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
                    <div class="doc-type">Payment Receipt</div>
                    <div class="doc-no">Ref: <?= htmlspecialchars($r['invoice_no']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- PAYMENT INFO -->
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Received From</span>
                <span class="val"><?= htmlspecialchars($r['company_name']) ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Payment Date</span>
                <span class="val"><?= isset($r['payment_date']) ? date('d.m.Y', strtotime($r['payment_date'])) : '—' ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Payment Method</span>
                <span class="val"><?= htmlspecialchars($payMethods[$r['payment_method'] ?? ''] ?? ucfirst($r['payment_method'] ?? '—')) ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Invoice Reference</span>
                <span class="val"><?= htmlspecialchars($r['invoice_no']) ?></span>
            </td>
            <td>
                <span class="lbl">Invoice Date</span>
                <span class="val"><?= isset($r['invoice_date']) ? date('d.m.Y', strtotime($r['invoice_date'])) : '—' ?></span>
            </td>
            <td>
                <span class="lbl">Currency</span>
                <span class="val"><?= htmlspecialchars($r['currency'] ?? 'USD') ?></span>
            </td>
        </tr>
    </table>

    <!-- AMOUNT -->
    <table class="amount-box">
        <tr>
            <td style="width:50%;">
                <div class="total-label">Amount Received</div>
            </td>
            <td style="width:50%;">
                <div class="total-amount"><?= number_format($r['paid_amount'] ?? 0, 2) ?> <?= htmlspecialchars($r['currency'] ?? 'USD') ?></div>
            </td>
        </tr>
    </table>

    <?php if (($r['total_amount'] ?? 0) != ($r['paid_amount'] ?? 0)): ?>
    <table class="info-block" style="margin-top: 10px;">
        <tr>
            <td style="width:50%;"><span class="lbl">Total Invoice Amount</span><span class="val"><?= number_format($r['total_amount'] ?? 0, 2) ?> <?= htmlspecialchars($r['currency'] ?? 'USD') ?></span></td>
            <td style="width:50%;"><span class="lbl">Balance Remaining</span><span class="val" style="color: #c62828;"><?= number_format(($r['total_amount'] ?? 0) - ($r['paid_amount'] ?? 0), 2) ?> <?= htmlspecialchars($r['currency'] ?? 'USD') ?></span></td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- PAID STAMP -->
    <div class="paid-stamp">
        <span>✓ PAID</span>
    </div>

    <!-- NOTES -->
    <?php if (!empty($r['notes'])): ?>
    <div class="notes">
        <div class="notes-hd">Notes</div>
        <?= nl2br(htmlspecialchars($r['notes'])) ?>
    </div>
    <?php endif; ?>

    <!-- AUTHORIZATION & STAMP -->
    <?php if ($stampBase64): ?>
    <table class="auth-section">
        <tr>
            <td style="width:50%; vertical-align:bottom; padding:0;">
                <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.6px; color:#777; font-weight:bold;">Authorized By</div>
                <div style="margin-top: 4px; font-size: 11px; font-weight: bold; color: #222;"><?= htmlspecialchars($companyName) ?></div>
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
