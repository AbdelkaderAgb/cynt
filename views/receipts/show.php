<?php /** Receipt Detail View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('receipt') ?: 'Receipt' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($receipt['invoice_no']) ?></p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= url('receipts/pdf') ?>?id=<?= $receipt['id'] ?>" target="_blank" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
            <i class="fas fa-download mr-1"></i>Download PDF
        </a>
        <a href="<?= url('receipts/pdf') ?>?id=<?= $receipt['id'] ?>" target="_blank" onclick="window.open(this.href);setTimeout(()=>window.frames[window.frames.length-1]?.print(),500);return false;" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            <i class="fas fa-print mr-1"></i>Print
        </a>
        <?php if (!empty($receipt['partner_id'])): ?>
        <span class="px-3 py-2 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-lg text-sm font-semibold">
            <i class="fas fa-portal-enter mr-1"></i>On Portal
        </span>
        <?php else: ?>
        <button onclick="sendToPortal(<?= $receipt['id'] ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition" title="Send to partner portal">
            <i class="fas fa-share mr-1"></i>Send to Portal
        </button>
        <?php endif; ?>
        <a href="<?= url('receipts') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Payment Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4"><i class="fas fa-check-circle text-emerald-500 mr-2"></i>Payment Information</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Status</span>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-xs font-bold uppercase">Paid</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Company</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white"><?= e($receipt['company_name']) ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Payment Date</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white"><?= $receipt['payment_date'] ? date('d/m/Y', strtotime($receipt['payment_date'])) : '—' ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Payment Method</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white"><?= e(ucfirst(str_replace('_', ' ', $receipt['payment_method'] ?? 'N/A'))) ?></span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500">Invoice Reference</span>
                <a href="<?= url('invoices/show') ?>?id=<?= $receipt['id'] ?>" class="text-sm font-semibold text-blue-600 hover:underline"><?= e($receipt['invoice_no']) ?></a>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4"><i class="fas fa-coins text-amber-500 mr-2"></i>Financial Summary</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Invoice Amount</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white"><?= number_format($receipt['total_amount'] ?? 0, 2) ?> <?= e($receipt['currency']) ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Tax</span>
                <span class="text-sm text-gray-600 dark:text-gray-400"><?= number_format($receipt['tax_amount'] ?? 0, 2) ?> <?= e($receipt['currency']) ?></span>
            </div>
            <?php if (($receipt['discount'] ?? 0) > 0): ?>
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Discount</span>
                <span class="text-sm text-red-500">-<?= number_format($receipt['discount'], 2) ?> <?= e($receipt['currency']) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between items-center py-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl px-4 -mx-2">
                <span class="text-base font-bold text-emerald-700 dark:text-emerald-400">Amount Paid</span>
                <span class="text-xl font-bold text-emerald-700 dark:text-emerald-300"><?= number_format($receipt['paid_amount'] ?? 0, 2) ?> <?= e($receipt['currency']) ?></span>
            </div>
            <?php $balance = ($receipt['total_amount'] ?? 0) - ($receipt['paid_amount'] ?? 0); if ($balance > 0.01): ?>
            <div class="flex justify-between items-center py-2">
                <span class="text-sm text-gray-500">Balance Due</span>
                <span class="text-sm font-bold text-red-600"><?= number_format($balance, 2) ?> <?= e($receipt['currency']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($receipt['notes'])): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-3"><i class="fas fa-sticky-note text-yellow-500 mr-2"></i>Notes</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400"><?= nl2br(e($receipt['notes'])) ?></p>
</div>
<?php endif; ?>

<!-- Send to Portal Modal (JS) -->
<script>
function sendToPortal(id) {
    if (confirm('Send this receipt to the partner portal?')) {
        fetch('<?= url('receipts/send-to-portal') ?>?id=' + id, { method: 'GET' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Receipt sent to portal!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to send to portal');
                }
            });
    }
}
</script>
