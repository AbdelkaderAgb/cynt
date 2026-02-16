<?php /** Receipts Index View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('receipts') ?: 'Receipts' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('paid_invoices') ?: 'paid invoices' ?></p>
    </div>
    <div class="flex items-center gap-4">
        <div class="bg-emerald-50 dark:bg-emerald-900/30 rounded-xl px-5 py-3 border border-emerald-200 dark:border-emerald-700">
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium"><?= __('total_received') ?: 'Total Received' ?></p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300"><?= number_format($summary['total_paid'] ?? 0, 2) ?></p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl px-5 py-3 border border-blue-200 dark:border-blue-700">
            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium"><?= __('total_receipts') ?: 'Total Receipts' ?></p>
            <p class="text-xl font-bold text-blue-700 dark:text-blue-300"><?= number_format($summary['total_count'] ?? 0) ?></p>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('receipts') ?>" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Invoice no, company..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Method</label>
            <select name="method" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">All</option>
                <option value="cash" <?= ($filters['method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="bank_transfer" <?= ($filters['method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                <option value="credit_card" <?= ($filters['method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                <option value="paypal" <?= ($filters['method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition"><i class="fas fa-search mr-1"></i>Search</button>
        <?php if (!empty($filters['search']) || !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['method'])): ?>
        <a href="<?= url('receipts') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($receipts)): ?>
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-receipt text-4xl mb-3 block"></i>No receipts found</td></tr>
                <?php else: foreach ($receipts as $r): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3">
                        <a href="<?= url('receipts/show') ?>?id=<?= $r['id'] ?>" class="font-mono font-semibold text-emerald-600 hover:underline"><?= e($r['invoice_no']) ?></a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><?= e($r['company_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= $r['payment_date'] ? date('d/m/Y', strtotime($r['payment_date'])) : '-' ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            <?php
                            $m = $r['payment_method'] ?? '';
                            echo match($m) {
                                'cash'           => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'bank_transfer'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'credit_card'    => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                'paypal'         => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                default          => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                            };
                            ?>">
                            <?= e(ucfirst(str_replace('_', ' ', $m ?: 'N/A'))) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-600"><?= number_format($r['paid_amount'], 2) ?> <?= e($r['currency']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= url('receipts/show') ?>?id=<?= $r['id'] ?>" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            <a href="<?= url('receipts/pdf') ?>?id=<?= $r['id'] ?>" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition" title="Download PDF">
                                <i class="fas fa-download text-sm"></i>
                            </a>
                            <a href="<?= url('receipts/edit') ?>?id=<?= $r['id'] ?>" class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            <a href="<?= url('receipts/revert') ?>?id=<?= $r['id'] ?>" onclick="return confirm('Revert this receipt? Invoice will be marked as unpaid.')" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Revert (mark unpaid)">
                                <i class="fas fa-undo text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        <div class="text-xs text-gray-500">Page <?= $page ?> of <?= $pages ?></div>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="<?= url('receipts') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&date_from=<?= urlencode($filters['date_from'] ?? '') ?>&date_to=<?= urlencode($filters['date_to'] ?? '') ?>&method=<?= urlencode($filters['method'] ?? '') ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 transition">Previous</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="<?= url('receipts') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&date_from=<?= urlencode($filters['date_from'] ?? '') ?>&date_to=<?= urlencode($filters['date_to'] ?? '') ?>&method=<?= urlencode($filters['method'] ?? '') ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 transition">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
