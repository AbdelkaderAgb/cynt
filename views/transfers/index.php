<?php
/**
 * Transfer Vouchers List
 */
$statusLabels = ['pending'=>__('pending'),'confirmed'=>__('confirmed'),'completed'=>__('completed'),'cancelled'=>__('cancelled')];
$statusColors = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-700'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-trash mr-1"></i><?= __('deleted_successfully') ?></div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('transfer_vouchers') ?: 'Transfer Vouchers' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total ?? 0) ?> records found</p>
    </div>
    <a href="<?= url('transfers/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('new_transfer') ?>
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('transfers') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?></label>
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Voucher, Company, Hotel..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?></option>
                <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i><?= __('filter') ?></button>
            <a href="<?= url('transfers') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Voucher No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company / Guest</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Route</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pax</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($vouchers)): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-shuttle-van text-4xl mb-3 block"></i>No transfers found</td></tr>
                <?php else: ?>
                <?php foreach ($vouchers as $v): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-blue-600">
                        <a href="<?= url('transfer-voucher/show') ?>?id=<?= $v['id'] ?>" class="hover:underline"><?= e($v['voucher_no']) ?></a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                        <div><?= e($v['company_name']) ?></div>
                        <div class="text-xs text-gray-400"><?= e($v['passengers']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <div class="flex items-center gap-1"><i class="fas fa-circle text-[8px] text-teal-500"></i> <?= e($v['pickup_location']) ?></div>
                        <div class="flex items-center gap-1"><i class="fas fa-map-marker text-[10px] text-rose-500 ml-px"></i> <?= e($v['dropoff_location']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        <div><?= date('d/m/Y', strtotime($v['pickup_date'])) ?></div>
                        <div class="text-xs"><?= e($v['pickup_time']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-50 text-blue-700 text-xs font-bold"><?= $v['total_pax'] ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$v['status']] ?? $v['status'] ?></span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= url('transfer-voucher/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('transfer-voucher/edit') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('transfer-voucher/delete') ?>?id=<?= $v['id'] ?>" onclick="return confirm('Delete transfer?')" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if (($pages ?? 1) > 1): ?>
    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <a href="?page=<?= max(1, $page-1) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            <a href="?page=<?= min($pages, $page+1) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div><p class="text-sm text-gray-700 dark:text-gray-400">Page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $pages ?></span></p></div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php for ($i=1; $i<=$pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= e($filters['search']) ?>&status=<?= e($filters['status']) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i==$page ? 'text-blue-600 z-10 bg-blue-50 border-blue-500' : 'text-gray-500 hover:bg-gray-50' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>
