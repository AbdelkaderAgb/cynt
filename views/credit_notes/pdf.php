<?php
/**
 * Credit Note PDF Template — green accent, similar to receipt PDF
 * Rendered standalone via Dompdf
 */
$statusColors = [
    'draft'   => ['bg' => '#f1f5f9', 'text' => '#475569'],
    'issued'  => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'applied' => ['bg' => '#d1fae5', 'text' => '#065f46'],
];

$logoPath   = ROOT_PATH . '/assets/images/logo.png';
$tursabPath = ROOT_PATH . '/assets/images/Toursablogo.png';
$stampPath  = ROOT_PATH . '/stamp.png';
$logoBase64   = file_exists($logoPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))   : '';
$tursabBase64 = file_exists($tursabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($tursabPath)) : '';
$stampBase64  = file_exists($stampPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))  : '';

$sc = $statusColors[$cn['status']] ?? $statusColors['draft'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Credit Note <?= e($cn['credit_note_no']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
        .page { padding: 32px 36px; max-width: 680px; margin: 0 auto; }
        .header { display: table; width: 100%; margin-bottom: 24px; border-bottom: 3px solid #0d9488; padding-bottom: 14px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; text-align: right; vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: #0d9488; margin-top: 4px; }
        .company-info { font-size: 9px; color: #666; margin-top: 3px; line-height: 1.5; }
        .doc-title { font-size: 26px; font-weight: bold; color: #0d9488; }
        .doc-no { font-size: 12px; color: #555; margin-top: 4px; }
        .doc-date { font-size: 10px; color: #888; margin-top: 2px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
        .amount-box { background: #0d9488; color: #fff; border-radius: 6px; padding: 18px 24px; text-align: center; margin: 20px 0; }
        .amount-label { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; opacity: .8; }
        .amount-value { font-size: 34px; font-weight: bold; margin-top: 4px; }
        .amount-currency { font-size: 16px; opacity: .85; }
        .info-row { display: table; width: 100%; margin-bottom: 16px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; padding: 12px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; }
        .info-col + .info-col { border-left: none; }
        .info-label { font-size: 9px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
        .info-value { font-size: 12px; font-weight: bold; color: #1a1a2e; }
        .info-sub { font-size: 10px; color: #555; margin-top: 2px; }
        .reason-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px 14px; margin-bottom: 20px; background: #fafafa; }
        .reason-box h4 { font-size: 9px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: .5px; margin-bottom: 5px; }
        .reason-box p { font-size: 10px; color: #475569; white-space: pre-wrap; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 12px; font-size: 9px; color: #94a3b8; text-align: center; margin-top: 24px; }
        .stamp { position: absolute; right: 50px; bottom: 80px; opacity: 0.35; width: 90px; }
    </style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <?php if ($logoBase64): ?>
            <img src="<?= $logoBase64 ?>" style="height:44px; margin-bottom:6px; display:block;">
            <?php endif; ?>
            <div class="company-name"><?= e($companyName ?? '') ?></div>
            <div class="company-info">
                <?= e($companyAddress ?? '') ?><br>
                <?php if (!empty($companyPhone)): ?>Tel: <?= e($companyPhone) ?><?php endif; ?>
                <?php if (!empty($companyPhone) && !empty($companyEmail)): ?> &nbsp;|&nbsp; <?php endif; ?>
                <?php if (!empty($companyEmail)): ?><?= e($companyEmail) ?><?php endif; ?>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">CREDIT NOTE</div>
            <div class="doc-no"><?= e($cn['credit_note_no']) ?></div>
            <div class="doc-date"><?= $cn['created_at'] ? date('d M Y', strtotime($cn['created_at'])) : date('d M Y') ?></div>
            <span class="status-badge" style="background:<?= $sc['bg'] ?>; color:<?= $sc['text'] ?>;"><?= ucfirst($cn['status']) ?></span>
        </div>
    </div>

    <!-- Amount Box -->
    <div class="amount-box">
        <div class="amount-label">Credit Amount</div>
        <div class="amount-value">
            <span class="amount-currency"><?= e($cn['currency']) ?></span>
            <?= number_format((float)$cn['amount'], 2) ?>
        </div>
    </div>

    <!-- Info -->
    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Partner / Client</div>
            <div class="info-value"><?= e($cn['partner_name'] ?? '—') ?></div>
        </div>
        <div class="info-col" style="border-left: none; padding-left: 20px;">
            <div class="info-label">Invoice Reference</div>
            <div class="info-value"><?= e($cn['invoice_no'] ?? '—') ?></div>
            <?php if (!empty($cn['invoice_no'])): ?>
            <div class="info-sub">Linked invoice</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reason -->
    <?php if (!empty($cn['reason'])): ?>
    <div class="reason-box">
        <h4>Reason / Notes</h4>
        <p><?= e($cn['reason']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($stampBase64): ?>
    <img src="<?= $stampBase64 ?>" class="stamp">
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <?php if ($tursabBase64): ?>
        <img src="<?= $tursabBase64 ?>" style="height:18px; margin-bottom:4px;"><br>
        <?php endif; ?>
        <?= e($companyName ?? '') ?> · <?= e($companyAddress ?? '') ?> · Tel: <?= e($companyPhone ?? '') ?> · <?= e($companyEmail ?? '') ?><br>
        Generated on <?= date('d M Y H:i') ?>
    </div>
</div>
</body>
</html>
