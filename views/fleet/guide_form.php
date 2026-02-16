<?php $g = $guide; ?>
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1></div>
<form method="POST" action="<?= url('guides/store') ?>" class="space-y-6">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $g['id'] ?>"><?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('first_name') ?> *</label><input type="text" name="first_name" value="<?= e($g['first_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('last_name') ?> *</label><input type="text" name="last_name" value="<?= e($g['last_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('phone') ?> *</label><input type="text" name="phone" value="<?= e($g['phone'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('email') ?></label><input type="email" name="email" value="<?= e($g['email'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('languages') ?></label><input type="text" name="languages" value="<?= e($g['languages'] ?? '') ?>" placeholder="TR, EN, AR, DE" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('specialties') ?></label><input type="text" name="specializations" value="<?= e($g['specializations'] ?? '') ?>" placeholder="Historical tours, Nature tours" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Experience (years)</label><input type="number" name="experience_years" value="<?= $g['experience_years'] ?? 0 ?>" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Daily Rate</label><input type="number" name="daily_rate" value="<?= $g['daily_rate'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('currency') ?></label><select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['USD','EUR','TRY'] as $c): ?><option value="<?= $c ?>" <?= ($g['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label><select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><option value="active" <?= ($g['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= __('active') ?></option><option value="inactive" <?= ($g['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= __('inactive') ?></option></select></div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?></button>
        <a href="<?= url('guides') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>
