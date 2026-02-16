<?php
/**
 * Partner Portal — Dashboard
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
        Welcome, <?= e($partner['company_name'] ?? 'Partner') ?> 👋
    </h1>
    <p class="text-sm text-gray-500 mt-1">Here's your account overview</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Total Invoices -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $invoiceCount ?></p>
                <p class="text-xs text-gray-500">Total Invoices</p>
            </div>
        </div>
        <?php if ($pendingInvoices > 0): ?>
            <div class="mt-3 text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-lg inline-block">
                <i class="fas fa-clock mr-1"></i><?= $pendingInvoices ?> pending payment
            </div>
        <?php endif; ?>
    </div>

    <!-- Total Vouchers -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-receipt text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $voucherCount ?></p>
                <p class="text-xs text-gray-500">Total Vouchers</p>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-check text-purple-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pendingRequests ?></p>
                <p class="text-xs text-gray-500">Pending Requests</p>
            </div>
        </div>
    </div>

    <!-- Unread Messages -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center">
                <i class="fas fa-envelope text-rose-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $unreadMessages ?></p>
                <p class="text-xs text-gray-500">Unread Messages</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <a href="<?= url('portal/booking/create') ?>" class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus-circle text-xl"></i>
        <div>
            <p class="font-semibold text-sm">New Booking</p>
            <p class="text-[10px] text-blue-200">Request a transfer, hotel, or tour</p>
        </div>
    </a>
    <a href="<?= url('portal/invoices') ?>" class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-file-invoice text-blue-500 text-xl"></i>
        <div>
            <p class="font-semibold text-sm text-gray-800 dark:text-white">View Invoices</p>
            <p class="text-[10px] text-gray-500">Download & track payments</p>
        </div>
    </a>
    <a href="<?= url('portal/messages') ?>" class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-comments text-emerald-500 text-xl"></i>
        <div>
            <p class="font-semibold text-sm text-gray-800 dark:text-white">Contact Us</p>
            <p class="text-[10px] text-gray-500">Send a message to CYN Tourism</p>
        </div>
    </a>
</div>

<!-- Recent Invoices -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice text-blue-500 mr-2"></i>Recent Invoices</h3>
        <a href="<?= url('portal/invoices') ?>" class="text-sm text-blue-500 hover:underline">View All →</a>
    </div>

    <?php if (empty($recentInvoices)): ?>
        <p class="text-sm text-gray-400 py-4 text-center">No invoices yet</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left pb-2">Invoice</th>
                        <th class="text-left pb-2">Date</th>
                        <th class="text-right pb-2">Amount</th>
                        <th class="text-center pb-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentInvoices as $inv): ?>
                        <tr class="border-b border-gray-50 dark:border-gray-700/50">
                            <td class="py-3">
                                <a href="<?= url('portal/invoices/view?id=' . $inv['id']) ?>" class="text-blue-600 hover:underline font-medium">
                                    <?= e($inv['invoice_no']) ?>
                                </a>
                            </td>
                            <td class="py-3 text-gray-500"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="py-3 text-right font-bold"><?= number_format($inv['total_amount'], 2) ?> <?= e($inv['currency']) ?></td>
                            <td class="py-3 text-center">
                                <?php
                                    $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'paid' => 'bg-emerald-100 text-emerald-700', 'overdue' => 'bg-red-100 text-red-700'];
                                    $sc = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $sc ?>"><?= ucfirst($inv['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
