<?php /** Email Settings View */ $c = $config; ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('saved_successfully') ?></div><?php endif; ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-cog text-gray-500 mr-2"></i><?= __('settings') ?></h1>
    <div class="flex gap-2 mt-3">
        <a href="<?= url('settings') ?>" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition"><?= __('general') ?></a>
        <a href="<?= url('settings/email') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold"><?= __('email') ?: 'Email' ?></a>
    </div>
</div>

<form method="POST" action="<?= url('settings/email') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-server text-blue-500 mr-2"></i>SMTP Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1">SMTP Host</label><input type="text" name="smtp_host" value="<?= e($c['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">SMTP Port</label><input type="number" name="smtp_port" value="<?= $c['smtp_port'] ?? 587 ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">SMTP Username</label><input type="text" name="smtp_username" value="<?= e($c['smtp_username'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">SMTP Password</label><input type="password" name="smtp_password" value="<?= e($c['smtp_password'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-envelope text-emerald-500 mr-2"></i>Sender Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Sender Email</label><input type="email" name="from_email" value="<?= e($c['from_email'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Sender Name</label><input type="text" name="from_name" value="<?= e($c['from_name'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
        </div>
        <div class="flex gap-6 mt-4">
            <label class="flex items-center gap-2"><input type="checkbox" name="enable_notifications" value="1" <?= !empty($c['enable_notifications']) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="text-sm text-gray-600">Notifications enabled</span></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="enable_reminders" value="1" <?= !empty($c['enable_reminders']) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span class="text-sm text-gray-600">Reminders enabled</span></label>
        </div>
    </div>
    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= __('save') ?></button>
</form>
