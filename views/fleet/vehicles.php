<?php $statusColors = ['available'=>'bg-emerald-100 text-emerald-700','in_use'=>'bg-blue-100 text-blue-700','maintenance'=>'bg-amber-100 text-amber-700','retired'=>'bg-gray-100 text-gray-500']; ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('vehicle_management') ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p></div>
    <a href="<?= url('vehicles/form') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 text-white rounded-xl font-semibold shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-plus"></i> <?= __('new_vehicle') ?></a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('plate_number') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('brand') ?> / <?= __('model') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('capacity') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('vehicle_type') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($vehicles)): ?><tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-car text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($vehicles as $vh): $sc = $statusColors[$vh['status']] ?? 'bg-gray-100 text-gray-500'; ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono font-bold text-blue-600"><?= e($vh['plate_number']) ?></td>
                    <td class="px-4 py-3 font-medium"><?= e(($vh['make'] ?? '') . ' ' . ($vh['model'] ?? '')) ?> <span class="text-gray-400 text-xs"><?= $vh['year'] ?? '' ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-700 text-xs font-bold"><?= $vh['capacity'] ?? '—' ?></span></td>
                    <td class="px-4 py-3 text-gray-500"><?= e($vh['vehicle_type'] ?? '') ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= __($vh['status']) ?: $vh['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= url('vehicles/form') ?>?id=<?= $vh['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-block" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="<?= url('vehicles/delete') ?>?id=<?= $vh['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition inline-block" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
