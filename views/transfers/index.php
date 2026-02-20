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
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Voucher, Company, Route..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
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

<?php if (empty($vouchers)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
    <i class="fas fa-shuttle-van text-4xl mb-3 block"></i>No transfers found
</div>
<?php else: ?>

<!-- ── Desktop table (md+) ── -->
<div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Voucher No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company / Guest</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Route</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pax</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($vouchers as $v): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-blue-600">
                        <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="hover:underline"><?= e($v['voucher_no']) ?></a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-gray-200"><?= e($v['company_name']) ?></div>
                        <div class="text-xs text-gray-400 truncate max-w-[160px]"><?= e($v['passengers']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        <div class="flex items-center gap-1"><i class="fas fa-circle text-[6px] text-teal-500"></i> <?= e($v['pickup_location']) ?></div>
                        <div class="flex items-center gap-1 mt-0.5"><i class="fas fa-map-marker-alt text-[8px] text-rose-500"></i> <?= e($v['dropoff_location']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm">
                        <div class="font-medium"><?= date('d/m/Y', strtotime($v['pickup_date'])) ?></div>
                        <div class="text-xs text-gray-400"><?= e($v['pickup_time']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-700 text-xs font-bold"><?= $v['total_pax'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$v['status']] ?? $v['status'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('transfers/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('transfers/edit') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('transfers/delete') ?>?id=<?= $v['id'] ?>" onclick="return confirm('Delete transfer?')" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Mobile cards (< md) ── -->
<div class="md:hidden space-y-3">
    <?php foreach ($vouchers as $v): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Card header -->
        <div class="px-4 py-3 flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
            <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="font-mono font-bold text-blue-600 text-sm hover:underline"><?= e($v['voucher_no']) ?></a>
            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$v['status']] ?? $v['status'] ?></span>
        </div>
        <!-- Card body -->
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm"><?= e($v['company_name']) ?></p>
                    <?php if (!empty($v['passengers'])): ?>
                    <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]"><?= e($v['passengers']) ?></p>
                    <?php endif; ?>
                </div>
                <span class="flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-lg shrink-0">
                    <i class="fas fa-users text-[10px]"></i><?= $v['total_pax'] ?> pax
                </span>
            </div>
            <!-- Route -->
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/40 rounded-lg px-3 py-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1 truncate"><i class="fas fa-circle text-[6px] text-teal-500 shrink-0"></i><span class="truncate"><?= e($v['pickup_location']) ?></span></div>
                    <div class="flex items-center gap-1 truncate mt-0.5"><i class="fas fa-map-marker-alt text-[8px] text-rose-500 shrink-0"></i><span class="truncate"><?= e($v['dropoff_location']) ?></span></div>
                </div>
                <div class="text-right shrink-0">
                    <div class="font-semibold text-gray-700 dark:text-gray-200"><?= date('d/m/Y', strtotime($v['pickup_date'])) ?></div>
                    <div class="text-gray-400"><?= e($v['pickup_time']) ?></div>
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center gap-2 pt-1">
                <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 transition"><i class="fas fa-eye mr-1"></i>View</a>
                <a href="<?= url('transfers/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="flex-1 py-2 text-center text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 transition"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
                <a href="<?= url('transfers/edit') ?>?id=<?= $v['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 transition"><i class="fas fa-edit mr-1"></i>Edit</a>
                <a href="<?= url('transfers/delete') ?>?id=<?= $v['id'] ?>" onclick="return confirm('Delete transfer?')" class="py-2 px-3 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<!-- Pagination -->
<?php if (($pages ?? 1) > 1): ?>
<div class="mt-4 flex items-center justify-between">
    <p class="text-sm text-gray-500 dark:text-gray-400">Page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $pages ?></span></p>
    <div class="flex gap-1">
        <a href="?page=<?= max(1, $page-1) ?>&search=<?= e($filters['search'] ?? '') ?>&status=<?= e($filters['status'] ?? '') ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">&#8592;</a>
        <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= e($filters['search'] ?? '') ?>&status=<?= e($filters['status'] ?? '') ?>" class="px-3 py-1.5 text-sm border rounded-lg transition <?= $i == $page ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?page=<?= min($pages, $page+1) ?>&search=<?= e($filters['search'] ?? '') ?>&status=<?= e($filters['status'] ?? '') ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">&#8594;</a>
    </div>
</div>
<?php endif; ?>
