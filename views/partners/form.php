<?php $p = $partner; ?>
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1></div>
<form method="POST" action="<?= url('partners/store') ?>" class="space-y-6" x-data="{sub:false}" @submit="sub=true">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-building text-purple-500 mr-2"></i><?= __('company_info') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('company_name') ?> *</label><input type="text" name="company_name" value="<?= e($p['company_name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('contact_person') ?></label><input type="text" name="contact_person" value="<?= e($p['contact_person'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('email') ?> *</label><input type="email" name="email" value="<?= e($p['email'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('phone') ?></label><input type="text" name="phone" value="<?= e($p['phone'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Mobile</label><input type="text" name="mobile" value="<?= e($p['mobile'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Website</label><input type="url" name="website" value="<?= e($p['website'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('tax_id') ?></label><input type="text" name="tax_id" value="<?= e($p['tax_id'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('partner_type') ?></label>
                <select name="partner_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['agency'=>__('agency'),'hotel'=>__('hotel'),'supplier'=>__('supplier'),'other'=>__('other')] as $k=>$v): ?><option value="<?= $k ?>" <?= ($p['partner_type'] ?? 'agency') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
            </div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['active'=>__('active'),'inactive'=>__('inactive'),'suspended'=>__('suspended')] as $k=>$v): ?><option value="<?= $k ?>" <?= ($p['status'] ?? 'active') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-map-marker-alt text-blue-500 mr-2"></i><?= __('address') ?> & Finance</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('address') ?></label><input type="text" name="address" value="<?= e($p['address'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('city') ?></label><input type="text" name="city" value="<?= e($p['city'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('country') ?></label><input type="text" name="country" value="<?= e($p['country'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Commission (%)</label><input type="number" name="commission_rate" value="<?= $p['commission_rate'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Credit Limit</label><input type="number" name="credit_limit" value="<?= $p['credit_limit'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Payment Terms (days)</label><input type="number" name="payment_terms" value="<?= $p['payment_terms'] ?? 30 ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
        </div>
        <div class="mt-4"><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('notes') ?></label><textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?= e($p['notes'] ?? '') ?></textarea></div>
    </div>
    <!-- Portal Access -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-key text-amber-500 mr-2"></i>Portal Access</h3>
        <p class="text-sm text-gray-500 mb-4">Set a password to allow this partner to log in to the portal. The email address will be used as the username.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Portal Password <?= $isEdit ? '<span class="text-xs text-gray-400">(leave blank to keep current)</span>' : '' ?></label>
                <input type="password" name="portal_password" minlength="6" placeholder="••••••••" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <?php if ($isEdit && !empty($p['password'])): ?>
                    <span class="text-sm text-emerald-600 font-medium"><i class="fas fa-check-circle mr-1"></i>Portal access active</span>
                <?php elseif ($isEdit): ?>
                    <span class="text-sm text-gray-400"><i class="fas fa-times-circle mr-1"></i>Portal password not set</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" :disabled="sub" :class="{'opacity-50 cursor-not-allowed':sub}" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><span x-text="sub ? 'Saving…' : '<?= $isEdit ? __('update') : __('save') ?>'"></span></button>
        <a href="<?= url('partners') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>
