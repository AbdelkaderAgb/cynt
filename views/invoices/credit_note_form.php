<?php
/**
 * CYN Tourism — Credit Note Form
 */
$note      = $note ?? [];
$partners  = $partners ?? [];
$invoices  = $invoices ?? [];
$isEdit    = $isEdit ?? false;
$invoiceId = $invoiceId ?? 0;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $isEdit ? __('edit') : __('create') ?> Credit Note</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <form action="<?= url('credit-notes/store') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $note['id'] ?? '' ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('invoice_no') ?></label>
                <select name="invoice_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="">— None —</option>
                    <?php foreach ($invoices as $inv): ?>
                    <option value="<?= $inv['id'] ?>" <?= ($invoiceId == $inv['id'] || ($note['invoice_id'] ?? 0) == $inv['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($inv['invoice_no']) ?> — <?= htmlspecialchars($inv['company_name']) ?> (<?= number_format($inv['total_amount'], 2) ?> <?= $inv['currency'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('partner') ?></label>
                <select name="partner_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="">— None —</option>
                    <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($note['partner_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('amount') ?> *</label>
                <input type="number" name="amount" step="0.01" min="0" required value="<?= $note['amount'] ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('currency') ?></label>
                <select name="currency" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <?php foreach (['USD','EUR','TRY','GBP','SAR'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($note['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500">
                    <option value="draft" <?= ($note['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>><?= __('draft') ?></option>
                    <option value="issued" <?= ($note['status'] ?? '') === 'issued' ? 'selected' : '' ?>>Issued</option>
                    <option value="applied" <?= ($note['status'] ?? '') === 'applied' ? 'selected' : '' ?>>Applied</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('reason') ?></label>
                <textarea name="reason" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($note['reason'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="<?= url('credit-notes') ?>" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium"><?= __('cancel') ?></a>
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition font-medium">
                <i class="fas fa-save mr-1"></i> <?= __('save') ?>
            </button>
        </div>
    </form>
</div>
