<?php /** Users List View */ ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('user_management') ?></h1><p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('entries') ?></p></div>
    <a href="<?= url('users/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-user-plus"></i> <?= __('new_user') ?></a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('users') ?>" class="flex gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="<?= __('search') ?>..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-64 focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold"><i class="fas fa-search mr-1"></i><?= __('search') ?></button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('first_name') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('email') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('role') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('last_login') ?></th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($users)): ?><tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-users text-4xl mb-3 block"></i><?= __('no_data_found') ?></td></tr>
                <?php else: foreach ($users as $u):
                    $roleBg = ['admin'=>'bg-red-100 text-red-700','manager'=>'bg-blue-100 text-blue-700','operator'=>'bg-amber-100 text-amber-700','viewer'=>'bg-gray-100 text-gray-600'][$u['role']] ?? 'bg-gray-100 text-gray-600';
                    $statusBg = $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500';
                ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold"><?= strtoupper(mb_substr($u['first_name'],0,1) . mb_substr($u['last_name'],0,1)) ?></div>
                            <span class="font-semibold text-gray-800"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= e($u['email']) ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $roleBg ?>"><?= $u['role'] ?></span></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusBg ?>"><?= __($u['status']) ?: $u['status'] ?></span></td>
                    <td class="px-4 py-3 text-gray-400 text-xs"><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : __('never') ?></td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= url('users/edit') ?>?id=<?= $u['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-block" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
