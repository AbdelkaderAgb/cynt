<?php $u = $user ?? []; ?>
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1></div>
<form method="POST" action="<?= url('users/store') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('first_name') ?> *</label><input type="text" name="first_name" value="<?= e($u['first_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('last_name') ?> *</label><input type="text" name="last_name" value="<?= e($u['last_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('email') ?> *</label><input type="email" name="email" value="<?= e($u['email'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= $isEdit ? __('password') . ' (change)' : __('password') . ' *' ?></label><input type="password" name="password" <?= !$isEdit ? 'required' : '' ?> class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('role') ?></label><select name="role" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['admin'=>'Admin','manager'=>'Manager','operator'=>'Operator','viewer'=>'Viewer'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($u['role'] ?? 'viewer') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label><select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><option value="active" <?= ($u['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= __('active') ?></option><option value="inactive" <?= ($u['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('inactive') ?></option></select></div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?></button>
        <a href="<?= url('users') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>
