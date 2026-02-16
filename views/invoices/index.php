<?php
$statusMap = ['draft' => __('draft'), 'sent' => __('sent'), 'paid' => __('paid'), 'overdue' => __('overdue'), 'cancelled' => __('cancelled'), 'partial' => __('pending')];
$statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'overdue' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-500', 'partial' => 'bg-amber-100 text-amber-700'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('invoice_list') ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p>
    </div>
    <a href="<?= url('invoices/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('create') ?>
    </a>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400 mb-1"><?= __('total_invoices') ?></p>
        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($summary['total'] ?? 0) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400 mb-1"><?= __('pending') ?></p>
        <p class="text-2xl font-bold text-amber-600"><?= number_format($summary['pending'] ?? 0) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400 mb-1"><?= __('paid') ?></p>
        <p class="text-2xl font-bold text-emerald-600"><?= number_format($summary['paid'] ?? 0) ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400 mb-1"><?= __('total_amount') ?></p>
        <p class="text-2xl font-bold text-blue-600">$<?= number_format($summary['total_amount'] ?? 0, 2) ?></p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('invoices') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?></label><input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('search') ?>..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?></option>
                <?php foreach ($statusMap as $k => $v): ?><option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
            </select>
        </div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('start_date') ?></label><input type="date" name="date_from" value="<?= e($filters['date_from']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('end_date') ?></label><input type="date" name="date_to" value="<?= e($filters['date_to']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i><?= __('search') ?></button>
            <a href="<?= url('invoices') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('invoice_no') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('company_name') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('date') ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('amount') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($invoices)): ?>
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-file-invoice text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($invoices as $inv): $sc = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600'; ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-emerald-600"><?= e($inv['invoice_no']) ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><?= e($inv['company_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= isset($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : date('d/m/Y', strtotime($inv['created_at'])) ?></td>
                    <td class="px-4 py-3 text-right font-semibold"><?= number_format($inv['total_amount'] ?? 0, 2) ?> <?= $inv['currency'] ?? 'USD' ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= $statusMap[$inv['status']] ?? $inv['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="<?= __('view') ?>"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('invoices/edit') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                            <?php if ($inv['status'] !== 'paid'): ?>
                            <a href="<?= url('invoices/mark-paid') ?>?id=<?= $inv['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="<?= __('paid') ?>"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            <a href="<?= url('invoices/delete') ?>?id=<?= $inv['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                            <?php if (empty($inv['partner_id'])): ?>
                            <button onclick="sendToPortal(<?= $inv['id'] ?>)" class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Send to Portal"><i class="fas fa-share-square"></i></button>
                            <?php else: ?>
                            <span class="p-1.5 text-purple-400 cursor-default" title="On Portal"><i class="fas fa-check-circle"></i></span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/30 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500"><?= number_format($total) ?> <?= __('entries') ?></p>
        <div class="flex gap-1"><?php for ($i = 1; $i <= min($pages, 10); $i++): ?>
            <a href="<?= url('invoices') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1 rounded-lg text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<script>
function sendToPortal(id) {
    if (confirm('Send this invoice to the partner portal?')) {
        fetch('<?= url('invoices/send-to-portal') ?>?id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.success) { alert('Invoice sent to portal!'); location.reload(); }
                else alert(d.message || 'Error sending to portal');
            });
    }
}
</script>
