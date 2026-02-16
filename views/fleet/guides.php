<?php $statusColors = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-gray-100 text-gray-500','on_leave'=>'bg-amber-100 text-amber-700']; ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('guide_management') ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p></div>
    <a href="<?= url('guides/form') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-xl font-semibold shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-plus"></i> <?= __('new_guide') ?></a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('first_name') ?> / <?= __('last_name') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('phone') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('languages') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('specialties') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Experience</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Daily Rate</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($guides)): ?><tr><td colspan="8" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-map-signs text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($guides as $g): $sc = $statusColors[$g['status']] ?? 'bg-gray-100 text-gray-500'; ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-semibold text-gray-800"><?= e($g['first_name'] . ' ' . $g['last_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= e($g['phone'] ?? '') ?></td>
                    <td class="px-4 py-3"><?php foreach(explode(',', $g['languages'] ?? '') as $lang): $lang = trim($lang); if ($lang): ?><span class="inline-flex px-2 py-0.5 text-xs bg-blue-50 text-blue-600 rounded-full mr-1"><?= e($lang) ?></span><?php endif; endforeach; ?></td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?= e(mb_substr($g['specializations'] ?? '', 0, 30)) ?></td>
                    <td class="px-4 py-3 text-center"><?= $g['experience_years'] ?? 0 ?> yrs</td>
                    <td class="px-4 py-3 text-right font-medium"><?= number_format($g['daily_rate'] ?? 0, 2) ?> <?= $g['currency'] ?? 'USD' ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= __($g['status']) ?: $g['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= url('guides/form') ?>?id=<?= $g['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-block" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="<?= url('guides/delete') ?>?id=<?= $g['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition inline-block" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
