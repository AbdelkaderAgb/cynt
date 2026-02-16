<?php /** Receipt Edit View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('edit') ?: 'Edit' ?> <?= __('receipt') ?: 'Receipt' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($receipt['invoice_no']) ?></p>
    </div>
    <a href="<?= url('receipts/show') ?>?id=<?= $receipt['id'] ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
        <i class="fas fa-arrow-left mr-1"></i>Back
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
    <form method="POST" action="<?= url('receipts/update') ?>">
        <input type="hidden" name="id" value="<?= $receipt['id'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment Date</label>
                <input type="date" name="payment_date" value="<?= e($receipt['payment_date'] ?? date('Y-m-d')) ?>"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                <select name="payment_method" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                    <option value="cash" <?= ($receipt['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank_transfer" <?= ($receipt['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="credit_card" <?= ($receipt['payment_method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                    <option value="paypal" <?= ($receipt['payment_method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                    <option value="other" <?= ($receipt['payment_method'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Amount Paid (<?= e($receipt['currency'] ?? 'USD') ?>)</label>
            <input type="number" step="0.01" name="paid_amount" value="<?= e($receipt['paid_amount'] ?? $receipt['total_amount'] ?? 0) ?>"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition" required>
            <p class="text-xs text-gray-500 mt-1">Invoice total: <?= number_format($receipt['total_amount'] ?? 0, 2) ?> <?= e($receipt['currency'] ?? 'USD') ?></p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Notes</label>
            <textarea name="notes" rows="3"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition resize-none"><?= e($receipt['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                <i class="fas fa-save mr-1"></i>Save Changes
            </button>
            <a href="<?= url('receipts/show') ?>?id=<?= $receipt['id'] ?>" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-300 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
