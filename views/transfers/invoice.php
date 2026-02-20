<?php
/**
 * Transfer Invoices Dashboard — with filters, stats and responsive layout
 */
$statusLabels = ['draft'=>'Draft','sent'=>'Sent','paid'=>'Paid','overdue'=>'Overdue','cancelled'=>'Cancelled'];
$statusColors = [
    'draft'     => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    'sent'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'paid'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'overdue'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
];
$s = $stats ?? [];
$hasActiveFilter = !empty($filters['search']) || !empty($filters['status'])
                || !empty($filters['date_from']) || !empty($filters['date_to'])
                || !empty($filters['currency']);
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle mr-1"></i> Invoice saved successfully.
</div>
<?php endif; ?>

<!-- ── Page header ── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <span class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
            </span>
            Transfer Invoices
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-11">
            <?= number_format($total ?? 0) ?> record<?= ($total ?? 0) !== 1 ? 's' : '' ?>
            <?php if ($hasActiveFilter): ?>
            <span class="ml-1 text-blue-500 font-medium">· filtered</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= url('transfer-invoice/create') ?>"
       class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> New Invoice
    </a>
</div>

<!-- ── Stats bar ── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
            <i class="fas fa-file-invoice text-indigo-500 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Total</p>
            <p class="text-xl font-bold text-gray-800 dark:text-white"><?= number_format((int)($s['total'] ?? 0)) ?></p>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
            <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Paid</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400"><?= number_format((int)($s['paid_count'] ?? 0)) ?></p>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
            <i class="fas fa-clock text-amber-500 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Outstanding</p>
            <p class="text-xl font-bold text-amber-600 dark:text-amber-400"><?= number_format((int)($s['outstanding'] ?? 0)) ?></p>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center shrink-0">
            <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Overdue</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400"><?= number_format((int)($s['overdue_count'] ?? 0)) ?></p>
        </div>
    </div>
</div>

<!-- ── Filter panel ── -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('transfer-invoice') ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                           placeholder="Invoice No., Company..."
                           class="w-full pl-8 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Status</label>
                <select name="status"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">All Statuses</option>
                    <?php foreach ($statusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">From Date</label>
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">To Date</label>
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>
        </div>

        <!-- Currency row + action buttons -->
        <div class="flex flex-col sm:flex-row sm:items-end gap-3 mt-3">
            <div class="sm:w-40">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Currency</label>
                <select name="currency"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">All Currencies</option>
                    <option value="EUR" <?= ($filters['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                    <option value="USD" <?= ($filters['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                    <option value="TRY" <?= ($filters['currency'] ?? '') === 'TRY' ? 'selected' : '' ?>>TRY</option>
                    <option value="GBP" <?= ($filters['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                    <i class="fas fa-search"></i> Filter
                </button>
                <?php if ($hasActiveFilter): ?>
                <a href="<?= url('transfer-invoice') ?>"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i> Clear
                </a>
                <?php endif; ?>
            </div>
            <!-- Active filters summary -->
            <?php if ($hasActiveFilter): ?>
            <div class="flex flex-wrap gap-1.5 sm:ml-auto">
                <?php if (!empty($filters['search'])): ?>
                <span class="inline-flex items-center gap-1 text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2.5 py-1 rounded-full font-medium">
                    <i class="fas fa-search text-[9px]"></i> <?= e($filters['search']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($filters['status'])): ?>
                <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 px-2.5 py-1 rounded-full font-medium">
                    <i class="fas fa-circle text-[7px]"></i> <?= $statusLabels[$filters['status']] ?? $filters['status'] ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                <span class="inline-flex items-center gap-1 text-xs bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 px-2.5 py-1 rounded-full font-medium">
                    <i class="fas fa-calendar text-[9px]"></i>
                    <?= !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '…' ?>
                    –
                    <?= !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '…' ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($filters['currency'])): ?>
                <span class="inline-flex items-center gap-1 text-xs bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2.5 py-1 rounded-full font-medium">
                    <i class="fas fa-coins text-[9px]"></i> <?= e($filters['currency']) ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($invoices)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
    <i class="fas fa-inbox text-4xl mb-3 block"></i>
    <p class="font-medium">No invoices found</p>
    <?php if ($hasActiveFilter): ?>
    <a href="<?= url('transfer-invoice') ?>" class="mt-3 inline-flex items-center gap-1 text-sm text-blue-500 hover:underline"><i class="fas fa-times"></i> Clear filters</a>
    <?php endif; ?>
</div>
<?php else: ?>

<!-- ── Desktop table (md+) ── -->
<div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Route</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($invoices as $inv):
                    $ml         = json_decode($inv['main_leg_json'] ?? '{}', true) ?: [];
                    $extraStops = array_values(array_filter(json_decode($inv['stops_json'] ?? '[]', true) ?: [], fn($s) => !empty($s['from']) || !empty($s['to'])));
                    $legCount   = 1 + count($extraStops);
                    $mlType     = $ml['type'] ?? 'one_way';
                    $balance    = (float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0);
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3">
                        <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="font-mono font-bold text-blue-600 dark:text-blue-400 hover:underline"><?= e($inv['invoice_no']) ?></a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                        <?= e($inv['company_name']) ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if (!empty($ml['from']) || !empty($ml['to'])): ?>
                        <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-300">
                            <span class="truncate max-w-[80px]"><?= e($ml['from'] ?? '—') ?></span>
                            <i class="fas fa-arrow-right text-gray-300 text-[8px] shrink-0"></i>
                            <span class="truncate max-w-[80px]"><?= e($ml['to'] ?? '—') ?></span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="inline-flex px-1.5 py-0.5 text-[9px] font-bold rounded-full <?= $mlType === 'round_trip' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= $mlType === 'round_trip' ? '⇆ RT' : '→ OW' ?>
                            </span>
                            <?php if ($legCount > 1): ?><span class="text-[10px] text-gray-400"><?= $legCount ?> legs</span><?php endif; ?>
                        </div>
                        <?php else: ?><span class="text-gray-400 text-xs">—</span><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                        <div><?= !empty($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : '—' ?></div>
                        <?php if (!empty($ml['date']) && $ml['date'] !== '1970-01-01'): ?>
                        <div class="text-xs text-gray-400 mt-0.5"><i class="fas fa-car text-[9px] mr-1"></i><?= date('d/m/Y', strtotime($ml['date'])) ?><?= !empty($ml['time']) ? ' '.$ml['time'] : '' ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="font-bold text-gray-800 dark:text-gray-200"><?= number_format((float)$inv['total_amount'], 2) ?> <span class="text-xs font-normal text-gray-400"><?= e($inv['currency']) ?></span></div>
                        <?php if ($balance > 0.01): ?>
                        <div class="text-xs text-red-500 mt-0.5">bal: <?= number_format($balance, 2) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$inv['status']] ?? $inv['status'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>" target="_blank" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('transfer-invoice/edit') ?>?id=<?= $inv['id'] ?>" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Mobile cards (< md) ── -->
<div class="md:hidden space-y-3">
    <?php foreach ($invoices as $inv):
        $ml         = json_decode($inv['main_leg_json'] ?? '{}', true) ?: [];
        $extraStops = array_values(array_filter(json_decode($inv['stops_json'] ?? '[]', true) ?: [], fn($s) => !empty($s['from']) || !empty($s['to'])));
        $legCount   = 1 + count($extraStops);
        $mlType     = $ml['type'] ?? 'one_way';
        $balance    = (float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0);
    ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Card header -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="font-mono font-bold text-blue-600 dark:text-blue-400 text-sm hover:underline"><?= e($inv['invoice_no']) ?></a>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $statusLabels[$inv['status']] ?? $inv['status'] ?>
                </span>
            </div>
        </div>
        <!-- Card body -->
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= e($inv['company_name']) ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><?= !empty($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : '—' ?></p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-gray-800 dark:text-gray-200"><?= number_format((float)$inv['total_amount'], 2) ?> <span class="text-xs text-gray-400"><?= e($inv['currency']) ?></span></p>
                    <?php if ($balance > 0.01): ?>
                    <p class="text-xs text-red-500">bal: <?= number_format($balance, 2) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($ml['from']) || !empty($ml['to'])): ?>
            <div class="flex items-center gap-2 text-xs bg-gray-50 dark:bg-gray-700/40 rounded-lg px-3 py-2">
                <span class="inline-flex px-1.5 py-0.5 text-[9px] font-bold rounded-full <?= $mlType === 'round_trip' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' ?> shrink-0">
                    <?= $mlType === 'round_trip' ? '⇆ RT' : '→ OW' ?>
                </span>
                <span class="truncate text-gray-600 dark:text-gray-300 flex items-center gap-1">
                    <span class="truncate max-w-[100px]"><?= e($ml['from'] ?? '—') ?></span>
                    <i class="fas fa-arrow-right text-gray-300 text-[8px] shrink-0"></i>
                    <span class="truncate max-w-[100px]"><?= e($ml['to'] ?? '—') ?></span>
                </span>
                <?php if ($legCount > 1): ?><span class="ml-auto text-gray-400 shrink-0"><?= $legCount ?> legs</span><?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- Actions -->
            <div class="flex gap-2 pt-1">
                <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 transition"><i class="fas fa-eye mr-1"></i>View</a>
                <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>" target="_blank" class="flex-1 py-2 text-center text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 transition"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
                <a href="<?= url('transfer-invoice/edit') ?>?id=<?= $inv['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 transition"><i class="fas fa-edit mr-1"></i>Edit</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<!-- ── Pagination ── -->
<?php if (($pages ?? 1) > 1):
    $q = http_build_query(array_filter(['search'=>$filters['search'],'status'=>$filters['status'],'date_from'=>$filters['date_from'],'date_to'=>$filters['date_to'],'currency'=>$filters['currency']]));
?>
<div class="mt-4 flex items-center justify-between">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Page <span class="font-semibold"><?= $page ?></span> of <span class="font-semibold"><?= $pages ?></span>
    </p>
    <div class="flex gap-1">
        <a href="?page=<?= max(1,$page-1) ?>&<?= $q ?>"
           class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition">&#8592;</a>
        <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
        <a href="?page=<?= $i ?>&<?= $q ?>"
           class="px-3 py-1.5 text-sm border rounded-lg transition <?= $i==$page ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        <a href="?page=<?= min($pages,$page+1) ?>&<?= $q ?>"
           class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition">&#8594;</a>
    </div>
</div>
<?php endif; ?>
