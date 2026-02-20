<?php
/**
 * Transfer Invoice — Create / Edit Form
 * Pricing is per-leg: each transfer card has its own price + catalog lookup.
 */
$currencies = ['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'];
$payMethods = [
    ''              => '— Select Payment Method —',
    'cash'          => 'Cash',
    'bank_transfer' => 'Bank Transfer',
    'wire_transfer' => 'Wire Transfer (SWIFT)',
    'credit_card'   => 'Credit Card',
    'debit_card'    => 'Debit Card',
    'check'         => 'Check / Cheque',
    'credit'        => 'Partner Credit',
    'crypto'        => 'Cryptocurrency',
    'online'        => 'Online Payment',
    'other'         => 'Other',
];

$inv     = $invoice ?? [];
$isEdit  = !empty($isEdit) && !empty($inv['id']);
$ml      = $mainLeg      ?? [];  // main leg data
$eStops  = $invoiceStops ?? [];  // extra stops array
$eGuests = $invoiceGuests ?? []; // guests array

// JS-safe JSON for Alpine initialization
$jsMainPrice  = (float)($ml['price']    ?? 0);
$jsCurrency   = htmlspecialchars($inv['currency']  ?? 'USD');
$jsTaxRate    = (float)($inv['tax_rate'] ?? 0);
$jsDiscount   = (float)($inv['discount'] ?? 0);
$jsStops      = json_encode($eStops,  JSON_UNESCAPED_UNICODE);
$jsGuests     = !empty($eGuests) ? json_encode($eGuests, JSON_UNESCAPED_UNICODE) : json_encode([['name'=>'','passport'=>'']]);

$formAction   = $isEdit ? url('transfer-invoice/update')  : url('transfer-invoice/store');
$pageHeading  = $isEdit ? 'Edit Transfer Invoice'         : 'New Transfer Invoice';
$pageSubtitle = $isEdit ? 'Update pricing and route details below' : 'Price per transfer leg — add stops to bill each route separately';
$submitLabel  = $isEdit ? 'Save Changes'                  : 'Generate Invoice';
$submitIcon   = $isEdit ? 'fa-save'                       : 'fa-file-invoice-dollar';

// Field helpers — PHP pre-population for edit
function inv_val(string $key, array $inv, mixed $default = ''): string {
    return htmlspecialchars((string)($inv[$key] ?? $default));
}
function ml_val(string $key, array $ml, mixed $default = ''): string {
    return htmlspecialchars((string)($ml[$key] ?? $default));
}
function sel(string $opt, string $current): string {
    return $opt === $current ? ' selected' : '';
}
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-file-invoice-dollar text-blue-500 mr-2"></i><?= $pageHeading ?>
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= $pageSubtitle ?></p>
    </div>
    <a href="<?= url('transfer-invoice') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<form method="POST" action="<?= $formAction ?>"
      x-data="transferInvoiceForm()">
        <?= csrf_field() ?>
    <?php if ($isEdit): ?>
    <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="company_id"  id="ti_company_id"  value="<?= inv_val('company_id', $inv) ?>">
    <input type="hidden" name="stops_json"  :value="JSON.stringify(stops)">
    <input type="hidden" name="main_price"  :value="mainPrice">
    <input type="hidden" name="total_price" :value="grandSubtotal">

    <div class="space-y-5">

        <!-- ══════════════ 1 · BILLING PARTY ══════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Billing Party</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="relative" x-data="invoicePartnerSearch('ti_company_id','ti_company_phone')" @click.outside="open=false">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Company / Agency *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"><i class="fas fa-building text-xs"></i></span>
                        <input type="text" name="company_name" x-model="query"
                               @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                               required autocomplete="off"
                               value="<?= inv_val('company_name', $inv) ?>"
                               class="w-full pl-8 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"
                               placeholder="Search or type company name…">
                    </div>
                    <div x-show="open && results.length > 0" x-transition
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-52 overflow-y-auto">
                        <template x-for="r in results" :key="r.id">
                            <div @click="selectPartner(r)"
                                 class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-building text-blue-500 text-[10px]"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Phone</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"><i class="fas fa-phone text-xs"></i></span>
                        <input type="text" name="company_phone" id="ti_company_phone" readonly
                               class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm text-gray-500"
                               placeholder="Auto-filled">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"><i class="fas fa-map-marker-alt text-xs"></i></span>
                        <input type="text" name="company_address" id="ti_company_address" readonly
                               class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm text-gray-500"
                               placeholder="Auto-filled">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Invoice Date</label>
                    <input type="date" name="invoice_date"
                           value="<?= inv_val('invoice_date', $inv, date('Y-m-d')) ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div x-data="{ method: '<?= inv_val('payment_method', $inv) ?>' }">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        Payment Method
                    </label>
                    <?php
                    $pmIcons = [
                        ''              => 'fa-question-circle text-gray-400',
                        'cash'          => 'fa-money-bill-wave text-emerald-500',
                        'bank_transfer' => 'fa-university text-blue-500',
                        'wire_transfer' => 'fa-globe text-indigo-500',
                        'credit_card'   => 'fa-credit-card text-purple-500',
                        'debit_card'    => 'fa-credit-card text-teal-500',
                        'check'         => 'fa-money-check text-amber-500',
                        'credit'        => 'fa-coins text-yellow-500',
                        'crypto'        => 'fa-bitcoin-sign text-orange-500',
                        'online'        => 'fa-laptop text-cyan-500',
                        'other'         => 'fa-ellipsis-h text-gray-400',
                    ];
                    ?>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <?php foreach ($pmIcons as $pmv => $pmc): ?>
                            <i class="fas <?= $pmc ?> text-sm"
                               x-show="method === '<?= $pmv ?>'"
                               <?= $pmv !== inv_val('payment_method', $inv) ? 'style="display:none"' : '' ?>></i>
                            <?php endforeach; ?>
                        </div>
                        <select name="payment_method"
                                x-model="method"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 appearance-none">
                            <?php foreach ($payMethods as $v => $l): ?>
                            <option value="<?= $v ?>"<?= sel($v, inv_val('payment_method', $inv)) ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <!-- Method badge -->
                    <?php
                    $pmBadges = [
                        'cash'          => ['label' => 'Instant', 'class' => 'bg-emerald-100 text-emerald-700'],
                        'bank_transfer' => ['label' => '1–3 days', 'class' => 'bg-blue-100 text-blue-700'],
                        'wire_transfer' => ['label' => 'SWIFT', 'class' => 'bg-indigo-100 text-indigo-700'],
                        'credit_card'   => ['label' => 'Card', 'class' => 'bg-purple-100 text-purple-700'],
                        'debit_card'    => ['label' => 'Card', 'class' => 'bg-teal-100 text-teal-700'],
                        'check'         => ['label' => 'Cheque', 'class' => 'bg-amber-100 text-amber-700'],
                        'credit'        => ['label' => 'Partner Credit', 'class' => 'bg-yellow-100 text-yellow-700'],
                        'crypto'        => ['label' => 'Digital', 'class' => 'bg-orange-100 text-orange-700'],
                        'online'        => ['label' => 'Online', 'class' => 'bg-cyan-100 text-cyan-700'],
                    ];
                    foreach ($pmBadges as $pbv => $pbd): ?>
                    <div x-show="method === '<?= $pbv ?>'" style="display:none" class="mt-1.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $pbd['class'] ?>">
                            <i class="fas fa-tag text-[8px]"></i> <?= $pbd['label'] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <!-- ══════════════ 2 · TRANSFERS + PER-LEG PRICING ══════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Transfers &amp; Pricing</h3>
                    <span class="text-xs text-gray-400 font-normal">— each leg has its own price</span>
                </div>
                <select name="currency" x-model="currency"
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($currencies as $c): ?>
                    <option value="<?= $c ?>"<?= sel($c, $jsCurrency) ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="p-6 space-y-3">

                <!-- ── MAIN LEG ── (no nested x-data — all bindings in parent scope) -->
                <div class="bg-teal-50 dark:bg-teal-900/10 border border-teal-200 dark:border-teal-800 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-[11px] font-bold flex items-center justify-center flex-shrink-0">1</span>
                            <span class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider">Main Transfer</span>
                        </div>
                        <!-- Catalog search (main) -->
                        <div class="relative" @click.outside="mainSearch.open = false">
                            <button type="button"
                                    @click="mainSearch.open = !mainSearch.open; if(mainSearch.open && !mainSearch.query) searchRoutes('main','')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-700 border border-teal-300 dark:border-teal-700 text-teal-700 dark:text-teal-300 rounded-lg text-xs font-semibold hover:bg-teal-50 transition">
                                <i class="fas fa-database text-[10px]"></i>
                                <span x-text="mainSearch.name || 'Search Catalog'"></span>
                                <i class="fas fa-chevron-down text-[9px] ml-0.5"></i>
                            </button>
                            <div x-show="mainSearch.open" x-transition @click.stop
                                 class="absolute right-0 z-50 mt-1 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl">
                                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 pointer-events-none"><i class="fas fa-search text-[10px]"></i></span>
                                        <input type="text" x-model="mainSearch.query"
                                               @input.debounce.250ms="searchRoutes('main', mainSearch.query)"
                                               placeholder="Type destination or airport…"
                                               class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                    </div>
                                </div>
                                <div class="max-h-52 overflow-y-auto">
                                    <div x-show="mainSearch.results.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">No transfers found</div>
                                    <template x-for="r in mainSearch.results" :key="r.id">
                                        <button type="button" @click="selectRoute('main', r)"
                                                class="w-full text-left px-3 py-2.5 hover:bg-teal-50 dark:hover:bg-teal-900/30 border-b border-gray-50 dark:border-gray-700 last:border-0 transition">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                        <span class="text-teal-600" x-text="r.from_location || r.name"></span>
                                                        <span class="mx-1 text-gray-400">→</span>
                                                        <span x-text="r.to_location || ''"></span>
                                                    </div>
                                                    <div class="text-[10px] text-gray-400" x-text="r.description || ''"></div>
                                                </div>
                                                <div class="text-right flex-shrink-0">
                                                    <div class="text-sm font-bold text-teal-700" x-text="r.currency + ' ' + parseFloat(r.price).toFixed(2)"></div>
                                                    <div class="text-[9px] text-gray-400 uppercase" x-text="r.unit || ''"></div>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catalog match badge -->
                    <div x-show="mainSearch.name" class="mb-3 flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                        <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                        <span class="text-xs font-semibold text-emerald-700" x-text="'From catalog: ' + mainSearch.name"></span>
                        <button type="button" @click="mainSearch.name=''" class="ml-auto text-gray-400 hover:text-red-500 text-xs"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-circle text-teal-400 mr-1" style="font-size:8px"></i>Pickup Location *</label>
                            <input type="text" name="pickup_location" x-model="startingPoint" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                                   placeholder="e.g. Istanbul Airport (IST)">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>Drop-off Location *</label>
                            <input type="text" name="dropoff_location" x-model="destination" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                                   placeholder="e.g. Hilton Istanbul Bosphorus">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date *</label>
                            <input type="date" name="pickup_date" required
                                   value="<?= ml_val('date', $ml) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Time</label>
                            <input type="time" name="pickup_time"
                                   value="<?= ml_val('time', $ml) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                            <select name="transfer_type" x-model="transferType"
                                    @change="if(mainCatalogPrice > 0) mainPrice = mainCatalogPrice * (transferType === 'round_trip' ? 2 : 1)"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <option value="one_way"<?= sel('one_way', ml_val('type', $ml, 'one_way')) ?>>One Way</option>
                                <option value="round_trip"<?= sel('round_trip', ml_val('type', $ml, 'one_way')) ?>>Round Trip</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-plane mr-1 text-gray-400 text-[10px]"></i>Flight No.</label>
                            <input type="text" name="flight_number"
                                   value="<?= ml_val('flight', $ml) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm uppercase tracking-wider"
                                   placeholder="TK801">
                        </div>
                    </div>

                    <div x-show="transferType === 'round_trip'" x-transition class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Return Date</label>
                            <input type="date" name="return_date" value="<?= ml_val('returnDate', $ml) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Return Time</label>
                            <input type="time" name="return_time" value="<?= ml_val('returnTime', $ml) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-teal-200 dark:border-teal-800 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider mb-1">Price for this leg</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs" x-text="currency"></span>
                                <input type="number" x-model.number="mainPrice" step="0.01" min="0"
                                       class="w-full pl-10 pr-4 py-2 border border-teal-300 dark:border-teal-700 rounded-lg bg-white dark:bg-gray-700 text-sm font-semibold text-teal-800 dark:text-teal-200 focus:ring-2 focus:ring-teal-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Pax</label>
                            <input type="number" name="total_pax" x-model.number="totalPax" min="1"
                                   value="<?= inv_val('total_pax', $inv, 1) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- ── EXTRA STOPS ── (no nested x-data — stop._xxx fields hold search state) -->
                <template x-for="(stop, idx) in stops" :key="idx">
                    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-[11px] font-bold flex items-center justify-center flex-shrink-0" x-text="idx + 2"></span>
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Additional Transfer</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Catalog search (stop) -->
                                <div class="relative" @click.outside="stop._open = false">
                                    <button type="button"
                                            @click="stop._open = !stop._open; if(stop._open && !stop._q) searchRoutes(idx,'')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-700 border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-semibold hover:bg-blue-50 transition">
                                        <i class="fas fa-database text-[10px]"></i>
                                        <span x-text="stop._name || 'Search Catalog'"></span>
                                        <i class="fas fa-chevron-down text-[9px] ml-0.5"></i>
                                    </button>
                                    <div x-show="stop._open" x-transition @click.stop
                                         class="absolute right-0 z-50 mt-1 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl">
                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 pointer-events-none"><i class="fas fa-search text-[10px]"></i></span>
                                                <input type="text" x-model="stop._q"
                                                       @input.debounce.250ms="searchRoutes(idx, stop._q)"
                                                       placeholder="Type destination or airport…"
                                                       class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                            </div>
                                        </div>
                                        <div class="max-h-52 overflow-y-auto">
                                            <div x-show="stop._results.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">No transfers found</div>
                                            <template x-for="r in stop._results" :key="r.id">
                                                <button type="button" @click="selectRoute(idx, r)"
                                                        class="w-full text-left px-3 py-2.5 hover:bg-blue-50 dark:hover:bg-blue-900/30 border-b border-gray-50 dark:border-gray-700 last:border-0 transition">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="min-w-0">
                                                            <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                                <span class="text-blue-600" x-text="r.from_location || r.name"></span>
                                                                <span class="mx-1 text-gray-400">→</span>
                                                                <span x-text="r.to_location || ''"></span>
                                                            </div>
                                                            <div class="text-[10px] text-gray-400" x-text="r.description || ''"></div>
                                                        </div>
                                                        <div class="text-right flex-shrink-0">
                                                            <div class="text-sm font-bold text-blue-700" x-text="r.currency + ' ' + parseFloat(r.price).toFixed(2)"></div>
                                                            <div class="text-[9px] text-gray-400 uppercase" x-text="r.unit || ''"></div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" @click="removeStop(idx)"
                                        class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="Remove">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Catalog match badge for stop -->
                        <div x-show="stop._name" class="mb-3 flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                            <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                            <span class="text-xs font-semibold text-emerald-700" x-text="'From catalog: ' + stop._name"></span>
                            <button type="button" @click="stop._name=''" class="ml-auto text-gray-400 hover:text-red-500 text-xs"><i class="fas fa-times"></i></button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-circle text-blue-400 mr-1" style="font-size:8px"></i>From</label>
                                <input type="text" x-model="stop.from" placeholder="Pickup point"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>To</label>
                                <input type="text" x-model="stop.to" placeholder="Drop-off point"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                <select x-model="stop.type"
                                        @change="if(stop._catalogPrice > 0) stop.price = stop._catalogPrice * (stop.type === 'round_trip' ? 2 : 1)"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="one_way">One Way</option>
                        <option value="round_trip">Round Trip</option>
                    </select>
                </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input type="date" x-model="stop.date"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Time</label>
                                <input type="time" x-model="stop.time"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                                <label class="block text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-1">Price</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs" x-text="currency"></span>
                                    <input type="number" x-model.number="stop.price" step="0.01" min="0"
                                           class="w-full pl-10 pr-2 py-2 border border-blue-300 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-700 text-sm font-semibold text-blue-800 dark:text-blue-200 focus:ring-2 focus:ring-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Add stop button -->
                <button type="button" @click="addStop()"
                        class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-blue-300 dark:border-blue-700 text-blue-500 dark:text-blue-400 rounded-xl text-sm font-medium hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                    <i class="fas fa-plus-circle"></i> Add Another Transfer / Stop
                </button>

            </div>
        </div>

        <!-- ══════════════ 3 · INVOICE SUMMARY ══════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Invoice Summary</h3>
            </div>
            <div class="p-6">

                <!-- Live subtotal from all legs -->
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4 mb-5 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Leg Breakdown</span>
                        <span class="text-xs text-gray-400" x-text="stops.length + 1 + ' leg(s)'"></span>
                    </div>
                    <!-- Main leg line -->
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-200 dark:border-gray-600">
                        <span class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-full bg-teal-500 text-white text-[9px] font-bold flex items-center justify-center">1</span>
                            <span x-text="(startingPoint || '…') + ' → ' + (destination || '…')"></span>
                        </span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="currency + ' ' + (mainPrice || 0).toFixed(2)"></span>
                    </div>
                    <!-- Extra stop lines -->
                    <template x-for="(stop, idx) in stops" :key="idx">
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-200 dark:border-gray-600 last:border-0">
                            <span class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-blue-500 text-white text-[9px] font-bold flex items-center justify-center" x-text="idx + 2"></span>
                                <span x-text="(stop.from || '…') + ' → ' + (stop.to || '…')"></span>
                            </span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="currency + ' ' + (stop.price || 0).toFixed(2)"></span>
                        </div>
                    </template>
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center pt-2 mt-1">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Subtotal</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white" x-text="currency + ' ' + grandSubtotal.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Tax / Discount / Paid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" x-model.number="taxRate" @input="recalcTotal()"
                               value="<?= inv_val('tax_rate', $inv, 0) ?>"
                               min="0" max="100" step="0.01"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Discount</label>
                        <input type="number" name="discount" x-model.number="discount" @input="recalcTotal()"
                               value="<?= inv_val('discount', $inv, 0) ?>"
                               min="0" step="0.01"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Paid Amount</label>
                        <input type="number" name="paid_amount"
                               value="<?= inv_val('paid_amount', $inv, 0) ?>"
                               min="0" step="0.01"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Grand total banner -->
                <div class="mt-4 flex items-center justify-between bg-blue-600 text-white rounded-xl px-5 py-3">
                    <span class="font-semibold text-sm uppercase tracking-wider">Grand Total</span>
                    <span class="text-2xl font-bold" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
                </div>

                <input type="hidden" name="tax_amount">
            </div>
        </div>

        <!-- ══════════════ 4 · GUESTS ══════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="invGuestList()">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">4</span>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Guests / Passengers</h3>
                </div>
                <button type="button" @click="addGuest()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition">
                    <i class="fas fa-plus"></i> Add Guest
                </button>
            </div>
            <div class="p-6 space-y-3">
                <input type="hidden" name="guests_json" :value="JSON.stringify(guests)">
                <template x-for="(guest, idx) in guests" :key="idx">
                    <div :class="idx === 0
                            ? 'border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/10 rounded-xl p-4'
                            : 'border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4'">
                        <div class="flex items-center gap-2 mb-3">
                            <span :class="idx === 0 ? 'text-xs font-bold text-amber-700 dark:text-amber-400' : 'text-xs font-bold text-gray-500 dark:text-gray-400'"
                                  x-text="idx === 0 ? 'Lead Guest ★' : 'Guest ' + (idx + 1)"></span>
                            <button x-show="idx > 0" type="button" @click="removeGuest(idx)"
                                    class="ml-auto text-red-400 hover:text-red-600 transition text-xs">
                                <i class="fas fa-times-circle"></i> Remove
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Full Name *</label>
                                <input type="text" x-model="guest.name" :required="idx === 0"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"
                                       placeholder="Full name">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Passport / ID No.</label>
                                <input type="text" x-model="guest.passport"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm font-mono tracking-wider uppercase"
                                       placeholder="Passport number">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ══════════════ 5 · NOTES ══════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">5</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Notes &amp; Terms</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Internal Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 resize-none"
                              placeholder="VIP pickup, special instructions…"><?= htmlspecialchars($inv['notes'] ?? '') ?></textarea>
                </div>
        <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Terms &amp; Conditions</label>
                    <textarea name="terms" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 resize-none"
                              placeholder="Payment terms, cancellation policy…"><?= htmlspecialchars($inv['terms'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── ACTIONS ── -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="<?= url('transfer-invoice') ?>"
               class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
                Cancel
            </a>
            <?php if ($isEdit): ?>
            <a href="<?= url('invoices/show') ?>?id=<?= (int)$inv['id'] ?>"
               class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition flex items-center gap-2">
                <i class="fas fa-eye"></i> View Invoice
            </a>
            <?php endif; ?>
            <button type="submit"
                    class="px-7 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas <?= $submitIcon ?>"></i> <?= $submitLabel ?>
            </button>
        </div>

        </div>
    </form>

<script>
function invoicePartnerSearch(idFieldId, phoneFieldId) {
    return {
        query: document.querySelector('[name="company_name"]')?.value || '',
        results: [], open: false,
        async search() {
            if (this.query.length < 1) { this.results = []; this.open = false; return; }
            try {
                const res = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.open = this.results.length > 0;
            } catch(e) { this.results = []; }
        },
        selectPartner(r) {
            this.query = r.company_name;
            this.open  = false;
            const idEl    = document.getElementById(idFieldId);
            const phoneEl = document.getElementById(phoneFieldId || '');
            const addrEl  = document.getElementById('ti_company_address');
            if (idEl)    idEl.value    = r.id;
            if (phoneEl) phoneEl.value = r.phone || '';
            if (addrEl)  addrEl.value  = [r.address, r.city, r.country].filter(Boolean).join(', ');
        }
    };
}

function transferInvoiceForm() {
    return {
        /* ── core state ── */
        startingPoint:     <?= json_encode(ml_val('from', $ml)) ?>,
        destination:       <?= json_encode(ml_val('to',   $ml)) ?>,
        transferType:      <?= json_encode(ml_val('type', $ml, 'one_way')) ?>,
        totalPax:          <?= (int)($inv['total_pax'] ?? 1) ?>,
        mainPrice:         <?= $jsMainPrice ?>,
        mainCatalogPrice:  <?= $jsMainPrice ?>, /* base price from catalog (before ×2) */
        currency:          <?= json_encode($jsCurrency) ?>,
        taxRate:           <?= $jsTaxRate ?>,
        discount:          <?= $jsDiscount ?>,

        /* stops array — each stop carries type, catalog base price, and search state */
        stops: (<?= $jsStops ?>).map(s => Object.assign(
            { type: 'one_way', _catalogPrice: 0, _q: '', _results: [], _open: false, _name: '' }, s
        )),

        /* main-leg search state */
        mainSearch: { query: '', results: [], open: false, name: '' },

        /* ── computed totals ── */
        get grandSubtotal() {
            return (parseFloat(this.mainPrice) || 0) +
                this.stops.reduce((s, st) => s + (parseFloat(st.price) || 0), 0);
        },
        get grandTotal() {
            const tax = this.grandSubtotal * (parseFloat(this.taxRate) || 0) / 100;
            return Math.max(0, this.grandSubtotal + tax - (parseFloat(this.discount) || 0));
        },

        recalcTotal() { /* getters are reactive — no-op needed */ },

        /* ── stop management ── */
        addStop() {
            this.stops.push({ from: '', to: '', date: '', time: '', price: 0,
                              type: 'one_way', _catalogPrice: 0,
                              _q: '', _results: [], _open: false, _name: '' });
        },
        removeStop(idx) { this.stops.splice(idx, 1); },

        /* ── catalog search ── */
        async searchRoutes(target, q) {
            const url = '<?= url('api/services/search') ?>?type=transfer'
                      + (q ? '&q=' + encodeURIComponent(q) : '');
            try {
                const data = await fetch(url).then(r => r.json());
                if (target === 'main') {
                    this.mainSearch.results = data;
                } else {
                    this.stops[target]._results = data;
                }
            } catch(e) { /* network error — silently ignore */ }
        },

        /* ── select a route from the dropdown ── */
        selectRoute(target, r) {
            const from           = r.from_location || (r.name.split('→')[0] || r.name).trim();
            const to             = r.to_location   || (r.name.split('→')[1] || '').trim();
            const price          = parseFloat(r.price) || 0;
            const perPersonBase  = r.unit === 'per_person' ? price * (this.totalPax || 1) : price;

            if (target === 'main') {
                const multi           = this.transferType === 'round_trip' ? 2 : 1;
                this.startingPoint    = from;
                this.destination      = to;
                this.mainCatalogPrice = perPersonBase;
                this.mainPrice        = perPersonBase * multi;
                this.mainSearch.name  = r.name + (multi === 2 ? ' (×2 Round Trip)' : '')
                                        + ' · ' + (r.currency || '') + ' ' + price.toFixed(2);
                this.mainSearch.open  = false;
                this.mainSearch.query = '';
            } else {
                const stop = this.stops[target];
                if (stop) {
                    const multi        = stop.type === 'round_trip' ? 2 : 1;
                    stop.from          = from;
                    stop.to            = to;
                    stop._catalogPrice = perPersonBase;
                    stop.price         = perPersonBase * multi;
                    stop._name         = r.name + (multi === 2 ? ' (×2 Round Trip)' : '')
                                         + ' · ' + (r.currency || '') + ' ' + price.toFixed(2);
                    stop._open         = false;
                    stop._q            = '';
                }
            }
        }
    };
}

function invGuestList() {
    return {
        guests: <?= $jsGuests ?>,
        addGuest()       { this.guests.push({ name: '', passport: '' }); },
        removeGuest(idx) { if (idx === 0) return; this.guests.splice(idx, 1); }
    };
}
</script>
