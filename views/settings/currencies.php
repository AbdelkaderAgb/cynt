<?php
/**
 * Currency Settings — list active currencies, add/remove, manage exchange rates
 */
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle"></i> Currency saved successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-trash-alt"></i> Currency removed.
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-circle"></i>
    <?php
    $errs = [
        'invalid_code'         => 'Currency code must be exactly 3 uppercase letters (e.g. USD).',
        'name_required'        => 'Currency name is required.',
        'cannot_delete_base'   => 'Cannot delete the base currency. Designate another currency as base first.',
        'not_found'            => 'Currency not found.',
        'missing'              => 'No currency specified.',
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
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Currencies</a>
        <a href="<?= url('settings/currencies/rates') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
            Exchange Rates
            <?php if ($rateCount > 0): ?>
            <span class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full"><?= $rateCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('settings/tax-rates') ?>"
           class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">Tax Rates</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- LEFT: Active Currencies List -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-coins text-yellow-500 mr-2"></i>Active Currencies
                </h2>
                <span class="text-xs text-gray-400"><?= count($currencies) ?> configured</span>
            </div>

            <?php if (empty($currencies)): ?>
            <div class="p-12 text-center text-gray-400">
                <i class="fas fa-coins text-4xl mb-3 block"></i>No currencies configured yet.
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-3 text-left">Code</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-center">Symbol</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Base</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($currencies as $c): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-gray-800 dark:text-gray-100 text-base"><?= e($c['code']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300"><?= e($c['name']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-lg font-semibold text-gray-700 dark:text-gray-200">
                                    <?= e($c['symbol']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($c['is_active']): ?>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">Active</span>
                                <?php else: ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($c['is_base']): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    <i class="fas fa-star text-xs mr-1"></i>Base
                                </span>
                                <?php else: ?>
                                <span class="text-gray-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if (!$c['is_base']): ?>
                                <a href="<?= url('settings/currencies/delete') ?>?code=<?= e($c['code']) ?>"
                                   class="text-red-400 hover:text-red-600 transition-colors p-1.5 rounded-lg hover:bg-red-50"
                                   title="Remove currency"
                                   onclick="return confirm('Remove <?= e($c['code']) ?> (<?= e($c['name']) ?>) from the system?')">
                                    <i class="fas fa-trash text-sm"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-gray-300 text-xs">Base</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Exchange Rate Quick View (read-only matrix) -->
        <?php
        try {
            $today = date('Y-m-d');
            $currentRates = Database::fetchAll(
                "SELECT r.*, c1.symbol as from_symbol, c2.symbol as to_symbol
                 FROM exchange_rates r
                 LEFT JOIN currencies c1 ON r.from_currency = c1.code
                 LEFT JOIN currencies c2 ON r.to_currency = c2.code
                 WHERE r.valid_from <= ? AND (r.valid_to IS NULL OR r.valid_to >= ?)
                 GROUP BY r.from_currency, r.to_currency
                 ORDER BY r.from_currency, r.to_currency",
                [$today, $today]
            );
        } catch (\Exception $e) {
            $currentRates = [];
        }
        ?>
        <?php if (!empty($currentRates)): ?>
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-exchange-alt text-blue-500 mr-2"></i>Current Effective Rates
                </h2>
                <a href="<?= url('settings/currencies/rates') ?>"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Manage Rates <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-6 py-3 text-left">Pair</th>
                            <th class="px-6 py-3 text-right">Market Rate</th>
                            <th class="px-6 py-3 text-right">Markup</th>
                            <th class="px-6 py-3 text-right">Effective Rate</th>
                            <th class="px-6 py-3 text-left">Valid From</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($currentRates as $r): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-3 font-mono font-bold text-gray-800 dark:text-gray-100">
                                <?= e($r['from_currency']) ?> → <?= e($r['to_currency']) ?>
                            </td>
                            <td class="px-6 py-3 text-right text-gray-600"><?= number_format((float)$r['market_rate'], 4) ?></td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-amber-600 font-medium">+<?= number_format((float)$r['markup_percent'], 2) ?>%</span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="font-semibold text-emerald-600"><?= number_format((float)$r['effective_rate'], 4) ?></span>
                            </td>
                            <td class="px-6 py-3 text-gray-500 text-xs"><?= e(format_date($r['valid_from'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT PANEL: Add Currency + Calculator -->
    <div class="space-y-6">

        <!-- Add Currency Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-plus-circle text-emerald-500 mr-2"></i>Add / Update Currency
                </h2>
            </div>
            <form method="POST" action="<?= url('settings/currencies/store') ?>" class="p-6 space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        ISO Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" maxlength="3" placeholder="USD"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-mono uppercase
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="text-xs text-gray-400 mt-1">3-letter ISO 4217 code (e.g. USD, EUR, TRY)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Currency Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" placeholder="US Dollar"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Symbol
                    </label>
                    <input type="text" name="symbol" placeholder="$"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3">
                    <input type="checkbox" name="is_base" id="is_base" value="1"
                           class="w-4 h-4 text-blue-600 rounded border-gray-300">
                    <label for="is_base" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                        Set as base (reporting) currency
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                            All amounts will be expressible in this currency.
                        </span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold
                               shadow text-sm hover:from-blue-700 hover:to-blue-800 transition-all">
                    <i class="fas fa-save mr-2"></i>Save Currency
                </button>
            </form>
        </div>

        <!-- Live Currency Calculator -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="currencyCalc()">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                    <i class="fas fa-calculator text-purple-500 mr-2"></i>Live Calculator
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Amount</label>
                    <input type="number" x-model="amount" @input="convert()" step="0.01" min="0" placeholder="100.00"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                        <select x-model="from" @change="convert()"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <?php foreach ($currencies as $c): ?>
                            <option value="<?= e($c['code']) ?>" <?= $c['is_base'] ? 'selected' : '' ?>><?= e($c['code']) ?> — <?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                        <select x-model="to" @change="convert()"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <?php foreach ($currencies as $i => $c): ?>
                            <option value="<?= e($c['code']) ?>" <?= $i === 1 ? 'selected' : '' ?>><?= e($c['code']) ?> — <?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Result display -->
                <div x-show="result !== null && !error" class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Converted amount (effective rate)</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" x-text="formatResult()"></p>
                    <p class="text-xs text-gray-400 mt-1">
                        Rate: <span class="font-mono" x-text="rate"></span>
                        <span class="ml-2 text-amber-500">Incl. markup</span>
                    </p>
                </div>
                <div x-show="error" class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 text-center text-red-600 text-sm" x-text="error"></div>
                <div x-show="loading" class="text-center text-gray-400 text-sm py-2">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Calculating…
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function currencyCalc() {
    return {
        amount: 100,
        from: '<?= e(array_values(array_filter($currencies, fn($c) => $c['is_base']))[0]['code'] ?? 'USD') ?>',
        to: '<?= e(count($currencies) > 1 ? $currencies[1]['code'] : 'EUR') ?>',
        result: null,
        rate: null,
        error: null,
        loading: false,
        timeout: null,

        convert() {
            clearTimeout(this.timeout);
            if (!this.amount || !this.from || !this.to) return;
            this.timeout = setTimeout(() => this.doConvert(), 300);
        },

        async doConvert() {
            this.loading = true;
            this.error = null;
            try {
                const url = `<?= url('api/currencies/convert') ?>?from=${this.from}&to=${this.to}&amount=${this.amount}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.error) {
                    this.error = data.error;
                    this.result = null;
                } else {
                    this.result = data.result;
                    this.rate = data.rate;
                }
            } catch (e) {
                this.error = 'Request failed.';
            } finally {
                this.loading = false;
            }
        },

        formatResult() {
            if (this.result === null) return '—';
            return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(this.result)
                + ' ' + this.to;
        }
    };
}
</script>
