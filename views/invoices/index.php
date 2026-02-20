<?php
$statusMap = [
    'draft'     => __('draft'),
    'sent'      => __('sent'),
    'paid'      => __('paid'),
    'overdue'   => __('overdue'),
    'cancelled' => __('cancelled'),
    'partial'   => 'Partial',
];
$statusColors = [
    'draft'     => 'bg-gray-100 text-gray-600',
    'sent'      => 'bg-blue-100 text-blue-700',
    'paid'      => 'bg-emerald-100 text-emerald-700',
    'overdue'   => 'bg-red-100 text-red-700',
    'cancelled' => 'bg-gray-100 text-gray-500',
    'partial'   => 'bg-amber-100 text-amber-700',
];
$collectionRate = (float)($summary['collection_rate'] ?? 0);
$byCurrency     = $summary['by_currency'] ?? [];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle"></i> <?= __('saved_successfully') ?>
</div>
<?php endif; ?>

<!-- ══ Header ══════════════════════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-indigo-500"></i> <?= __('invoice_list') ?>
        </h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= number_format($total) ?> <?= __('entries') ?></p>
    </div>
    <a href="<?= url('invoices/create') ?>"
       class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('create') ?>
    </a>
</div>

<!-- ══ Stats Row ════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">

    <!-- Total -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase">Total</p>
            <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                <i class="fas fa-file-invoice text-indigo-500 text-xs"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($summary['total'] ?? 0) ?></p>
        <p class="text-xs text-gray-400 mt-0.5">invoices</p>
    </div>

    <!-- Paid -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase">Paid</p>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-emerald-600"><?= number_format($summary['paid'] ?? 0) ?></p>
        <p class="text-xs text-gray-400 mt-0.5"><?= number_format($summary['partial'] ?? 0) ?> partial</p>
    </div>

    <!-- Pending -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase">Pending</p>
            <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                <i class="fas fa-clock text-amber-500 text-xs"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-amber-600"><?= number_format($summary['pending'] ?? 0) ?></p>
        <p class="text-xs text-gray-400 mt-0.5">awaiting payment</p>
    </div>

    <!-- Overdue -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase">Overdue</p>
            <span class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-red-600"><?= number_format($summary['overdue'] ?? 0) ?></p>
        <p class="text-xs text-gray-400 mt-0.5">past due date</p>
    </div>

    <!-- Collection Rate -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-400 uppercase">Collection</p>
            <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                <i class="fas fa-chart-pie text-blue-500 text-xs"></i>
            </span>
        </div>
        <p class="text-2xl font-bold <?= $collectionRate >= 80 ? 'text-emerald-600' : ($collectionRate >= 50 ? 'text-amber-600' : 'text-red-600') ?>">
            <?= $collectionRate ?>%
        </p>
        <div class="mt-1.5 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
            <div class="h-full rounded-full transition-all <?= $collectionRate >= 80 ? 'bg-emerald-500' : ($collectionRate >= 50 ? 'bg-amber-500' : 'bg-red-500') ?>"
                 style="width: <?= min(100, $collectionRate) ?>%"></div>
        </div>
    </div>
</div>

<!-- ══ Per-currency breakdown ═══════════════════════════════════════════ -->
<?php if (!empty($byCurrency)): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= min(4, count($byCurrency)) ?> gap-3 mb-6">
    <?php foreach ($byCurrency as $bc): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between gap-4 shadow-sm">
        <div>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= e($bc['currency'] ?? '—') ?></span>
            <p class="text-base font-bold text-gray-800 dark:text-white mt-0.5">
                <?= number_format((float)$bc['total_amount'], 2) ?>
                <span class="text-xs font-normal text-gray-400"><?= e($bc['currency']) ?></span>
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs text-emerald-600 font-semibold"><?= number_format((float)$bc['paid_amount'], 2) ?> paid</p>
            <?php if ((float)$bc['outstanding'] > 0): ?>
            <p class="text-xs text-red-500 font-semibold"><?= number_format((float)$bc['outstanding'], 2) ?> due</p>
            <?php else: ?>
            <p class="text-xs text-emerald-500">✓ cleared</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('invoices') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?></label><input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('search') ?>..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"></div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?></option>
                <?php foreach ($statusMap as $k => $v): ?><option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
            </select>
        </div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('start_date') ?></label><input type="date" name="date_from" value="<?= e($filters['date_from']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        <div><label class="block text-xs font-medium text-gray-500 mb-1"><?= __('end_date') ?></label><input type="date" name="date_to" value="<?= e($filters['date_to']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-search mr-1"></i><?= __('search') ?></button>
            <a href="<?= url('invoices') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- ══ Table ═══════════════════════════════════════════════════════════ -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('invoice_no') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('company_name') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('date') ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Due</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('amount') ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Balance Due</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                $typeColors = [
                    'general'  => 'bg-gray-100 text-gray-600',
                    'hotel'    => 'bg-teal-100 text-teal-700',
                    'tour'     => 'bg-purple-100 text-purple-700',
                    'transfer' => 'bg-blue-100 text-blue-700',
                ];
                $typeIcons = [
                    'general'  => 'fa-file-alt',
                    'hotel'    => 'fa-hotel',
                    'tour'     => 'fa-map-marked-alt',
                    'transfer' => 'fa-shuttle-van',
                ];
                ?>
                <?php if (empty($invoices)): ?>
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center">
                        <i class="fas fa-file-invoice text-5xl text-gray-200 dark:text-gray-700 mb-3 block"></i>
                        <p class="text-gray-400"><?= __('no_data_found') ?></p>
                    </td>
                </tr>
                <?php else: foreach ($invoices as $inv):
                    $sc       = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600';
                    $invType  = $inv['type'] ?? 'general';
                    $balDue   = round((float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0), 2);
                    $isPaid   = ($inv['status'] === 'paid') || $balDue <= 0;
                    $isOverdue= ($inv['status'] === 'overdue');
                    $dueDate  = $inv['due_date'] ?? '';
                    $duePast  = !empty($dueDate) && strtotime($dueDate) < time() && !$isPaid;
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition <?= $isOverdue ? 'bg-red-50/30 dark:bg-red-900/5' : '' ?>">
                    <td class="px-4 py-3">
                        <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>"
                           class="font-mono font-bold text-blue-600 hover:underline text-xs"><?= e($inv['invoice_no']) ?></a>
                        <?php if (!empty($inv['partner_id'])): ?>
                        <span class="ml-1 inline-flex items-center text-[9px] text-purple-400" title="On portal"><i class="fas fa-cloud-upload-alt"></i></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200 max-w-[160px] truncate">
                        <?= e($inv['company_name']) ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold <?= $typeColors[$invType] ?? $typeColors['general'] ?>">
                            <i class="fas <?= $typeIcons[$invType] ?? $typeIcons['general'] ?>"></i>
                            <?= ucfirst($invType) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        <?= !empty($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : '—' ?>
                    </td>
                    <td class="px-4 py-3 text-xs whitespace-nowrap <?= $duePast ? 'text-red-600 font-semibold' : 'text-gray-400' ?>">
                        <?= !empty($dueDate) ? date('d/m/Y', strtotime($dueDate)) : '—' ?>
                        <?php if ($duePast): ?><i class="fas fa-exclamation-circle ml-0.5 text-[9px]"></i><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                        <?= number_format((float)($inv['total_amount'] ?? 0), 2) ?>
                        <span class="text-xs font-normal text-gray-400"><?= e($inv['currency'] ?? 'USD') ?></span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <?php if ($isPaid): ?>
                        <span class="text-emerald-500 font-semibold text-xs"><i class="fas fa-check mr-0.5"></i> Paid</span>
                        <?php else: ?>
                        <span class="font-semibold text-red-600 text-xs"><?= number_format($balDue, 2) ?> <span class="font-normal text-gray-400"><?= e($inv['currency'] ?? '') ?></span></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $sc ?>">
                            <?= $statusMap[$inv['status']] ?? ucfirst($inv['status'] ?? '') ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                               title="<?= __('view') ?>"><i class="fas fa-eye text-xs"></i></a>
                            <a href="<?= url('invoices/edit') ?>?id=<?= $inv['id'] ?>"
                               class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                               title="<?= __('edit') ?>"><i class="fas fa-edit text-xs"></i></a>
                            <?php if (!$isPaid): ?>
                            <a href="<?= url('invoices/mark-paid') ?>?id=<?= $inv['id'] ?>"
                               onclick="return confirm('Mark as paid?')"
                               class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                               title="Mark Paid"><i class="fas fa-check text-xs"></i></a>
                            <?php endif; ?>
                            <a href="<?= url('invoices/delete') ?>?id=<?= $inv['id'] ?>"
                               onclick="return confirm('<?= __('confirm_delete') ?>')"
                               class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                               title="<?= __('delete') ?>"><i class="fas fa-trash text-xs"></i></a>
                            <?php if (empty($inv['partner_id'])): ?>
                            <button onclick="sendToPortal(<?= $inv['id'] ?>)"
                                    class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                    title="Send to Portal"><i class="fas fa-share-square text-xs"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/30 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500"><?= number_format($total) ?> <?= __('entries') ?></p>
        <div class="flex gap-1"><?php for ($i = 1; $i <= min($pages, 10); $i++): ?>
            <a href="<?= url('invoices') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1 rounded-lg text-sm <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' ?>"><?= $i ?></a>
        <?php endfor; ?></div>
    </div>
    <?php endif; ?>
</div>

<script>
function sendToPortal(id) {
    if (confirm('Send this invoice to the partner portal?')) {
        fetch('<?= url('invoices/send-to-portal') ?>?id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.success) { alert('Invoice sent to portal!'); location.reload(); }
                else alert(d.message || 'Error sending to portal');
            });
    }
}
</script>
