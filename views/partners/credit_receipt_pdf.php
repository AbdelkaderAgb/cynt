<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }
  .page { padding: 32px 36px; max-width: 680px; margin: 0 auto; }

  /* Header */
  .header { display: table; width: 100%; margin-bottom: 28px; border-bottom: 3px solid #0d9488; padding-bottom: 16px; }
  .header-left { display: table-cell; vertical-align: top; }
  .header-right { display: table-cell; text-align: right; vertical-align: top; }
  .company-name { font-size: 20px; font-weight: bold; color: #0d9488; }
  .company-info { font-size: 10px; color: #666; margin-top: 4px; line-height: 1.5; }
  .doc-title { font-size: 26px; font-weight: bold; color: #0d9488; }
  .doc-no { font-size: 12px; color: #555; margin-top: 4px; }
  .doc-date { font-size: 11px; color: #888; margin-top: 2px; }

  /* Info grid */
  .info-row { display: table; width: 100%; margin-bottom: 20px; }
  .info-col { display: table-cell; width: 50%; vertical-align: top; padding: 12px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; }
  .info-col + .info-col { margin-left: 12px; }
  .info-label { font-size: 9px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
  .info-value { font-size: 12px; font-weight: bold; color: #1a1a2e; }
  .info-sub { font-size: 10px; color: #555; margin-top: 2px; }

  /* Amount box */
  .amount-box { background: #0d9488; color: #fff; border-radius: 6px; padding: 18px 24px; text-align: center; margin: 20px 0; }
  .amount-label { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; opacity: .8; }
  .amount-value { font-size: 32px; font-weight: bold; margin-top: 4px; }
  .amount-currency { font-size: 16px; opacity: .85; }

  /* Details table */
  .details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .details th { background: #f8fafc; font-size: 9px; font-weight: bold; text-transform: uppercase; color: #64748b; padding: 7px 10px; border: 1px solid #e2e8f0; letter-spacing: .3px; }
  .details td { padding: 8px 10px; border: 1px solid #e2e8f0; font-size: 11px; color: #374151; }
  .details tr:nth-child(even) td { background: #f8fafc; }

  /* Balance */
  .balance-row { display: table; width: 100%; border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 4px; padding: 10px 14px; margin-bottom: 24px; }
  .balance-cell { display: table-cell; text-align: center; }
  .balance-cell + .balance-cell { border-left: 1px solid #86efac; }
  .balance-key { font-size: 9px; color: #059669; text-transform: uppercase; font-weight: bold; }
  .balance-val { font-size: 14px; font-weight: bold; color: #065f46; margin-top: 2px; }

  /* Footer */
  .footer { border-top: 1px solid #e2e8f0; padding-top: 12px; font-size: 9px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <div class="company-name"><?= htmlspecialchars($companyName) ?></div>
      <div class="company-info">
        <?= htmlspecialchars($companyAddress) ?><br>
        <?= htmlspecialchars($companyPhone) ?> &nbsp;|&nbsp; <?= htmlspecialchars($companyEmail) ?>
        <?php if (!empty($companyWebsite)): ?><br><?= htmlspecialchars($companyWebsite) ?><?php endif; ?>
      </div>
    </div>
    <div class="header-right">
      <div class="doc-title">CREDIT RECEIPT</div>
      <div class="doc-no"><?= htmlspecialchars($receiptNo) ?></div>
      <div class="doc-date"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></div>
    </div>
  </div>

  <!-- Amount -->
  <div class="amount-box">
    <div class="amount-label">Credit Recharge Amount</div>
    <div class="amount-value">
      <?= number_format((float)$tx['amount'], 2) ?>
      <span class="amount-currency"><?= htmlspecialchars($tx['currency']) ?></span>
    </div>
  </div>

  <!-- Partner / Transaction Info -->
  <div class="info-row">
    <div class="info-col">
      <div class="info-label">Partner</div>
      <div class="info-value"><?= htmlspecialchars($partner['company_name'] ?? '—') ?></div>
      <?php if (!empty($partner['contact_person'])): ?>
      <div class="info-sub"><?= htmlspecialchars($partner['contact_person']) ?></div>
      <?php endif; ?>
      <?php if (!empty($partner['email'])): ?>
      <div class="info-sub"><?= htmlspecialchars($partner['email']) ?></div>
      <?php endif; ?>
    </div>
    <div class="info-col" style="margin-left:12px">
      <div class="info-label">Transaction</div>
      <div class="info-value"><?= htmlspecialchars($receiptNo) ?></div>
      <div class="info-sub">Type: Credit Recharge</div>
      <div class="info-sub">Date: <?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></div>
    </div>
  </div>

  <!-- Details -->
  <table class="details">
    <thead>
      <tr>
        <th>Description</th>
        <th style="text-align:right">Balance Before</th>
        <th style="text-align:right">Amount Added</th>
        <th style="text-align:right">Balance After</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= htmlspecialchars($tx['description'] ?: 'Manual credit recharge') ?></td>
        <td style="text-align:right"><?= number_format((float)$tx['balance_before'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></td>
        <td style="text-align:right; color:#059669; font-weight:bold">+ <?= number_format((float)$tx['amount'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></td>
        <td style="text-align:right; font-weight:bold"><?= number_format((float)$tx['balance_after'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></td>
      </tr>
    </tbody>
  </table>

  <!-- Balance summary -->
  <div class="balance-row">
    <div class="balance-cell">
      <div class="balance-key">Balance Before</div>
      <div class="balance-val"><?= number_format((float)$tx['balance_before'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></div>
    </div>
    <div class="balance-cell">
      <div class="balance-key">Recharged</div>
      <div class="balance-val">+ <?= number_format((float)$tx['amount'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></div>
    </div>
    <div class="balance-cell">
      <div class="balance-key">New Balance</div>
      <div class="balance-val"><?= number_format((float)$tx['balance_after'], 2) ?> <?= htmlspecialchars($tx['currency']) ?></div>
    </div>
  </div>

  <div class="footer">
    This is an auto-generated credit receipt. For questions, contact <?= htmlspecialchars($companyEmail) ?> &nbsp;|&nbsp; <?= htmlspecialchars($companyPhone) ?>
  </div>

</div>
</body>
</html>
