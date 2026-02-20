<?php
/**
 * Partner Credits Page
 * Variables: $partner, $transactions, $total, $page, $pages, $flash
 */
$p          = $partner;
$balance    = (float)($p['balance'] ?? 0);
$creditLimit= (float)($p['credit_limit'] ?? 0);
$currencies = ['EUR' => 'EUR €', 'USD' => 'USD $', 'TRY' => 'TRY ₺', 'GBP' => 'GBP £'];

$typeMeta = [
    'recharge'   => ['label' => 'Recharge',   'bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'fa-plus-circle'],
    'payment'    => ['label' => 'Payment',    'bg' => 'bg-blue-100 text-blue-700',       'icon' => 'fa-file-invoice-dollar'],
    'refund'     => ['label' => 'Refund',     'bg' => 'bg-amber-100 text-amber-700',     'icon' => 'fa-undo'],
    'adjustment' => ['label' => 'Adjustment', 'bg' => 'bg-gray-100 text-gray-600',       'icon' => 'fa-sliders-h'],
];
?>

<!-- Page header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="<?= url('partners') ?>" class="hover:text-blue-500 transition">Partners</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            <a href="<?= url('partners/show') ?>?id=<?= $p['id'] ?>" class="hover:text-blue-500 transition"><?= e($p['company_name']) ?></a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            <span class="text-gray-600 dark:text-gray-300">Credits</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-coins text-amber-500"></i> Credit Account
        </h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= e($p['company_name']) ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('partners/show') ?>?id=<?= $p['id'] ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i> Back to Partner
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="mb-5 flex items-center gap-3 p-4 rounded-xl border text-sm font-medium
    <?= $flash['type'] === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-400'
        : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-400' ?>">
    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-lg flex-shrink-0"></i>
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Balance + stats row -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    <!-- Current balance (prominent) -->
    <div class="sm:col-span-2 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/20">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-amber-100 text-sm font-medium mb-1">Available Credit Balance</p>
                <p class="text-4xl font-black tracking-tight">
                    <?= number_format(abs($balance), 2) ?>
                    <span class="text-xl font-semibold opacity-80">EUR</span>
                </p>
                <?php if ($creditLimit > 0): ?>
                <p class="text-amber-200 text-xs mt-2">
                    Credit limit: <?= number_format($creditLimit, 2) ?> EUR
                    &nbsp;·&nbsp;
                    <?php $usedPct = $creditLimit > 0 ? min(100, ($balance / $creditLimit) * 100) : 0; ?>
                    <?= number_format($usedPct, 1) ?>% funded
                </p>
                <?php endif; ?>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">
                <i class="fas fa-coins text-2xl text-white/90"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-white/20 flex items-center gap-3 text-sm text-amber-100">
            <i class="fas fa-building text-white/70"></i>
            <span><?= e($p['company_name']) ?></span>
            <span class="ml-auto font-mono text-white/70 text-xs">ID #<?= $p['id'] ?></span>
        </div>
    </div>

    <!-- Stats column -->
    <div class="flex flex-col gap-4">
        <?php
        $totalRecharged = 0; $totalPaid = 0; $txCount = 0;
        // Aggregate from full ledger using a quick query
        $agg = Database::fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN type IN ('recharge','refund') THEN amount ELSE 0 END), 0) AS recharged,
                COALESCE(SUM(CASE WHEN type IN ('payment','adjustment') THEN amount ELSE 0 END), 0) AS paid,
                COUNT(*) AS cnt
             FROM credit_transactions WHERE partner_id = ?",
            [$p['id']]
        );
        $totalRecharged = (float)($agg['recharged'] ?? 0);
        $totalPaid      = (float)($agg['paid'] ?? 0);
        $txCount        = (int)($agg['cnt'] ?? 0);
        ?>
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                <i class="fas fa-arrow-circle-up text-emerald-500 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total Recharged</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white"><?= number_format($totalRecharged, 2) ?> <span class="text-xs text-gray-400">EUR</span></p>
            </div>
        </div>
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <i class="fas fa-arrow-circle-down text-blue-500 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total Consumed</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white"><?= number_format($totalPaid, 2) ?> <span class="text-xs text-gray-400">EUR</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Recharge form -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6"
     x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between text-left">
        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-plus-circle text-emerald-500"></i> Add Credit (Recharge)
        </h2>
        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="mt-5">
        <form method="POST" action="<?= url('partners/credits/recharge') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="partner_id" value="<?= $p['id'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        Amount <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="amount" min="0.01" step="0.01" required
                           placeholder="0.00"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Currency</label>
                    <select name="currency"
                            class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        <?php foreach ($currencies as $code => $label): ?>
                        <option value="<?= $code ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Description</label>
                    <input type="text" name="description" maxlength="200"
                           placeholder="e.g. Advance payment Jan 2026"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-500/20">
                    <i class="fas fa-plus-circle"></i> Add Credit
                </button>
                <p class="text-xs text-gray-400">This will increase <?= e($p['company_name']) ?>'s credit balance immediately.</p>
            </div>
        </form>
    </div>
</div>

<!-- Transaction history -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-history text-indigo-500"></i> Transaction History
            <span class="text-sm font-normal text-gray-400">(<?= number_format($total) ?>)</span>
        </h2>
    </div>

    <?php if (empty($transactions)): ?>
    <div class="py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-receipt text-gray-400 text-2xl"></i>
        </div>
        <p class="text-gray-500 text-sm">No transactions yet.</p>
        <p class="text-gray-400 text-xs mt-1">Add credit using the form above.</p>
    </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Balance After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($transactions as $tx):
                    $meta = $typeMeta[$tx['type']] ?? $typeMeta['adjustment'];
                    $isCredit = in_array($tx['type'], ['recharge', 'refund']);
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3 text-gray-400 font-mono text-xs"><?= $tx['id'] ?></td>
                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        <?= date('d/m/Y', strtotime($tx['created_at'])) ?>
                        <span class="block text-[11px] text-gray-400"><?= date('H:i', strtotime($tx['created_at'])) ?></span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $meta['bg'] ?>">
                            <i class="fas <?= $meta['icon'] ?> text-[9px]"></i>
                            <?= $meta['label'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-200 max-w-[220px] truncate" title="<?= e($tx['description'] ?? '') ?>">
                        <?= e($tx['description'] ?? '—') ?>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($tx['ref_type'] === 'invoice' && $tx['ref_id']): ?>
                        <a href="<?= url('invoices/show') ?>?id=<?= $tx['ref_id'] ?>"
                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-medium text-xs">
                            <i class="fas fa-file-invoice text-[9px]"></i>
                            <?= e($tx['invoice_no'] ?? '#' . $tx['ref_id']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-400 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-right font-bold whitespace-nowrap
                        <?= $isCredit ? 'text-emerald-600' : 'text-blue-600' ?>">
                        <?= $isCredit ? '+' : '-' ?><?= number_format((float)$tx['amount'], 2) ?>
                        <span class="font-normal text-gray-400 text-xs"><?= e($tx['currency']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                        <?= number_format((float)$tx['balance_after'], 2) ?>
                        <span class="font-normal text-gray-400 text-xs">EUR</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
        <?php foreach ($transactions as $tx):
            $meta = $typeMeta[$tx['type']] ?? $typeMeta['adjustment'];
            $isCredit = in_array($tx['type'], ['recharge', 'refund']);
        ?>
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold <?= $meta['bg'] ?> shrink-0">
                        <i class="fas <?= $meta['icon'] ?> text-[9px]"></i> <?= $meta['label'] ?>
                    </span>
                    <span class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></span>
                </div>
                <span class="font-bold text-sm shrink-0 <?= $isCredit ? 'text-emerald-600' : 'text-blue-600' ?>">
                    <?= $isCredit ? '+' : '-' ?><?= number_format((float)$tx['amount'], 2) ?> <?= e($tx['currency']) ?>
                </span>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-200 mt-2"><?= e($tx['description'] ?? '—') ?></p>
            <div class="flex items-center justify-between mt-2">
                <?php if ($tx['ref_type'] === 'invoice' && $tx['ref_id']): ?>
                <a href="<?= url('invoices/show') ?>?id=<?= $tx['ref_id'] ?>"
                   class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-file-invoice text-[9px]"></i>
                    <?= e($tx['invoice_no'] ?? '#' . $tx['ref_id']) ?>
                </a>
                <?php else: ?>
                <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
                <span class="text-xs text-gray-500">Balance: <strong class="text-gray-800 dark:text-gray-200"><?= number_format((float)$tx['balance_after'], 2) ?></strong></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Page <?= $page ?> of <?= $pages ?></p>
        <div class="flex gap-1.5">
            <?php if ($page > 1): ?>
            <a href="?id=<?= $p['id'] ?>&page=<?= $page - 1 ?>"
               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
            <a href="?id=<?= $p['id'] ?>&page=<?= $i ?>"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                      <?= $i === $page ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            <?php if ($page < $pages): ?>
            <a href="?id=<?= $p['id'] ?>&page=<?= $page + 1 ?>"
               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
