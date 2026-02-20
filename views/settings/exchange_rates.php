<?php
/**
 * Exchange Rates — full rate history, add new rates, live calculator
 */
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle"></i> Exchange rate saved.
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-trash-alt"></i> Rate deleted.
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-circle"></i>
    <?php
    $errs = [
        'invalid_pair'  => 'From and To currencies must be different valid codes.',
        'invalid_rate'  => 'Market rate must be a positive number.',
        'missing'       => 'No rate ID specified.',
    ];
    echo e($errs[$_GET['error']] ?? 'An error occurred.');
    ?>
</div>
<?php endif; ?>

<!-- Page Header & Tab Nav -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
        <i class="fas fa-cog text-gray-500 mr-2"></i>Settings
    </h1>
    <div class="flex flex-wrap gap-2 mt-3">
        <a href="<?= url('settings') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">General</a>
        <a href="<?= url('settings/email') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">Email</a>
        <a href="<?= url('settings/currencies') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">Currencies</a>
        <a href="<?= url('settings/currencies/rates') ?>"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Exchange Rates</a>
        <a href="<?= url('settings/tax-rates') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">Tax Rates</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- LEFT: Current rate matrix + history -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Current effective rate matrix -->
        <?php
        $today = date('Y-m-d');
        $activeCurrencies = array_filter($currencies, fn($c) => $c['is_active'] ?? 0);
        $codes = array_column($activeCurrencies, 'code');
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-table text-blue-500 mr-2"></i>Current Rate Matrix
                    <span class="text-xs font-normal text-gray-400 ml-2">as of <?= e(format_date($today)) ?></span>
                </h2>
            </div>
            <?php if (empty($codes) || empty($matrix)): ?>
            <div class="p-8 text-center text-gray-400">
                <i class="fas fa-exchange-alt text-3xl mb-3 block"></i>No rates configured yet. Add your first rate using the form on the right.
            </div>
            <?php else: ?>
            <div class="overflow-x-auto p-4">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 bg-gray-50 rounded-tl-lg">FROM \ TO</th>
                            <?php foreach ($codes as $toCode): ?>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 bg-gray-50 font-mono"><?= e($toCode) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codes as $fromCode): ?>
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-3 font-mono font-bold text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/30"><?= e($fromCode) ?></td>
                            <?php foreach ($codes as $toCode): ?>
                            <td class="px-4 py-3 text-center">
                                <?php if ($fromCode === $toCode): ?>
                                <span class="text-gray-300 font-bold">1.0000</span>
                                <?php else:
                                    $key = $fromCode . '_' . $toCode;
                                    $r = $matrix[$key] ?? null;
                                ?>
                                <?php if ($r): ?>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono">
                                    <?= number_format((float)$r['effective_rate'], 4) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-gray-300 text-xs">—</span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="text-xs text-gray-400 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Values shown are <strong>effective rates</strong> (market rate + company markup).
                    These are the rates applied to all financial conversions.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Full rate history table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-history text-gray-400 mr-2"></i>Rate History
                    <span class="text-xs font-normal text-gray-400 ml-2"><?= count($rates) ?> records</span>
                </h2>
            </div>
            <?php if (empty($rates)): ?>
            <div class="p-8 text-center text-gray-400">No rate history yet.</div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-3 text-left">Pair</th>
                            <th class="px-6 py-3 text-right">Market Rate</th>
                            <th class="px-6 py-3 text-right">Markup %</th>
                            <th class="px-6 py-3 text-right">Effective Rate</th>
                            <th class="px-6 py-3 text-center">Valid From</th>
                            <th class="px-6 py-3 text-center">Valid To</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Del</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($rates as $r):
                            $isActive = $r['valid_from'] <= $today && ($r['valid_to'] === null || $r['valid_to'] >= $today);
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors <?= $isActive ? '' : 'opacity-50' ?>">
                            <td class="px-6 py-3 font-mono font-bold text-gray-800 dark:text-gray-100 text-sm">
                                <?= e($r['from_currency']) ?> → <?= e($r['to_currency']) ?>
                            </td>
                            <td class="px-6 py-3 text-right text-gray-600 font-mono"><?= number_format((float)$r['market_rate'], 4) ?></td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-amber-600 font-medium">+<?= number_format((float)$r['markup_percent'], 2) ?>%</span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono">
                                    <?= number_format((float)$r['effective_rate'], 4) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center text-xs text-gray-500"><?= e(format_date($r['valid_from'])) ?></td>
                            <td class="px-6 py-3 text-center text-xs text-gray-500">
                                <?= $r['valid_to'] ? e(format_date($r['valid_to'])) : '<span class="text-emerald-500 font-medium">Current</span>' ?>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <?php if ($isActive): ?>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">Active</span>
                                <?php else: ?>
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">Historical</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= url('settings/currencies/rates/delete') ?>?id=<?= (int)$r['id'] ?>"
                                   class="text-red-400 hover:text-red-600 p-1.5 rounded hover:bg-red-50 transition-colors"
                                   title="Delete this rate"
                                   onclick="return confirm('Delete this exchange rate record?')">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: Add Rate Form -->
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="rateForm()">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-plus-circle text-emerald-500 mr-2"></i>Add Exchange Rate
                </h2>
            </div>
            <form method="POST" action="<?= url('settings/currencies/rates/store') ?>" class="p-6 space-y-4">
                <?= csrf_field() ?>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            From <span class="text-red-500">*</span>
                        </label>
                        <select name="from_currency" required @change="computeEffective()"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— From</option>
                            <?php foreach ($currencies as $c): ?>
                            <option value="<?= e($c['code']) ?>"><?= e($c['code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            To <span class="text-red-500">*</span>
                        </label>
                        <select name="to_currency" required @change="computeEffective()"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— To</option>
                            <?php foreach ($currencies as $c): ?>
                            <option value="<?= e($c['code']) ?>"><?= e($c['code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Market Rate <span class="text-red-500">*</span>
                        <span class="text-xs font-normal text-gray-400 ml-1">(interbank / raw)</span>
                    </label>
                    <input type="number" name="market_rate" x-model="marketRate" @input="computeEffective()"
                           step="0.0001" min="0.0001" placeholder="1.0000" required
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-mono
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Company Markup %
                        <span class="text-xs font-normal text-gray-400 ml-1">(0 = no markup)</span>
                    </label>
                    <input type="number" name="markup_percent" x-model="markupPct" @input="computeEffective()"
                           step="0.01" min="0" max="100" placeholder="1.50"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-mono
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Live effective rate preview -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 flex items-center justify-between">
                    <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">Effective Rate:</span>
                    <span class="font-mono font-bold text-blue-800 dark:text-blue-200 text-lg" x-text="effectiveRate"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Valid From <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_from" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Valid To
                            <span class="text-xs font-normal text-gray-400">(blank = open)</span>
                        </label>
                        <input type="date" name="valid_to"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Source, reason, etc."
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl font-semibold
                               shadow text-sm hover:from-emerald-700 hover:to-emerald-800 transition-all">
                    <i class="fas fa-save mr-2"></i>Save Exchange Rate
                </button>
            </form>
        </div>

        <!-- Explanation card -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>How rates work
            </h3>
            <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed">
                <strong>Market Rate</strong> is the raw interbank exchange rate.<br><br>
                <strong>Markup %</strong> is the company's margin on currency exchange.<br><br>
                <strong>Effective Rate</strong> = Market × (1 + Markup ÷ 100)<br><br>
                The effective rate is what gets applied to all customer invoices and conversions. Historical rates are preserved so old documents always recalculate correctly.
            </p>
        </div>
    </div>
</div>

<script>
function rateForm() {
    return {
        marketRate: '',
        markupPct: 1.5,
        effectiveRate: '—',

        computeEffective() {
            const m = parseFloat(this.marketRate);
            const p = parseFloat(this.markupPct) || 0;
            if (m > 0) {
                this.effectiveRate = (m * (1 + p / 100)).toFixed(6);
            } else {
                this.effectiveRate = '—';
            }
        }
    };
}
</script>
