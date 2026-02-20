<?php
$typeMap    = ['agency'=>__('agency'),'hotel'=>__('hotel'),'supplier'=>__('supplier'),'other'=>__('other')];
$statusMap  = ['active'=>__('active'),'inactive'=>__('inactive'),'suspended'=>__('suspended'),'blacklisted'=>'Blacklisted'];
$statusColors = ['active'=>'bg-emerald-100 text-emerald-700','inactive'=>'bg-gray-100 text-gray-500','suspended'=>'bg-amber-100 text-amber-700','blacklisted'=>'bg-red-100 text-red-700'];
$creditBalances = $creditBalances ?? [];
$currencySymbols = ['EUR'=>'€','USD'=>'$','TRY'=>'₺','GBP'=>'£','DZD'=>'د.ج','AZN'=>'₼'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('partner_management') ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p></div>
    <a href="<?= url('partners/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-plus"></i> <?= __('new_partner') ?></a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('partners') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div><input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('search') ?>..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"></div>
        <div><select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><option value=""><?= __('all') ?></option><?php foreach($typeMap as $k=>$v): ?><option value="<?= $k ?>" <?= ($filters['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div><select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><option value=""><?= __('all') ?></option><?php foreach($statusMap as $k=>$v): ?><option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="flex gap-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700"><i class="fas fa-search mr-1"></i><?= __('search') ?></button><a href="<?= url('partners') ?>" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200"><i class="fas fa-undo"></i></a></div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('company_name') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('contact') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('type') ?></th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Credit Balance</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($partners)): ?>
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-handshake text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($partners as $p): $sc = $statusColors[$p['status']] ?? 'bg-gray-100 text-gray-500'; ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3">
                        <a href="<?= url('partners/show') ?>?id=<?= $p['id'] ?>" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline"><?= e($p['company_name']) ?></a>
                        <p class="text-xs text-gray-400"><?= e($p['contact_person'] ?? '') ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><p><?= e($p['email'] ?? '') ?></p><p class="text-xs"><?= e($p['phone'] ?? '') ?></p></td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full"><?= $typeMap[$p['partner_type']] ?? $p['partner_type'] ?></span></td>
                    <td class="px-4 py-3 text-right">
                        <?php $pcb = $creditBalances[(int)$p['id']] ?? []; ?>
                        <?php if (!empty($pcb)): ?>
                        <a href="<?= url('partners/show') ?>?id=<?= $p['id'] ?>#credits"
                           class="inline-flex flex-wrap justify-end gap-1" title="Click to view credit details">
                            <?php foreach ($pcb as $cur => $bal): ?>
                            <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-full hover:bg-amber-100 transition whitespace-nowrap">
                                <i class="fas fa-coins text-[8px] text-amber-500"></i>
                                <?= number_format($bal, 2) ?> <?= e($cur) ?>
                            </span>
                            <?php endforeach; ?>
                        </a>
                        <?php else: ?>
                        <span class="text-xs text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>"><?= $statusMap[$p['status']] ?? $p['status'] ?></span></td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= url('partners/show') ?>?id=<?= $p['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition inline-block" title="View Details"><i class="fas fa-eye"></i></a>
                        <a href="<?= url('partners/credits') ?>?id=<?= $p['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-block" title="Credits"><i class="fas fa-coins"></i></a>
                        <a href="<?= url('partners/edit') ?>?id=<?= $p['id'] ?>" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition inline-block" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                        <a href="<?= url('partners/delete') ?>?id=<?= $p['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition inline-block" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
