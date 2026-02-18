<?php
/**
 * Partner Portal — Profile
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-user-circle text-blue-500 mr-2"></i>My Profile</h1>
    <p class="text-sm text-gray-500 mt-1">Update your contact information</p>
</div>

<?php if (isset($_GET['updated'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> Profile updated successfully!
    </div>
<?php endif; ?>

<div class="max-w-2xl grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Company Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 text-center">
        <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
            <?= strtoupper(substr($partner['company_name'] ?? 'P', 0, 1)) ?>
        </div>
        <h3 class="font-bold text-gray-800 dark:text-white"><?= e($partner['company_name'] ?? '') ?></h3>
        <p class="text-sm text-gray-500"><?= e($partner['email'] ?? '') ?></p>
        <p class="text-xs text-gray-400 mt-1 capitalize"><?= e($partner['partner_type'] ?? 'agency') ?></p>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-400">Member since</p>
            <p class="text-sm font-medium"><?= date('M Y', strtotime($partner['created_at'] ?? 'now')) ?></p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="lg:col-span-2 space-y-4">
        <form method="POST" action="<?= url('portal/profile/update') ?>" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <?= csrf_field() ?>
            <h3 class="font-bold text-gray-800 dark:text-white mb-2">Contact Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" value="<?= e($partner['contact_person'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?= e($partner['phone'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
                    <input type="text" name="mobile" value="<?= e($partner['mobile'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                    <input type="text" name="city" value="<?= e($partner['city'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                    <input type="text" name="country" value="<?= e($partner['country'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <input type="text" name="address" value="<?= e($partner['address'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                </div>
            </div>

            <h3 class="font-bold text-gray-800 dark:text-white pt-4 border-t border-gray-100 dark:border-gray-700">Change Password</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                <input type="password" name="new_password" minlength="6" placeholder="••••••••"
                       class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>
