<?php $d = $driver; ?>
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1></div>
<form method="POST" action="<?= url('drivers/store') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $d['id'] ?>"><?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('first_name') ?> *</label><input type="text" name="first_name" value="<?= e($d['first_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('last_name') ?> *</label><input type="text" name="last_name" value="<?= e($d['last_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('phone') ?> *</label><input type="text" name="phone" value="<?= e($d['phone'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Mobile</label><input type="text" name="mobile" value="<?= e($d['mobile'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('email') ?></label><input type="email" name="email" value="<?= e($d['email'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('license_number') ?></label><input type="text" name="license_no" value="<?= e($d['license_no'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('license_expiry') ?></label><input type="date" name="license_expiry" value="<?= $d['license_expiry'] ?? '' ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('languages') ?></label><input type="text" name="languages" value="<?= e($d['languages'] ?? '') ?>" placeholder="TR, EN, AR" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label><select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><option value="active" <?= ($d['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= __('active') ?></option><option value="inactive" <?= ($d['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('inactive') ?></option><option value="on_leave" <?= ($d['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>><?= __('off_duty') ?></option></select></div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?></button>
        <a href="<?= url('drivers') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>
