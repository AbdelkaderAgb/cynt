<?php /** Settings View */ ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-cog text-gray-500 mr-2"></i><?= __('settings') ?></h1>
    <div class="flex gap-2 mt-3">
        <a href="<?= url('settings') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold"><?= __('general') ?></a>
        <a href="<?= url('settings/email') ?>" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition"><?= __('email') ?: 'Email' ?></a>
    </div>
</div>

<form method="POST" action="<?= url('settings/update') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php foreach ($settings as $group => $items): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 capitalize"><i class="fas fa-folder text-blue-500 mr-2"></i><?= e($group) ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($items as $key => $value): ?>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= e(ucfirst(str_replace('_', ' ', $key))) ?></label>
                <?php if (strlen($value) > 100): ?>
                <textarea name="<?= e($key) ?>" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"><?= e($value) ?></textarea>
                <?php elseif ($value === '0' || $value === '1'): ?>
                <select name="<?= e($key) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
                    <option value="1" <?= $value === '1' ? 'selected' : '' ?>><?= __('active') ?></option>
                    <option value="0" <?= $value === '0' ? 'selected' : '' ?>><?= __('inactive') ?></option>
                </select>
                <?php else: ?>
                <input type="text" name="<?= e($key) ?>" value="<?= e($value) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($settings)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-cogs text-4xl mb-3 block"></i><?= __('no_data_found') ?>
    </div>
    <?php endif; ?>
    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= __('save') ?></button>
</form>
