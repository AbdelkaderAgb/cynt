<?php
/**
 * CYN Tourism — Credit Notes List
 */
$notes   = $notes ?? [];
$filters = $filters ?? [];
$summary = $summary ?? [];
?>

<?php if (!empty($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('saved_successfully') ?>', 'success'));</script>
<?php elseif (!empty($_GET['deleted'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('deleted_successfully') ?>', 'success'));</script>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Credit Notes</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= (int)($summary['total_count'] ?? 0) ?> notes · <?= number_format((float)($summary['total_amount'] ?? 0), 2) ?> total</p>
    </div>
    <a href="<?= url('credit-notes/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition font-medium">
        <i class="fas fa-plus"></i> New Credit Note
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
    <form class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="<?= __('search') ?>..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
        </div>
        <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <option value=""><?= __('all') ?> <?= __('status') ?></option>
            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= __('draft') ?></option>
            <option value="issued" <?= ($filters['status'] ?? '') === 'issued' ? 'selected' : '' ?>>Issued</option>
            <option value="applied" <?= ($filters['status'] ?? '') === 'applied' ? 'selected' : '' ?>>Applied</option>
        </select>
        <button type="submit" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
            <i class="fas fa-search mr-1"></i> <?= __('filter') ?>
        </button>
    </form>
</div>

<!-- Notes Table -->
<?php if (empty($notes)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
        <i class="fas fa-file-invoice text-2xl text-purple-500"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"><?= __('no_data_found') ?></h3>
    <p class="text-gray-500 dark:text-gray-400 mb-6">Issue credit notes to adjust invoices or provide refunds.</p>
</div>
<?php else: ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300">CN No</th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('partner') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('invoice_no') ?></th>
                    <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('amount') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('status') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('reason') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('date') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                <tr class="border-t border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-5 py-3 font-mono font-semibold text-purple-600 dark:text-purple-400"><?= htmlspecialchars($n['credit_note_no']) ?></td>
                    <td class="px-5 py-3 text-gray-900 dark:text-white"><?= htmlspecialchars($n['partner_name'] ?? '—') ?></td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($n['invoice_no'] ?? '—') ?></td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white"><?= number_format($n['amount'], 2) ?> <?= htmlspecialchars($n['currency']) ?></td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                            <?= $n['status'] === 'applied' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                             : ($n['status'] === 'issued' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' 
                             : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400') ?>">
                            <?= ucfirst($n['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300 max-w-[200px] truncate"><?= htmlspecialchars($n['reason']) ?></td>
                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400"><?= date('M d, Y', strtotime($n['created_at'])) ?></td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= url('credit-notes/create') ?>?id=<?= $n['id'] ?>" class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('credit-notes/delete') ?>?id=<?= $n['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
