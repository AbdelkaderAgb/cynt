<?php
/**
 * Invoice PDF — Professional Official Document
 * Variables: $invoice, $companyName, $companyAddress, $companyPhone, $companyEmail
 */
$inv = $invoice;
$logoPath = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath = ROOT_PATH . '/stamp.png';
$logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64 = file_exists($stampPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath)) : '';
$payMethods = ['cash'=>'Cash','bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','paypal'=>'PayPal','other'=>'Other'];
$statusLabels = ['draft'=>'Draft','sent'=>'Sent','paid'=>'Paid','overdue'=>'Overdue','pending'=>'Pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice <?= htmlspecialchars($inv['invoice_no']) ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #222; line-height: 1.5; }
    .page { padding: 25px 35px; }

    /* Header band */
    .header { border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 18px; }
    .header table { width: 100%; }
    .header .logo-cell { width: 50%; vertical-align: bottom; }
    .header .logo-cell img { height: 60px; vertical-align: middle; margin-right: 8px; }
    .header .doc-cell { width: 50%; text-align: right; vertical-align: bottom; }
    .header .doc-type { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; color: #111; }
    .header .doc-no { font-size: 11px; color: #555; margin-top: 2px; }

    /* Two-column info block */
    .info-block { width: 100%; margin-bottom: 16px; border: 1px solid #ccc; border-collapse: collapse; }
    .info-block td { padding: 6px 10px; vertical-align: top; border: 1px solid #ccc; }
    .info-block .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; display: block; margin-bottom: 1px; }
    .info-block .val { font-size: 11px; color: #111; font-weight: bold; }

    /* Financial table */
    .fin-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .fin-table th { background: #f5f5f5; border: 1px solid #ccc; padding: 7px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #333; text-align: left; }
    .fin-table th.r { text-align: right; }
    .fin-table td { border: 1px solid #ccc; padding: 7px 10px; }
    .fin-table .total td { background: #f5f5f5; font-weight: bold; font-size: 13px; border-top: 2px solid #222; }
    .fin-table .total .amt { color: #111; font-size: 15px; }
    .r { text-align: right; }

    /* Status stamp */
    .stamp { display: inline-block; border: 2px solid; padding: 4px 16px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transform: rotate(-5deg); margin-top: 6px; }
    .stamp-paid { border-color: #2e7d32; color: #2e7d32; }
    .stamp-overdue { border-color: #c62828; color: #c62828; }
    .stamp-sent { border-color: #1565c0; color: #1565c0; }
    .stamp-draft { border-color: #757575; color: #757575; }
    .stamp-pending { border-color: #e65100; color: #e65100; }

    /* Notes */
    .notes { margin-top: 14px; padding: 8px 10px; border: 1px solid #ddd; background: #fafafa; font-size: 10px; }
    .notes-hd { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; margin-bottom: 3px; }

    /* Authorization / Stamp */
    .auth-section { margin-top: 30px; width: 100%; }
    .auth-section td { vertical-align: bottom; padding: 0; }
    .auth-left { width: 50%; }
    .auth-right { width: 50%; text-align: right; }
    .auth-right img { height: 120px; opacity: 0.9; }
    .auth-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #777; font-weight: bold; }

    /* Footer */
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
                    <div class="doc-type">Invoice</div>
                    <div class="doc-no"><?= htmlspecialchars($inv['invoice_no']) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- COMPANY & DATE INFO -->
    <table class="info-block">
        <tr>
            <td style="width:50%;">
                <span class="lbl">Bill To</span>
                <span class="val"><?= htmlspecialchars($inv['company_name']) ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Invoice Date</span>
                <span class="val"><?= isset($inv['invoice_date']) ? date('d.m.Y', strtotime($inv['invoice_date'])) : '—' ?></span>
            </td>
            <td style="width:25%;">
                <span class="lbl">Due Date</span>
                <span class="val"><?= isset($inv['due_date']) ? date('d.m.Y', strtotime($inv['due_date'])) : '—' ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="lbl">Payment Method</span>
                <span class="val"><?= htmlspecialchars($payMethods[$inv['payment_method'] ?? ''] ?? ucfirst($inv['payment_method'] ?? '—')) ?></span>
            </td>
            <td>
                <span class="lbl">Currency</span>
                <span class="val"><?= htmlspecialchars($inv['currency'] ?? 'USD') ?></span>
            </td>
            <td>
                <span class="lbl">Status</span>
                <span class="val"><?= htmlspecialchars($statusLabels[$inv['status'] ?? 'draft'] ?? ucfirst($inv['status'] ?? 'draft')) ?></span>
            </td>
        </tr>
    </table>

    <!-- FINANCIAL SUMMARY -->
    <table class="fin-table">
        <thead>
            <tr><th>Description</th><th class="r" style="width:35%;">Amount (<?= htmlspecialchars($inv['currency'] ?? 'USD') ?>)</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Subtotal</td>
                <td class="r"><?= number_format($inv['subtotal'] ?? $inv['total_amount'] ?? 0, 2) ?></td>
            </tr>
            <?php if (!empty($inv['tax_rate'])): ?>
            <tr>
                <td>Tax (<?= $inv['tax_rate'] ?>%)</td>
                <td class="r"><?= number_format($inv['tax_amount'] ?? 0, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($inv['discount']) && $inv['discount'] > 0): ?>
            <tr>
                <td>Discount</td>
                <td class="r" style="color:#c62828;">−<?= number_format($inv['discount'], 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total">
                <td>TOTAL</td>
                <td class="r amt"><?= number_format($inv['total_amount'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td class="r" style="font-weight:bold;"><?= number_format($inv['paid_amount'] ?? 0, 2) ?></td>
            </tr>
            <?php $balance = ($inv['total_amount'] ?? 0) - ($inv['paid_amount'] ?? 0); if ($balance > 0.01): ?>
            <tr>
                <td style="font-weight:bold;">Balance Due</td>
                <td class="r" style="font-weight:bold; color:#c62828; font-size:13px;"><?= number_format($balance, 2) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- STATUS STAMP -->
    <div style="text-align:center; margin: 10px 0;">
        <?php $st = $inv['status'] ?? 'draft'; ?>
        <span class="stamp stamp-<?= $st ?>"><?= htmlspecialchars($statusLabels[$st] ?? ucfirst($st)) ?></span>
    </div>

    <!-- NOTES -->
    <?php if (!empty($inv['notes'])): ?>
    <div class="notes">
        <div class="notes-hd">Notes</div>
        <?= nl2br(htmlspecialchars($inv['notes'])) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($inv['terms'])): ?>
    <div class="notes" style="margin-top:8px;">
        <div class="notes-hd">Terms &amp; Conditions</div>
        <?= nl2br(htmlspecialchars($inv['terms'])) ?>
    </div>
    <?php endif; ?>

    <!-- AUTHORIZATION & STAMP -->
    <?php if ($stampBase64): ?>
    <table class="auth-section">
        <tr>
            <td class="auth-left">
                <div class="auth-label">Authorized By</div>
                <div style="margin-top: 4px; font-size: 11px; font-weight: bold; color: #222;"><?= htmlspecialchars($companyName) ?></div>
            </td>
            <td class="auth-right">
                <img src="<?= $stampBase64 ?>" alt="Company Seal">
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
