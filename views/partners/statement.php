<?php
/**
 * CYN Tourism — Partner Statement of Account
 * Per-currency sections with proper running balance per currency.
 */
$partner     = $partner     ?? [];
$byCurrency  = $byCurrency  ?? [];
$grandTotals = $grandTotals ?? [];
$dateFrom    = $dateFrom    ?? '';
$dateTo      = $dateTo      ?? '';

$currencySymbols = [
    'EUR' => '€', 'USD' => '$', 'TRY' => '₺', 'GBP' => '£',
    'DZD' => 'د.ج', 'AZN' => '₼',
];
$sym = fn(string $c) => $currencySymbols[strtoupper($c)] ?? $c;

$typeConfig = [
    'invoice'     => ['label' => 'Invoice',      'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',    'icon' => 'fa-file-invoice'],
    'payment'     => ['label' => 'Payment',      'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'icon' => 'fa-check-circle'],
    'credit_note' => ['label' => 'Credit Note',  'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300', 'icon' => 'fa-file-minus'],
    'recharge'    => ['label' => 'Credit Added', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',  'icon' => 'fa-coins'],
    'refund'      => ['label' => 'Refund',       'class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',     'icon' => 'fa-undo'],
    'payment_deduction' => ['label' => 'Credit Used', 'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300', 'icon' => 'fa-minus-circle'],
];
?>

<?php if (!empty($_GET['print'])): ?>
<script>window.addEventListener('DOMContentLoaded', () => setTimeout(() => window.print(), 400));</script>
<?php endif; ?>

<!-- ══ Header ═══════════════════════════════════════════════════════════ -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-file-alt text-indigo-500"></i> Statement of Account
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            <?= htmlspecialchars($partner['company_name'] ?? '') ?>
            &nbsp;·&nbsp;
            <?= date('d M Y', strtotime($dateFrom)) ?> – <?= date('d M Y', strtotime($dateTo)) ?>
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="<?= url('partners/show') ?>?id=<?= $partner['id'] ?? 0 ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-medium">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="<?= url('partners/statement/pdf') ?>?id=<?= $partner['id'] ?? 0 ?>&date_from=<?= htmlspecialchars($dateFrom) ?>&date_to=<?= htmlspecialchars($dateTo) ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition text-sm font-medium">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>
</div>

<!-- ══ Date Filter ══════════════════════════════════════════════════════ -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
    <form class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="id" value="<?= $partner['id'] ?? 0 ?>">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                   class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                   class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-semibold">
            <i class="fas fa-filter"></i> Filter
        </button>
    </form>
</div>

<!-- ══ Grand Summary (per currency) ════════════════════════════════════ -->
<?php if (!empty($grandTotals)): ?>
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
        Balance Summary
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($grandTotals as $cur => $gt): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($cur) ?></span>
                <span class="text-lg font-bold text-gray-300 dark:text-gray-600"><?= $sym($cur) ?></span>
            </div>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Invoiced</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                        <?= number_format($gt['total_invoiced'], 2) ?> <?= htmlspecialchars($cur) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Paid</span>
                    <span class="font-semibold text-emerald-600"><?= number_format($gt['total_paid'], 2) ?> <?= htmlspecialchars($cur) ?></span>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-1.5 flex justify-between">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Outstanding</span>
                    <span class="font-bold text-lg <?= $gt['outstanding'] > 0.005 ? 'text-red-600' : 'text-emerald-600' ?>">
                        <?= number_format(abs($gt['outstanding']), 2) ?> <?= htmlspecialchars($cur) ?>
                        <?php if ($gt['outstanding'] < -0.005): ?><span class="text-xs font-normal"> CR</span><?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══ Per-currency transaction tables ═════════════════════════════════ -->
<?php if (empty($byCurrency)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
    <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
    <p class="text-gray-400 text-sm">No transactions found for the selected period.</p>
</div>
<?php else: ?>
<?php foreach ($byCurrency as $cur => $bucket): ?>
<div class="mb-8">
    <!-- Currency section header -->
    <div class="flex items-center gap-3 mb-3">
        <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 font-bold text-sm"><?= $sym($cur) ?></span>
        <div>
            <h2 class="text-base font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($cur) ?> Transactions</h2>
            <p class="text-xs text-gray-400"><?= count($bucket['entries']) ?> entries &nbsp;·&nbsp;
                Outstanding: <span class="font-semibold <?= $bucket['outstanding'] > 0.005 ? 'text-red-500' : 'text-emerald-500' ?>">
                    <?= number_format(abs($bucket['outstanding']), 2) ?> <?= htmlspecialchars($cur) ?>
                    <?= $bucket['outstanding'] < -0.005 ? ' CR' : '' ?>
                </span>
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Debit <?= htmlspecialchars($cur) ?></th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Credit <?= htmlspecialchars($cur) ?></th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Balance <?= htmlspecialchars($cur) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <?php $running = 0.0; ?>
                    <?php foreach ($bucket['entries'] as $t):
                        $debit  = (float)($t['debit']  ?? 0);
                        $credit = (float)($t['credit'] ?? 0);
                        $running = round($running + $debit - $credit, 4);
                        $tc = $typeConfig[$t['type']] ?? ['label' => ucfirst($t['type']), 'class' => 'bg-gray-100 text-gray-600'];
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            <?= !empty($t['date']) ? date('d M Y', strtotime($t['date'])) : '—' ?>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $tc['class'] ?>">
                                <?= htmlspecialchars($tc['label']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                            <?php if (in_array($t['type'], ['invoice','payment'], true) && !empty($t['ref_id'])): ?>
                            <a href="<?= url('invoices/show') ?>?id=<?= (int)$t['ref_id'] ?>"
                               class="text-blue-600 hover:underline"><?= htmlspecialchars($t['reference']) ?></a>
                            <?php else: ?>
                            <?= htmlspecialchars($t['reference']) ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                            <?= htmlspecialchars($t['description'] ?? '') ?>
                        </td>
                        <td class="px-5 py-3 text-right <?= $debit > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-300 dark:text-gray-600' ?>">
                            <?= $debit > 0 ? number_format($debit, 2) : '—' ?>
                        </td>
                        <td class="px-5 py-3 text-right <?= $credit > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-300 dark:text-gray-600' ?>">
                            <?= $credit > 0 ? number_format($credit, 2) : '—' ?>
                        </td>
                        <td class="px-5 py-3 text-right font-bold whitespace-nowrap
                            <?= $running > 0.005 ? 'text-red-600 dark:text-red-400' : ($running < -0.005 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400') ?>">
                            <?= number_format(abs($running), 2) ?>
                            <?php if ($running < -0.005): ?><span class="text-xs font-normal ml-0.5">CR</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-700/30 border-t-2 border-gray-200 dark:border-gray-600">
                        <td colspan="4" class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Totals</td>
                        <td class="px-5 py-3 text-right font-bold text-red-600">
                            <?= number_format($bucket['total_invoiced'], 2) ?>
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">
                            <?= number_format($bucket['total_paid'] + $bucket['total_credits'], 2) ?>
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-lg
                            <?= $bucket['outstanding'] > 0.005 ? 'text-red-600' : 'text-emerald-600' ?>">
                            <?= number_format(abs($bucket['outstanding']), 2) ?>
                            <?= $bucket['outstanding'] < -0.005 ? '<span class="text-xs font-normal ml-0.5">CR</span>' : '' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
