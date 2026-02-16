<?php
/**
 * Partner Portal — Invoices List
 */
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice text-blue-500 mr-2"></i>My Invoices</h1>
        <p class="text-sm text-gray-500 mt-1"><?= $total ?> invoices found</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?= url('portal/invoices') ?>" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search invoices..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
        <select name="status" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            <option value="">All Status</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="sent" <?= $status === 'sent' ? 'selected' : '' ?>>Sent</option>
            <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Overdue</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
    </form>
</div>

<!-- Invoice Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($invoices)): ?>
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-file-invoice text-4xl mb-3"></i>
            <p>No invoices found</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 uppercase">
                        <th class="text-left px-5 py-3">Invoice #</th>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Due Date</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="text-center px-5 py-3">Status</th>
                        <th class="text-center px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <?php
                            $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'overdue' => 'bg-red-100 text-red-700'];
                            $sc = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600';
                        ?>
                        <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-5 py-4 font-semibold text-blue-600"><?= e($inv['invoice_no']) ?></td>
                            <td class="px-5 py-4 text-gray-500"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="px-5 py-4 text-gray-500"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></td>
                            <td class="px-5 py-4 text-right font-bold"><?= number_format($inv['total_amount'], 2) ?> <?= e($inv['currency']) ?></td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $sc ?>"><?= ucfirst($inv['status']) ?></span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="<?= url('portal/invoices/view?id=' . $inv['id']) ?>" class="text-blue-500 hover:text-blue-700 mr-2" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= url('invoices/pdf?id=' . $inv['id']) ?>" target="_blank" class="text-gray-400 hover:text-gray-600" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <div class="flex items-center justify-center gap-2 p-4 border-t border-gray-100 dark:border-gray-700">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="<?= url("portal/invoices?page=$i&search=" . urlencode($search) . "&status=" . urlencode($status)) ?>"
                       class="w-8 h-8 rounded-lg flex items-center justify-center text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
