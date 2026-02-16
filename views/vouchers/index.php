<?php
/**
 * Vouchers List View — Tailwind CSS
 */
$statuses = ['pending' => __('pending'), 'confirmed' => __('confirmed'), 'completed' => __('completed'), 'cancelled' => __('cancelled')];
?>

<!-- Toast Messages -->
<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> <?= __('saved_successfully') ?>
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-trash"></i> <?= __('deleted_successfully') ?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('voucher_management', [], 'Voucher Management') ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p>
    </div>
    <a href="<?= url('vouchers/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('new_voucher', [], 'New Voucher') ?>
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('vouchers') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?></label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('search') ?>..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?></option>
                <?php foreach ($statuses as $k => $v): ?>
                <option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('start_date') ?></label>
            <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('end_date') ?></label>
            <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i><?= __('filter') ?></button>
            <a href="<?= url('vouchers') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('voucher_no') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('company_name') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('date') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('route') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('transfer_type') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('pax') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('status') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($vouchers)): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-inbox text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: ?>
                <?php foreach ($vouchers as $v): ?>
                <?php
                    $statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700', 'no_show' => 'bg-gray-100 text-gray-600'];
                    $sc = $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600';
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-blue-600"><?= e($v['voucher_no']) ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><?= e($v['company_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($v['pickup_date'])) ?> <span class="text-gray-400"><?= $v['pickup_time'] ?></span></td>
                    <td class="px-4 py-3 text-gray-600"><span class="text-gray-400"><?= e(mb_substr($v['pickup_location'] ?? '',0,20)) ?></span> → <?= e(mb_substr($v['dropoff_location'] ?? '',0,20)) ?></td>
                    <td class="px-4 py-3"><span class="text-xs"><?= e($v['transfer_type'] ?? '') ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-700 text-xs font-bold"><?= $v['total_pax'] ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= $statuses[$v['status']] ?? $v['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('vouchers/show') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="<?= __('view') ?>"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>&download=1" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="<?= __('download') ?>"><i class="fas fa-download"></i></a>
                            <a href="<?= url('vouchers/edit') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('vouchers/delete') ?>?id=<?= $v['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <p class="text-sm text-gray-500"><?= __('showing') ?> <?= (($page-1)*20+1) ?>-<?= min($page*20, $total) ?> <?= __('of') ?> <?= number_format($total) ?> <?= __('entries') ?></p>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= min($pages, 10); $i++): ?>
            <a href="<?= url('vouchers') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"
               class="px-3 py-1 rounded-lg text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 hover:bg-gray-100' ?> transition"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
