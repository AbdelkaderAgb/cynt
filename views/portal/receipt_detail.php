<?php /** Portal Receipt Detail */ ?>
<div class="mb-6 flex items-center justify-between">
    <a href="<?= url('portal/receipts') ?>" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to Receipts
    </a>
    <a href="<?= url('receipts/pdf') ?>?id=<?= $receipt['id'] ?>&download=1" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
        <i class="fas fa-download mr-1"></i> Download PDF
    </a>
</div>

<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Header -->
    <div class="bg-gray-50 dark:bg-gray-700/50 px-8 py-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Payment Receipt</h1>
            <p class="text-sm text-gray-500 mt-1">Ref: <?= htmlspecialchars($receipt['invoice_no']) ?></p>
        </div>
        <div class="text-right">
            <span class="inline-block px-4 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 rounded-full text-sm font-bold uppercase tracking-wide">
                Paid
            </span>
        </div>
    </div>

    <!-- Body -->
    <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">Date Paid</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?= date('d M Y', strtotime($receipt['payment_date'])) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">Method</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?= ucfirst($receipt['payment_method']) ?></span>
                    </div>
                     <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">Currency</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?= $receipt['currency'] ?></span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Service Details</h3>
                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Company</p>
                    <p class="font-bold text-gray-900 dark:text-white text-lg"><?= htmlspecialchars($receipt['company_name']) ?></p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Invoice Total</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?= number_format($receipt['total_amount'], 2) ?> <?= $receipt['currency'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-6 mb-8 border border-emerald-100 dark:border-emerald-800">
            <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-emerald-800 dark:text-emerald-300">Total Amount Paid</span>
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-400"><?= number_format($receipt['paid_amount'], 2) ?> <?= $receipt['currency'] ?></span>
            </div>
        </div>

        <?php if (!empty($receipt['notes'])): ?>
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                <?= nl2br(htmlspecialchars($receipt['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 dark:bg-gray-700/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700 text-center text-xs text-gray-500">
        Electronic Receipt generated on <?= date('d M Y H:i') ?>
    </div>
</div>
