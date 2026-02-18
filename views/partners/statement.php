<?php
/**
 * CYN Tourism — Partner Statement of Account
 * Shows invoices, receipts, credit notes, and running balance per partner.
 */
$partner     = $partner ?? [];
$transactions = $transactions ?? [];
$dateFrom    = $dateFrom ?? '';
$dateTo      = $dateTo ?? '';
$balance     = $balance ?? [];
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Statement of Account</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($partner['company_name'] ?? '') ?></p>
    </div>
    <div class="flex gap-3">
        <a href="<?= url('partners/show') ?>?id=<?= $partner['id'] ?? 0 ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left"></i> <?= __('go_back') ?>
        </a>
        <a href="<?= url('partners/statement/pdf') ?>?id=<?= $partner['id'] ?? 0 ?>&date_from=<?= htmlspecialchars($dateFrom) ?>&date_to=<?= htmlspecialchars($dateTo) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-xl hover:bg-red-200 dark:hover:bg-red-900/40 transition">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
    <form class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="id" value="<?= $partner['id'] ?? 0 ?>">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"><?= __('start_date') ?></label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"><?= __('end_date') ?></label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            <i class="fas fa-filter mr-1"></i> <?= __('filter') ?>
        </button>
    </form>
</div>

<!-- Balance Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Invoiced</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?= number_format((float)($balance['total_invoiced'] ?? 0), 2) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Paid</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?= number_format((float)($balance['total_paid'] ?? 0), 2) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Credit Notes</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1"><?= number_format((float)($balance['total_credits'] ?? 0), 2) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Outstanding Balance</p>
        <p class="text-2xl font-bold <?= ($balance['outstanding'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?> mt-1">
            <?= number_format((float)($balance['outstanding'] ?? 0), 2) ?>
        </p>
    </div>
</div>

<!-- Transactions Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('date') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Type</th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Reference</th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('description') ?></th>
                    <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Debit</th>
                    <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Credit</th>
                    <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php $runningBal = 0; ?>
                <?php if (empty($transactions)): ?>
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400"><?= __('no_data_found') ?></td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <?php 
                    $debit = $t['debit'] ?? 0;
                    $credit = $t['credit'] ?? 0;
                    $runningBal += $debit - $credit;
                ?>
                <tr class="border-t border-gray-100 dark:border-gray-700/50">
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= date('M d, Y', strtotime($t['date'])) ?></td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                            <?= $t['type'] === 'invoice' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' 
                             : ($t['type'] === 'payment' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                             : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400') ?>">
                            <?= ucfirst($t['type']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 font-mono text-gray-900 dark:text-white"><?= htmlspecialchars($t['reference']) ?></td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($t['description'] ?? '') ?></td>
                    <td class="px-5 py-3 text-right <?= $debit > 0 ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-400' ?>">
                        <?= $debit > 0 ? number_format($debit, 2) : '—' ?>
                    </td>
                    <td class="px-5 py-3 text-right <?= $credit > 0 ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-400' ?>">
                        <?= $credit > 0 ? number_format($credit, 2) : '—' ?>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold <?= $runningBal > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                        <?= number_format(abs($runningBal), 2) ?><?= $runningBal < 0 ? ' CR' : '' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
