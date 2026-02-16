<?php
/**
 * Hotel Invoices View — Tailwind CSS
 */
$statusLabels = ['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'];
$statusColors = ['draft' => 'bg-gray-100 text-gray-700', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'overdue' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-600'];
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('hotel_invoice') ?: 'Hotel Invoices' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> records found</p>
    </div>
    <a href="<?= url('hotel-invoice/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> New Invoice
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Due Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($invoices)): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-file-invoice text-4xl mb-3 block"></i>No hotel invoices found</td></tr>
                <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-teal-600"><?= e($inv['invoice_no']) ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><?= e($inv['company_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></td>
                    <td class="px-4 py-3 text-right font-semibold"><?= number_format($inv['total_amount'], 2) ?> <?= e($inv['currency']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$inv['status']] ?? $inv['status'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('invoices/edit') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
