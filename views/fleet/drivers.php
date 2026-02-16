<?php $statusColors = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-gray-100 text-gray-500','on_leave'=>'bg-amber-100 text-amber-700']; ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('driver_management') ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p></div>
    <a href="<?= url('drivers/form') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-xl font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-plus"></i> <?= __('new_driver') ?></a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('drivers') ?>" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('search') ?>..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-64 focus:ring-2 focus:ring-blue-500">
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm"><option value=""><?= __('all') ?></option><option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= __('active') ?></option><option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('inactive') ?></option></select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold"><i class="fas fa-search mr-1"></i><?= __('search') ?></button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('first_name') ?> / <?= __('last_name') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('phone') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('license_number') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('languages') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($drivers)): ?><tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-user-tie text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($drivers as $d): $sc = $statusColors[$d['status']] ?? 'bg-gray-100 text-gray-500'; ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-semibold text-gray-800"><?= e($d['first_name'] . ' ' . $d['last_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= e($d['phone'] ?? '') ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= e($d['license_no'] ?? '—') ?> <?php if (!empty($d['license_expiry'])): ?><span class="text-xs text-gray-400">(<?= date('m/Y', strtotime($d['license_expiry'])) ?>)</span><?php endif; ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= e($d['languages'] ?? '') ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= __($d['status']) ?: $d['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= url('drivers/form') ?>?id=<?= $d['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-block" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="<?= url('drivers/delete') ?>?id=<?= $d['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition inline-block" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
