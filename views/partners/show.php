<?php
/**
 * Partner Detail View
 * 
 * Shows partner info + their voucher & invoice history.
 * Variables: $partner, $vouchers, $invoices
 */
$p = $partner;
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= e($p['company_name']) ?></h1>
        <p class="text-sm text-slate-500 mt-1"><?= __('partner_details') ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('partners/edit') ?>?id=<?= $p['id'] ?>" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
            <i class="fas fa-edit mr-1"></i> <?= __('edit') ?>
        </a>
        <a href="<?= url('partners') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-1"></i> <?= __('back') ?>
        </a>
    </div>
</div>

<!-- Partner Info Card -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('contact_person') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['contact_person'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('email') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['email'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('phone') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['phone'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('address') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['address'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('city') ?> / <?= __('country') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['city'] ?? '') ?> <?= e($p['country'] ?? '') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('partner_type') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= __(strtolower($p['partner_type'] ?? 'agency')) ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('tax_id') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= e($p['tax_id'] ?? '-') ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('credit_limit') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= number_format((float)($p['credit_limit'] ?? 0), 2) ?></p>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-400 uppercase"><?= __('commission_rate') ?></label>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200 mt-1"><?= number_format((float)($p['commission_rate'] ?? 0), 1) ?>%</p>
        </div>
    </div>
</div>

<!-- Voucher History -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-ticket-alt text-pink-500"></i> <?= __('voucher_history') ?>
            <span class="text-sm font-normal text-slate-400">(<?= count($vouchers) ?>)</span>
        </h3>
    </div>
    <?php if (empty($vouchers)): ?>
    <div class="text-center py-8 text-sm text-slate-500"><?= __('no_data_found') ?></div>
    <?php else: ?>
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[600px]">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-700/50 text-left">
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('voucher_no') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('date') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('route') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('amount') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('status') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <?php foreach ($vouchers as $v): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-6 py-3 font-medium text-blue-600">
                    <a href="<?= url('vouchers/show') ?>?id=<?= $v['id'] ?>"><?= e($v['voucher_no']) ?></a>
                </td>
                <td class="px-6 py-3 text-slate-600"><?= date('d/m/Y', strtotime($v['pickup_date'])) ?></td>
                <td class="px-6 py-3 text-slate-600"><?= e($v['pickup_location']) ?> → <?= e($v['dropoff_location']) ?></td>
                <td class="px-6 py-3 font-medium text-emerald-600"><?= format_currency($v['price'] ?? 0, $v['currency'] ?? 'USD') ?></td>
                <td class="px-6 py-3">
                    <?php $sc = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-700'][$v['status']] ?? 'bg-gray-100 text-gray-600'; ?>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $sc ?>"><?= __(strtolower($v['status'])) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Invoice History -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-indigo-500"></i> <?= __('invoice_history') ?>
            <span class="text-sm font-normal text-slate-400">(<?= count($invoices) ?>)</span>
        </h3>
    </div>
    <?php if (empty($invoices)): ?>
    <div class="text-center py-8 text-sm text-slate-500"><?= __('no_data_found') ?></div>
    <?php else: ?>
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[600px]">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-700/50 text-left">
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('invoice_no') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('date') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('total_amount') ?></th>
                <th class="px-6 py-3 text-xs font-semibold uppercase text-slate-500"><?= __('status') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <?php foreach ($invoices as $inv): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-6 py-3 font-medium text-blue-600">
                    <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>"><?= e($inv['invoice_no']) ?></a>
                </td>
                <td class="px-6 py-3 text-slate-600"><?= date('d/m/Y', strtotime($inv['invoice_date'])) ?></td>
                <td class="px-6 py-3 font-medium text-emerald-600"><?= format_currency($inv['total_amount'] ?? 0, $inv['currency'] ?? 'USD') ?></td>
                <td class="px-6 py-3">
                    <?php $ic = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700'][$inv['status']] ?? 'bg-gray-100 text-gray-600'; ?>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $ic ?>"><?= __(strtolower($inv['status'])) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
