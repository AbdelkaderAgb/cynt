<?php
/**
 * Partner Portal — Invoice Detail
 */
$statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'overdue' => 'bg-red-100 text-red-700'];
$sc = $statusColors[$invoice['status']] ?? 'bg-gray-100 text-gray-600';
?>
<div class="mb-6 flex items-center gap-3">
    <a href="<?= url('portal/invoices') ?>" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:bg-gray-200">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Invoice <?= e($invoice['invoice_no']) ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></p>
    </div>
    <span class="ml-3 px-3 py-1 rounded-full text-xs font-semibold <?= $sc ?>"><?= ucfirst($invoice['status']) ?></span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Invoice Info -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs text-gray-400 uppercase mb-1">Company</p>
                <p class="font-semibold text-gray-800 dark:text-white"><?= e($invoice['company_name']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase mb-1">Invoice Date</p>
                <p class="font-semibold"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase mb-1">Due Date</p>
                <p class="font-semibold"><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase mb-1">Currency</p>
                <p class="font-semibold"><?= e($invoice['currency']) ?></p>
            </div>
        </div>

        <?php if (!empty($invoice['notes'])): ?>
            <div class="mb-6 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Notes</p>
                <p class="text-sm"><?= nl2br(e($invoice['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Items Table -->
        <?php if (!empty($items)): ?>
            <h3 class="font-bold mb-3 text-gray-800 dark:text-white">Invoice Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 uppercase">
                            <th class="text-left px-4 py-2">Description</th>
                            <th class="text-center px-4 py-2">Qty</th>
                            <th class="text-right px-4 py-2">Price</th>
                            <th class="text-right px-4 py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr class="border-b border-gray-50 dark:border-gray-700/50">
                                <td class="px-4 py-3"><?= e($item['description'] ?? '') ?></td>
                                <td class="px-4 py-3 text-center"><?= e($item['quantity'] ?? 1) ?></td>
                                <td class="px-4 py-3 text-right"><?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                                <td class="px-4 py-3 text-right font-bold"><?= number_format($item['total_price'] ?? $item['total'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <!-- Totals Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-semibold"><?= number_format($invoice['subtotal'], 2) ?></span>
                </div>
                <?php if ($invoice['tax_amount'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax (<?= $invoice['tax_rate'] ?>%)</span>
                        <span><?= number_format($invoice['tax_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($invoice['discount'] > 0): ?>
                    <div class="flex justify-between text-sm text-green-600">
                        <span>Discount</span>
                        <span>-<?= number_format($invoice['discount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="border-t pt-3 flex justify-between">
                    <span class="font-bold text-gray-800 dark:text-white">Total</span>
                    <span class="font-bold text-lg text-blue-600"><?= number_format($invoice['total_amount'], 2) ?> <?= e($invoice['currency']) ?></span>
                </div>
                <?php if ($invoice['paid_amount'] > 0): ?>
                    <div class="flex justify-between text-sm text-emerald-600">
                        <span>Paid</span>
                        <span><?= number_format($invoice['paid_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-red-600">
                        <span>Balance Due</span>
                        <span><?= number_format($invoice['total_amount'] - $invoice['paid_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <a href="<?= url('invoices/pdf?id=' . $invoice['id']) ?>" target="_blank"
               class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-50 text-red-600 rounded-xl font-medium text-sm hover:bg-red-100 transition">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>
</div>
