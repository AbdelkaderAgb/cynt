<?php
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'];
$statusColors = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-cyan-100 text-cyan-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-700'];
?>
<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i>Tour saved</div>
<?php endif; ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('tour_voucher') ?: 'Tour Vouchers' ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> records</p></div>
    <a href="<?= url('tour-voucher/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> New Tour Voucher
    </a>
</div>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tour Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company / Customer</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pax</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($tours)): ?>
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-map-marked-alt text-4xl mb-3 block"></i>No tours found</td></tr>
                <?php else: foreach ($tours as $t): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                        <a href="<?= url('tour-voucher/show') ?>?id=<?= $t['id'] ?>" class="hover:underline"><?= e($t['tour_name']) ?></a>
                        <div class="text-xs text-gray-400"><?= e($t['tour_code'] ?? '') ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <div><?= e($t['company_name'] ?: '—') ?></div>
                        <?php 
                        $custs = json_decode($t['customers'] ?? '[]', true) ?: [];
                        $firstCust = $custs[0]['name'] ?? '';
                        if($firstCust) echo '<div class="text-xs text-gray-400">'.e($firstCust) . (count($custs)>1 ? ' +'.(count($custs)-1) : '').'</div>';
                        ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= $t['tour_date'] ? date('d/m/Y', strtotime($t['tour_date'])) : '—' ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-50 text-purple-700 text-xs font-bold"><?= $t['total_pax'] ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$t['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$t['status']] ?? $t['status'] ?></span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= url('tour-voucher/show') ?>?id=<?= $t['id'] ?>" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="<?= __('view') ?>"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('tour-voucher/pdf') ?>?id=<?= $t['id'] ?>" target="_blank" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('tour-voucher/pdf') ?>?id=<?= $t['id'] ?>&download=1" class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="<?= __('download') ?>"><i class="fas fa-download"></i></a>
                            <a href="<?= url('tour-voucher/edit') ?>?id=<?= $t['id'] ?>" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('tour-voucher/delete') ?>?id=<?= $t['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
