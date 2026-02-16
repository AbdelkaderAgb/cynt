<?php /** Portal Receipts List */ ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('my_receipts') ?: 'My Receipts' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total ?? 0) ?> records found</p>
    </div>
</div>

<!-- Search -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?= url('portal/receipts') ?>" class="flex gap-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by number..." class="flex-1 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Search</button>
    </form>
</div>

<!-- Receipts List -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Receipt #</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Method</th>
                    <th class="px-6 py-4 text-right">Amount</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($receipts)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-file-invoice-dollar text-4xl mb-3 block opacity-50"></i>
                        No receipts found.
                    </td>
                </tr>
                <?php else: foreach ($receipts as $r): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                        <?= htmlspecialchars($r['invoice_no']) ?>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                        <?= date('d M Y', strtotime($r['payment_date'])) ?>
                    </td>
                    <td class="px-6 py-4">
                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <?= ucfirst($r['payment_method'] ?? 'Paid') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white">
                        <?= number_format($r['paid_amount'], 2) ?> <?= $r['currency'] ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="<?= url('portal/receipts/view') ?>?id=<?= $r['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($pages ?? 0) > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <span class="text-xs text-gray-500">Page <?= $page ?? 1 ?> of <?= $pages ?? 1 ?></span>
        <div class="flex gap-2">
            <?php if (($page ?? 1) > 1): ?>
            <a href="?page=<?= ($page ?? 1) - 1 ?>&search=<?= urlencode($search ?? '') ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">Previous</a>
            <?php endif; ?>
            <?php if (($page ?? 1) < ($pages ?? 1)): ?>
            <a href="?page=<?= ($page ?? 1) + 1 ?>&search=<?= urlencode($search ?? '') ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>