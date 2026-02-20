<?php
/**
 * Partner Detail View — fully merged with credit management
 * Variables: $partner, $currencyBalances, $invoices, $transactions, $txTotal, $txPage, $txPages, $flash
 */
$p = $partner;

// Build per-currency balance map from the ledger (source of truth)
// $currencyBalances is keyed by currency code, each has ['balance', ...]
$balanceMap = [];
foreach ($currencyBalances as $cur => $cb) {
    $balanceMap[strtoupper($cur)] = round((float)$cb['balance'], 4);
}
// Total across all currencies for the quick-stat card (display only)
$bal = array_sum($balanceMap);

$statusColors = [
    'active'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'inactive'    => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
    'suspended'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'blacklisted' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
];
$typeColors = [
    'agency'   => 'bg-blue-100 text-blue-700',
    'hotel'    => 'bg-purple-100 text-purple-700',
    'supplier' => 'bg-teal-100 text-teal-700',
    'other'    => 'bg-gray-100 text-gray-600',
];
$currencySymbols = ['EUR' => '€', 'USD' => '$', 'TRY' => '₺', 'GBP' => '£', 'DZD' => 'د.ج', 'AZN' => '₼'];
$invStatusColors = [
    'draft'    => 'bg-gray-100 text-gray-600',
    'sent'     => 'bg-blue-100 text-blue-700',
    'paid'     => 'bg-emerald-100 text-emerald-700',
    'partial'  => 'bg-cyan-100 text-cyan-700',
    'overdue'  => 'bg-red-100 text-red-700',
    'cancelled'=> 'bg-gray-100 text-gray-400',
    'pending'  => 'bg-amber-100 text-amber-700',
];
$txTypeMeta = [
    'recharge'   => ['label' => 'Recharge',   'bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'fa-plus-circle',          'sign' => '+'],
    'payment'    => ['label' => 'Payment',    'bg' => 'bg-blue-100 text-blue-700',       'icon' => 'fa-file-invoice-dollar',  'sign' => '-'],
    'refund'     => ['label' => 'Refund',     'bg' => 'bg-amber-100 text-amber-700',     'icon' => 'fa-undo',                 'sign' => '+'],
    'adjustment' => ['label' => 'Adjust',     'bg' => 'bg-gray-100 text-gray-600',       'icon' => 'fa-sliders-h',            'sign' => '±'],
];
$currencies = ['EUR' => 'EUR €', 'USD' => 'USD $', 'TRY' => 'TRY ₺', 'GBP' => 'GBP £', 'DZD' => 'DZD د.ج', 'AZN' => 'AZN ₼'];
?>

<!-- ══════════════════════════════════════════════════════
     Alpine root: handles both recharge and pay-invoice modals
     ══════════════════════════════════════════════════════ -->
<script>
function partnerShowData() {
    return {
        showRecharge: false,
        showPay:      false,
        payInvoiceId: 0,
        payInvoiceNo: '',
        payBalanceDue: 0,
        payCurrency:  'EUR',
        payAmount:    0,
        partnerBalance: 0,
        balanceMap: <?= json_encode($balanceMap, JSON_NUMERIC_CHECK) ?: '{}' ?>,

        get payInsufficient() {
            return parseFloat(this.payAmount) > this.partnerBalance + 0.0001;
        },
        get payOverpay() {
            return parseFloat(this.payAmount) > this.payBalanceDue + 0.001;
        },

        openPay(invId, invNo, due, cur) {
            this.payInvoiceId   = invId;
            this.payInvoiceNo   = invNo;
            this.payBalanceDue  = parseFloat(due) || 0;
            this.payCurrency    = cur;
            // Look up the per-currency balance — only same-currency credit can pay this invoice
            this.partnerBalance = parseFloat(this.balanceMap[cur]) || 0;
            this.payAmount      = Math.min(this.payBalanceDue, this.partnerBalance).toFixed(2);
            this.showPay        = true;
        }
    };
}
</script>
<div x-data="partnerShowData()">

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-black shrink-0 shadow-lg shadow-blue-500/20">
            <?= strtoupper(substr($p['company_name'], 0, 1)) ?>
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= e($p['company_name']) ?></h1>
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$p['status'] ?? 'active'] ?? '' ?>"><?= ucfirst($p['status'] ?? 'active') ?></span>
                <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $typeColors[$p['partner_type'] ?? 'other'] ?? '' ?>"><?= ucfirst($p['partner_type'] ?? 'agency') ?></span>
            </div>
            <p class="text-sm text-gray-400 mt-0.5">
                <?php if (!empty($p['contact_person'])): ?><?= e($p['contact_person']) ?> · <?php endif; ?>
                <?= e($p['email'] ?? '') ?>
            </p>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap shrink-0">
        <button @click="showRecharge = true"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
            <i class="fas fa-plus-circle"></i> Add Credit
        </button>
        <a href="<?= url('partners/statement') ?>?id=<?= $p['id'] ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-chart-line"></i> Statement
        </a>
        <a href="<?= url('partners/edit') ?>?id=<?= $p['id'] ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="<?= url('partners') ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- ── Flash message ───────────────────────────────────── -->
<?php if (!empty($flash)): ?>
<div class="mb-5 flex items-center gap-3 p-4 rounded-xl border text-sm font-medium
    <?= $flash['type'] === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-400'
        : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-400' ?>">
    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-lg shrink-0"></i>
    <span class="flex-1"><?= e($flash['message']) ?></span>
    <?php if (!empty($flash['receipt_url'])): ?>
    <a href="<?= htmlspecialchars($flash['receipt_url']) ?>" target="_blank"
       class="ml-2 flex-shrink-0 px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition flex items-center gap-1">
        <i class="fas fa-file-pdf"></i> Download Receipt
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Top row: Partner info + quick stats ─────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="fas fa-building"></i> Partner Information
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <?php
            $fields = [
                ['label' => 'Contact Person', 'value' => $p['contact_person'] ?? ''],
                ['label' => 'Email',          'value' => $p['email'] ?? '',          'link' => 'mailto:' . ($p['email'] ?? '')],
                ['label' => 'Phone',          'value' => $p['phone'] ?? ''],
                ['label' => 'Mobile',         'value' => $p['mobile'] ?? ''],
                ['label' => 'Address',        'value' => trim(($p['address'] ?? '') . ($p['city'] ? ', ' . $p['city'] : '') . ($p['country'] ? ' ' . $p['country'] : ''))],
                ['label' => 'Tax ID',         'value' => $p['tax_id'] ?? ''],
                ['label' => 'Website',        'value' => $p['website'] ?? '', 'link' => $p['website'] ?? ''],
                ['label' => 'Payment Terms',  'value' => ($p['payment_terms'] ?? 30) . ' days'],
            ];
            foreach ($fields as $f):
                if (empty(trim($f['value']))) continue;
            ?>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-0.5"><?= $f['label'] ?></p>
                <?php if (!empty($f['link']) && $f['link'] !== 'mailto:'): ?>
                <a href="<?= e($f['link']) ?>" class="text-sm font-medium text-blue-600 hover:underline"><?= e($f['value']) ?></a>
                <?php else: ?>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200"><?= e($f['value']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($p['notes'])): ?>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Notes</p>
            <p class="text-sm text-gray-600 dark:text-gray-300"><?= nl2br(e($p['notes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="flex flex-col gap-4">
        <?php
        $paidCount  = count(array_filter($invoices, fn($i) => ($i['status'] ?? '') === 'paid'));
        $totalOwed  = array_sum(array_map(fn($i) => max(0, (float)$i['total_amount'] - (float)($i['paid_amount'] ?? 0)), $invoices));
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0"><i class="fas fa-file-invoice-dollar text-emerald-500"></i></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Invoices</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= count($invoices) ?>
                    <span class="text-sm font-medium text-emerald-500 ml-1"><?= $paidCount ?> paid</span>
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center shrink-0"><i class="fas fa-balance-scale text-red-400"></i></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Outstanding</p>
                <p class="text-xl font-bold <?= $totalOwed > 0 ? 'text-red-600' : 'text-gray-800 dark:text-white' ?>"><?= number_format($totalOwed, 2) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center shrink-0"><i class="fas fa-coins text-amber-500"></i></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Credit Balance</p>
                <?php if (empty($balanceMap)): ?>
                <p class="text-2xl font-bold text-gray-400">0.00 <span class="text-base font-medium">—</span></p>
                <?php elseif (count($balanceMap) === 1): ?>
                <?php foreach ($balanceMap as $bc => $bv): ?>
                <p class="text-2xl font-bold <?= $bv > 0 ? 'text-amber-600' : 'text-gray-800 dark:text-white' ?>">
                    <?= number_format($bv, 2) ?><span class="text-base font-medium text-gray-400"> <?= e($bc) ?></span>
                </p>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="flex flex-wrap gap-1 mt-0.5">
                    <?php foreach ($balanceMap as $bc => $bv): if ($bv <= 0) continue; ?>
                    <span class="text-sm font-bold text-amber-600"><?= number_format($bv, 2) ?> <span class="text-xs font-medium text-gray-400"><?= e($bc) ?></span></span>
                    <?php endforeach; ?>
                    <?php if ($bal <= 0): ?><span class="text-xl font-bold text-gray-400">0.00</span><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     CREDITS SECTION
     ══════════════════════════════════════════════════════ -->
<div id="credits" class="scroll-mt-6">

<!-- Credit balance cards (per currency) -->
<?php if (!empty($currencyBalances)): ?>
<div class="mb-5">
    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
        <i class="fas fa-coins text-amber-400"></i> Credit Balances by Currency
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php foreach ($currencyBalances as $cur => $cb):
            $cbBal = (float)$cb['balance'];
            $sym   = $currencySymbols[$cur] ?? $cur;
            $isPos = $cbBal >= 0;
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border <?= $isPos ? 'border-amber-200 dark:border-amber-700' : 'border-red-200 dark:border-red-700' ?> shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl <?= $isPos ? 'bg-amber-50 dark:bg-amber-900/30' : 'bg-red-50 dark:bg-red-900/20' ?> flex items-center justify-center">
                    <span class="text-lg font-black <?= $isPos ? 'text-amber-500' : 'text-red-500' ?>"><?= $sym ?></span>
                </div>
                <span class="text-xs font-bold text-gray-400"><?= $cur ?></span>
            </div>
            <p class="text-xs text-gray-400 font-medium mb-0.5">Available</p>
            <p class="text-2xl font-black <?= $isPos ? 'text-gray-800 dark:text-white' : 'text-red-600' ?>"><?= $isPos ? '' : '-' ?><?= number_format(abs($cbBal), 2) ?></p>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-2 text-xs">
                <div><p class="text-gray-400">In</p><p class="font-semibold text-emerald-600">+<?= number_format((float)$cb['total_in'], 2) ?></p></div>
                <div><p class="text-gray-400">Out</p><p class="font-semibold text-blue-600">-<?= number_format((float)$cb['total_out'], 2) ?></p></div>
            </div>
            <p class="text-[11px] text-gray-400 mt-2"><?= (int)$cb['tx_count'] ?> tx</p>
        </div>
        <?php endforeach; ?>

        <!-- Add credit card -->
        <div class="bg-gray-50 dark:bg-gray-700/40 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-5 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition"
             @click="showRecharge = true">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                <i class="fas fa-plus text-emerald-500"></i>
            </div>
            <p class="text-xs font-semibold text-emerald-600">Add Credit</p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="mb-5 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="flex items-center gap-3 flex-1">
        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center shrink-0"><i class="fas fa-coins text-amber-500"></i></div>
        <div>
            <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm">No credit balance yet</p>
            <p class="text-amber-600 dark:text-amber-400 text-xs mt-0.5">Recharge this partner's account to enable credit payments on invoices.</p>
        </div>
    </div>
    <button @click="showRecharge = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition shrink-0">
        <i class="fas fa-plus"></i> Add Credit
    </button>
</div>
<?php endif; ?>

<!-- Credit transaction history -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-history text-indigo-400"></i> Credit Transaction History
            <span class="font-normal normal-case tracking-normal text-gray-400">(<?= number_format($txTotal) ?>)</span>
        </h2>
    </div>
    <?php if (empty($transactions)): ?>
    <div class="py-10 text-center">
        <i class="fas fa-receipt text-3xl text-gray-300 mb-2 block"></i>
        <p class="text-sm text-gray-400">No transactions yet. Use "Add Credit" to get started.</p>
    </div>
    <?php else: ?>
    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Description</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Reference</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Balance After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($transactions as $tx):
                    $meta     = $txTypeMeta[$tx['type']] ?? $txTypeMeta['adjustment'];
                    $isCredit = in_array($tx['type'], ['recharge', 'refund']);
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3 text-gray-400 font-mono text-xs"><?= $tx['id'] ?></td>
                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap text-xs">
                        <?= date('d/m/Y', strtotime($tx['created_at'])) ?>
                        <span class="block text-[11px] text-gray-400"><?= date('H:i', strtotime($tx['created_at'])) ?></span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $meta['bg'] ?>">
                            <i class="fas <?= $meta['icon'] ?> text-[9px]"></i> <?= $meta['label'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-200 max-w-[200px] truncate text-xs" title="<?= e($tx['description'] ?? '') ?>"><?= e($tx['description'] ?? '—') ?></td>
                    <td class="px-5 py-3">
                        <?php if ($tx['ref_type'] === 'invoice' && $tx['ref_id']): ?>
                        <a href="<?= url('invoices/show') ?>?id=<?= $tx['ref_id'] ?>" class="text-blue-600 hover:underline text-xs flex items-center gap-1">
                            <i class="fas fa-file-invoice text-[9px]"></i> <?= e($tx['invoice_no'] ?? '#'.$tx['ref_id']) ?>
                        </a>
                        <?php else: ?><span class="text-gray-400 text-xs">—</span><?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-right font-bold whitespace-nowrap <?= $isCredit ? 'text-emerald-600' : 'text-blue-600' ?>">
                        <?= $isCredit ? '+' : '-' ?><?= number_format((float)$tx['amount'], 2) ?>
                        <span class="font-normal text-gray-400 text-xs"><?= e($tx['currency']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap text-xs">
                        <?= number_format((float)$tx['balance_after'], 2) ?> <span class="text-gray-400 font-normal">EUR</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Mobile cards -->
    <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
        <?php foreach ($transactions as $tx):
            $meta     = $txTypeMeta[$tx['type']] ?? $txTypeMeta['adjustment'];
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
                <a href="<?= url('invoices/show') ?>?id=<?= $tx['ref_id'] ?>" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-file-invoice text-[9px]"></i> <?= e($tx['invoice_no'] ?? '#'.$tx['ref_id']) ?>
                </a>
                <?php else: ?><span class="text-xs text-gray-400">—</span><?php endif; ?>
                <span class="text-xs text-gray-500">Bal: <strong><?= number_format((float)$tx['balance_after'], 2) ?></strong></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Pagination -->
    <?php if ($txPages > 1): ?>
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
        <p class="text-xs text-gray-500">Page <?= $txPage ?> of <?= $txPages ?></p>
        <div class="flex gap-1.5">
            <?php if ($txPage > 1): ?>
            <a href="?id=<?= $p['id'] ?>&tx_page=<?= $txPage - 1 ?>#credits" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-chevron-left text-xs"></i></a>
            <?php endif; ?>
            <?php for ($i = max(1, $txPage - 2); $i <= min($txPages, $txPage + 2); $i++): ?>
            <a href="?id=<?= $p['id'] ?>&tx_page=<?= $i ?>#credits" class="px-3 py-1.5 rounded-lg text-sm font-medium transition <?= $i === $txPage ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 hover:bg-gray-200' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($txPage < $txPages): ?>
            <a href="?id=<?= $p['id'] ?>&tx_page=<?= $txPage + 1 ?>#credits" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-chevron-right text-xs"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

</div><!-- end #credits -->

<!-- ══════════════════════════════════════════════════════
     INVOICE HISTORY
     ══════════════════════════════════════════════════════ -->
<div id="invoices" class="scroll-mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-indigo-400"></i> Invoice History
            <span class="font-normal normal-case tracking-normal">(<?= count($invoices) ?>)</span>
        </h2>
        <a href="<?= url('invoices') ?>?search=<?= urlencode($p['company_name']) ?>" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
            View all <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>
    <?php if (empty($invoices)): ?>
    <div class="py-10 text-center text-sm text-gray-400">
        <i class="fas fa-file-invoice text-3xl mb-2 block opacity-30"></i>No invoices found.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Invoice No</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Date</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Due</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Amount</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Balance Due</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($invoices as $inv):
                    $due    = round((float)$inv['total_amount'] - (float)($inv['paid_amount'] ?? 0), 2);
                    $status = $inv['status'] ?? 'draft';
                    // Treat as paid if status=paid OR if balance due is zero/negative
                    $isPaid = $status === 'paid' || $due <= 0;
                    $ic     = $invStatusColors[$status] ?? 'bg-gray-100 text-gray-600';
                    $invCur = $inv['currency'] ?? 'EUR';
                    // Get available credit balance for this invoice currency
                    $curBalance = (float)($currencyBalances[$invCur]['balance'] ?? 0);
                    $canPay = !$isPaid && $due > 0 && $curBalance > 0;
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3">
                        <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>" class="font-semibold text-blue-600 hover:underline font-mono text-xs"><?= e($inv['invoice_no']) ?></a>
                        <?php if (($inv['type'] ?? '') === 'transfer'): ?>
                        <span class="ml-1 text-[10px] bg-teal-100 text-teal-600 px-1.5 py-0.5 rounded-full font-semibold">Transfer</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs"><?= !empty($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : '—' ?></td>
                    <td class="px-5 py-3 text-xs <?= !empty($inv['due_date']) && strtotime($inv['due_date']) < time() && !$isPaid ? 'text-red-500 font-semibold' : 'text-gray-500' ?>">
                        <?= !empty($inv['due_date']) ? date('d/m/Y', strtotime($inv['due_date'])) : '—' ?>
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-gray-800 dark:text-gray-200">
                        <?= number_format((float)$inv['total_amount'], 2) ?> <span class="text-gray-400 font-normal text-xs"><?= e($invCur) ?></span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold <?= $due > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
                        <?= $due > 0 ? number_format($due, 2) . ' <span class="text-xs font-normal text-gray-400">' . e($invCur) . '</span>' : '<i class="fas fa-check text-emerald-500 text-xs"></i>' ?>
                    </td>
                    <!-- Inline status changer -->
                    <td class="px-5 py-3 text-center"
                        x-data="{
                            st: '<?= e($status) ?>',
                            open: false,
                            saving: false,
                            sc: {
                                draft:    'bg-gray-100 text-gray-600',
                                sent:     'bg-blue-100 text-blue-700',
                                paid:     'bg-emerald-100 text-emerald-700',
                                partial:  'bg-cyan-100 text-cyan-700',
                                overdue:  'bg-red-100 text-red-700',
                                cancelled:'bg-gray-200 text-gray-500'
                            },
                            async set(val) {
                                if (val === this.st || this.saving) return;
                                this.saving = true; this.open = false;
                                const fd = new FormData();
                                fd.append('id', '<?= $inv['id'] ?>');
                                fd.append('status', val);
                                const r = await fetch('<?= url('invoices/update-status') ?>', {method:'POST', body:fd});
                                const d = await r.json();
                                if (d.success) this.st = val;
                                this.saving = false;
                            }
                        }"
                        @click.outside="open = false">
                        <div class="relative inline-block">
                            <button @click="open = !open"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full cursor-pointer hover:ring-2 hover:ring-offset-1 hover:ring-gray-300 transition disabled:opacity-60"
                                    :class="sc[st] || 'bg-gray-100 text-gray-600'">
                                <span x-text="st.charAt(0).toUpperCase() + st.slice(1)"></span>
                                <i x-show="!saving" class="fas fa-chevron-down text-[8px] opacity-60"></i>
                                <i x-show="saving" class="fas fa-spinner fa-spin text-[8px]"></i>
                            </button>
                            <!-- Dropdown -->
                            <div x-show="open" x-cloak
                                 class="absolute z-20 top-full mt-1 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-[130px]">
                                <?php foreach (['draft','sent','paid','partial','overdue','cancelled'] as $sv):
                                    $svc = ['draft'=>'text-gray-600','sent'=>'text-blue-600','paid'=>'text-emerald-600','partial'=>'text-cyan-600','overdue'=>'text-red-600','cancelled'=>'text-gray-400'][$sv];
                                    $svi = ['draft'=>'fa-pencil-alt','sent'=>'fa-paper-plane','paid'=>'fa-check-circle','partial'=>'fa-adjust','overdue'=>'fa-exclamation-circle','cancelled'=>'fa-ban'][$sv];
                                ?>
                                <button @click="set('<?= $sv ?>')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition <?= $svc ?>"
                                        :class="{ 'font-bold bg-gray-50 dark:bg-gray-700': st === '<?= $sv ?>' }">
                                    <i class="fas <?= $svi ?> w-3 text-center"></i>
                                    <?= ucfirst($sv) ?>
                                    <i x-show="st === '<?= $sv ?>'" class="fas fa-check ml-auto text-[9px]"></i>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="<?= url('invoices/show') ?>?id=<?= $inv['id'] ?>"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <?php if ($canPay): ?>
                            <button @click="openPay(<?= $inv['id'] ?>, '<?= e($inv['invoice_no']) ?>', <?= number_format($due, 4, '.', '') ?>, '<?= e($invCur) ?>')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500 text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition"
                                    title="Pay with Credit">
                                <i class="fas fa-coins text-[9px]"></i> Pay
                            </button>
                            <?php elseif (!$isPaid && $due > 0): ?>
                            <span class="text-[10px] text-gray-400 px-2">No credit in <?= e($invCur) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Recharge Credit
     ══════════════════════════════════════════════════════ -->
<div x-show="showRecharge" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showRecharge = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6" @click.outside="showRecharge = false">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-plus-circle text-emerald-500"></i> Add Credit
            </h2>
            <button @click="showRecharge = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form method="POST" action="<?= url('partners/credits/recharge') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="partner_id" value="<?= $p['id'] ?>">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Amount <span class="text-red-400">*</span></label>
                        <input type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Currency</label>
                        <select name="currency" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                            <?php foreach ($currencies as $code => $label): ?>
                            <option value="<?= $code ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                    <input type="text" name="description" maxlength="200" placeholder="e.g. Advance payment Feb 2026"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                </div>
                <button type="submit"
                        class="w-full py-3 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-plus-circle"></i> Add Credit to <?= e($p['company_name']) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Pay Invoice with Credit
     ══════════════════════════════════════════════════════ -->
<div x-show="showPay" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showPay = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6" @click.outside="showPay = false">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-coins text-amber-500"></i> Pay with Credit
            </h2>
            <button @click="showPay = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>

        <!-- Info strip -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-5 space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-amber-700 dark:text-amber-400 font-medium"><i class="fas fa-file-invoice mr-1"></i> Invoice</span>
                <span class="font-bold text-amber-800 dark:text-amber-300 font-mono" x-text="payInvoiceNo"></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-amber-700 dark:text-amber-400 font-medium"><i class="fas fa-wallet mr-1"></i> Credit Available</span>
                <span class="font-bold text-amber-800 dark:text-amber-300" x-text="partnerBalance.toFixed(2) + ' ' + payCurrency"></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-amber-700 dark:text-amber-400 font-medium"><i class="fas fa-balance-scale mr-1"></i> Balance Due</span>
                <span class="font-bold text-amber-800 dark:text-amber-300" x-text="payBalanceDue.toFixed(2) + ' ' + payCurrency"></span>
            </div>
        </div>

        <form method="POST" action="<?= url('partners/credits/pay-invoice') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="partner_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="invoice_id" :value="payInvoiceId">
            <input type="hidden" name="currency"   :value="payCurrency">

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Amount to Apply <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="number" name="amount" x-model="payAmount"
                           min="0.01" :max="payBalanceDue" step="0.01" required
                           class="w-full pl-4 pr-16 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-lg font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold" x-text="payCurrency"></span>
                </div>
                <p x-show="payInsufficient" x-cloak class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> Exceeds available credit balance.
                </p>
                <p x-show="payOverpay && !payInsufficient" x-cloak class="mt-1.5 text-xs text-amber-600 flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle"></i> Exceeds the invoice balance due.
                </p>
                <div class="flex gap-2 mt-2">
                    <button type="button" @click="payAmount = Math.min(payBalanceDue, partnerBalance).toFixed(2)"
                            class="text-xs px-3 py-1 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition font-semibold">Full amount</button>
                    <button type="button" @click="payAmount = (payBalanceDue / 2).toFixed(2)"
                            class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 transition font-semibold">50%</button>
                </div>
            </div>

            <button type="submit"
                    :disabled="payInsufficient || payOverpay || parseFloat(payAmount) <= 0"
                    class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Confirm Payment
            </button>
        </form>
    </div>
</div>

</div><!-- end Alpine root -->
