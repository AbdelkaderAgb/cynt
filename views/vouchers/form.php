<?php
/**
 * Transfer Voucher Form — Create / Edit
 * Architecture: numbered sections, catalog search, multi-stop builder, guest manifest.
 */
$v = $voucher;

/* ── Alpine init data ── */
$initTransferType = htmlspecialchars(($v['transfer_type'] ?? 'one_way') === 'multi_stop' ? 'one_way' : ($v['transfer_type'] ?? 'one_way'));
$rawStops     = array_values(array_filter(json_decode($v['stops_json'] ?? '[]', true) ?: []));
$jsStops      = json_encode($rawStops, JSON_UNESCAPED_UNICODE);
$rawPassengers = $v['passengers'] ?? '';
$initGuests   = [];
if (!empty($rawPassengers) && substr(trim($rawPassengers), 0, 1) === '[') {
    $initGuests = json_decode($rawPassengers, true) ?: [];
}
if (empty($initGuests)) {
    $initGuests = [['name' => $v['guest_name'] ?? '', 'passport' => $v['passenger_passport'] ?? '']];
}
$jsGuests = json_encode($initGuests, JSON_UNESCAPED_UNICODE);
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-shuttle-van text-blue-500"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= $isEdit ? 'Update transfer route, passengers, and assignment.' : 'Create a new transfer voucher — add multiple stops if needed.' ?></p>
    </div>
    <a href="<?= url('vouchers') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition shrink-0">
        <i class="fas fa-arrow-left text-xs"></i> Back to List
    </a>
</div>

<form method="POST" action="<?= url('vouchers/store') ?>" id="voucherForm" class="space-y-5" x-data="{sub:false}" @submit="sub=true">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><?php endif; ?>
    <input type="hidden" name="company_id" id="vchr_company_id" value="<?= e($v['company_id'] ?? '') ?>">

    <!-- ══════════ 1 · BILLING PARTY ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Billing Party</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4"
             x-data="voucherPartnerSearch()" x-init="initPartner()" @click.outside="open=false">

            <!-- Company search -->
            <div class="relative md:col-span-2 sm:col-span-1 md:col-span-1">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Company / Agency *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fas fa-building text-xs"></i>
                    </span>
                    <input type="text" name="company_name" x-model="query"
                           @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                           required autocomplete="off"
                           value="<?= e($v['company_name'] ?? '') ?>"
                           class="w-full pl-8 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"
                           placeholder="Type to search registered partners…">
                </div>
                <div x-show="open && results.length > 0" x-transition
                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-52 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="select(r)"
                             class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
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
                    <input type="text" name="company_phone" id="vchr_phone" readonly
                           value="<?= e($v['company_phone'] ?? '') ?>"
                           class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm text-gray-500"
                           placeholder="Auto-filled">
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"><i class="fas fa-map-marker-alt text-xs"></i></span>
                    <input type="text" name="company_address" id="vchr_address" readonly
                           value="<?= e($v['company_address'] ?? '') ?>"
                           class="w-full pl-8 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm text-gray-500"
                           placeholder="Auto-filled">
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ 2 · TRANSFER DETAILS ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="voucherTransferForm()">

        <!-- Hidden sync -->
        <input type="hidden" name="stops_json" :value="JSON.stringify(stops)">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Route &amp; Schedule</h3>
                <span class="text-xs text-gray-400 font-normal hidden sm:inline">— add stops for multi-leg routes</span>
            </div>
            <div class="flex items-center gap-2">
                <select name="transfer_type" x-model="transferType"
                        class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="one_way">One Way</option>
                    <option value="round_trip">Round Trip</option>
                </select>
            </div>
        </div>

        <div class="p-6 space-y-4">

            <!-- ── MAIN LEG ── -->
            <div class="bg-teal-50 dark:bg-teal-900/10 border border-teal-200 dark:border-teal-800 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                        <span class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider">Main Transfer</span>
                    </div>
                    <!-- Catalog search for main leg -->
                    <div class="relative" @click.outside="mainOpen = false">
                        <button type="button"
                                @click="mainOpen = !mainOpen; if(mainOpen && !mainResults.length) fetchCatalog('main','')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-700 border border-teal-300 dark:border-teal-700 text-teal-700 dark:text-teal-300 rounded-lg text-xs font-semibold hover:bg-teal-50 transition">
                            <i class="fas fa-database text-[10px]"></i>
                            <span x-text="mainCatalogName || 'Catalog'"></span>
                            <i class="fas fa-chevron-down text-[9px] ml-0.5"></i>
                        </button>
                        <div x-show="mainOpen" x-transition @click.stop
                             class="absolute right-0 z-50 mt-1 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 pointer-events-none"><i class="fas fa-search text-[10px]"></i></span>
                                    <input type="text" x-model="mainQ" @input.debounce.250ms="fetchCatalog('main', mainQ)"
                                           placeholder="Search routes…"
                                           class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                </div>
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                <div x-show="!mainResults.length" class="px-4 py-3 text-xs text-gray-400 text-center">No routes found</div>
                                <template x-for="r in mainResults" :key="r.id">
                                    <button type="button" @click="pickCatalog('main', r)"
                                            class="w-full text-left px-3 py-2 hover:bg-teal-50 dark:hover:bg-teal-900/30 border-b border-gray-50 dark:border-gray-700 last:border-0 transition">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                    <span class="text-teal-600" x-text="r.from_location || r.name"></span>
                                                    <span class="mx-1 text-gray-400">→</span>
                                                    <span x-text="r.to_location || ''"></span>
                                                </div>
                                            </div>
                                            <div class="text-xs font-bold text-teal-700 shrink-0" x-text="(r.currency || '') + ' ' + parseFloat(r.price||0).toFixed(2)"></div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catalog badge -->
                <div x-show="mainCatalogName" class="mb-3 flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                    <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                    <span class="text-xs font-semibold text-emerald-700 truncate" x-text="'Catalog: ' + mainCatalogName"></span>
                    <button type="button" @click="mainCatalogName=''" class="ml-auto text-gray-400 hover:text-red-500 text-xs shrink-0"><i class="fas fa-times"></i></button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            <i class="fas fa-circle text-teal-400 mr-1" style="font-size:8px"></i>Pickup Location *
                        </label>
                        <input type="text" name="pickup_location" x-model="mainFrom"
                               value="<?= e($v['pickup_location'] ?? '') ?>" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g. Istanbul Airport (IST)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            <i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>Drop-off Location *
                        </label>
                        <input type="text" name="dropoff_location" x-model="mainTo"
                               value="<?= e($v['dropoff_location'] ?? '') ?>" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="e.g. Hilton Bosphorus">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date *</label>
                        <input type="date" name="pickup_date" value="<?= $v['pickup_date'] ?? '' ?>" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Time *</label>
                        <input type="time" name="pickup_time" value="<?= $v['pickup_time'] ?? '' ?>" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Total Pax</label>
                        <input type="number" name="total_pax" value="<?= $v['total_pax'] ?? 1 ?>" min="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-plane text-gray-400 mr-1 text-[10px]"></i>Flight No.</label>
                        <input type="text" name="flight_number" value="<?= e($v['flight_number'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm uppercase tracking-wider"
                               placeholder="TK801">
                    </div>
                </div>

                <!-- Round-trip return fields -->
                <div x-show="transferType === 'round_trip'" x-transition class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-undo-alt text-indigo-400 mr-1 text-[10px]"></i>Return Date</label>
                        <input type="date" name="return_date" value="<?= $v['return_date'] ?? '' ?>"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-undo-alt text-indigo-400 mr-1 text-[10px]"></i>Return Time</label>
                        <input type="time" name="return_time" value="<?= $v['return_time'] ?? '' ?>"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- ── EXTRA STOPS ── -->
            <template x-for="(stop, idx) in stops" :key="idx">
                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-[11px] font-bold flex items-center justify-center shrink-0" x-text="idx + 2"></span>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Additional Transfer</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Stop catalog search -->
                            <div class="relative" @click.outside="stop._open = false">
                                <button type="button"
                                        @click="stop._open = !stop._open; if(stop._open && !stop._results.length) fetchCatalog(idx,'')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-gray-700 border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-semibold hover:bg-blue-50 transition">
                                    <i class="fas fa-database text-[10px]"></i>
                                    <span x-text="stop._name || 'Catalog'"></span>
                                    <i class="fas fa-chevron-down text-[9px] ml-0.5"></i>
                                </button>
                                <div x-show="stop._open" x-transition @click.stop
                                     class="absolute right-0 z-50 mt-1 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl">
                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 pointer-events-none"><i class="fas fa-search text-[10px]"></i></span>
                                            <input type="text" x-model="stop._q" @input.debounce.250ms="fetchCatalog(idx, stop._q)"
                                                   placeholder="Search routes…"
                                                   class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto">
                                        <div x-show="!stop._results.length" class="px-4 py-3 text-xs text-gray-400 text-center">No routes found</div>
                                        <template x-for="r in stop._results" :key="r.id">
                                            <button type="button" @click="pickCatalog(idx, r)"
                                                    class="w-full text-left px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 border-b border-gray-50 dark:border-gray-700 last:border-0 transition">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                        <span class="text-blue-600" x-text="r.from_location || r.name"></span>
                                                        <span class="mx-1 text-gray-400">→</span>
                                                        <span x-text="r.to_location || ''"></span>
                                                    </div>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="removeStop(idx)"
                                    class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Catalog badge for stop -->
                    <div x-show="stop._name" class="mb-3 flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg">
                        <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                        <span class="text-xs font-semibold text-emerald-700 truncate" x-text="'Catalog: ' + stop._name"></span>
                        <button type="button" @click="stop._name=''" class="ml-auto text-gray-400 hover:text-red-500 text-xs shrink-0"><i class="fas fa-times"></i></button>
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
                    <div class="grid grid-cols-3 gap-3 mt-3">
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
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-plane text-gray-400 mr-1 text-[10px]"></i>Flight No.</label>
                            <input type="text" x-model="stop.flight" placeholder="Optional"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm uppercase tracking-wider">
                        </div>
                    </div>
                </div>
            </template>

            <!-- Add stop button -->
            <button type="button" @click="addStop()"
                    class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-blue-300 dark:border-blue-700 text-blue-500 dark:text-blue-400 rounded-xl text-sm font-medium hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                <i class="fas fa-plus-circle"></i> Add Transfer Stop
            </button>
        </div>
    </div>

    <!-- ══════════ 3 · ASSIGNMENT ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Fleet &amp; Assignment</h3>
            <span class="text-xs text-gray-400 font-normal hidden sm:inline">— optional</span>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-id-card text-purple-400 mr-1 text-[10px]"></i>Driver
                </label>
                <select name="driver_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— Optional —</option>
                    <?php foreach ($drivers ?? [] as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($v['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['name'] ?? trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''))) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-car text-blue-400 mr-1 text-[10px]"></i>Vehicle
                </label>
                <select name="vehicle_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— Optional —</option>
                    <?php foreach ($vehicles ?? [] as $vh): ?>
                    <option value="<?= $vh['id'] ?>" <?= ($v['vehicle_id'] ?? '') == $vh['id'] ? 'selected' : '' ?>>
                        <?= e($vh['plate_number']) ?> — <?= e($vh['make'] . ' ' . $vh['model']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-map-signs text-emerald-400 mr-1 text-[10px]"></i>Tour Guide
                </label>
                <select name="guide_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">— Optional —</option>
                    <?php foreach ($guides ?? [] as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($v['guide_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                        <?= e($g['name'] ?? trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''))) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-tag text-amber-400 mr-1 text-[10px]"></i>Status
                </label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="pending"   <?= ($v['status'] ?? 'pending') === 'pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($v['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="completed" <?= ($v['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($v['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ══════════ 4 · GUESTS ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="voucherGuestList()">
        <input type="hidden" name="guests_json" :value="JSON.stringify(guests)">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">4</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Passengers</h3>
                <span class="ml-1 text-xs text-gray-400 font-normal hidden sm:inline">— first passenger is Lead</span>
            </div>
            <button type="button" @click="add()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition">
                <i class="fas fa-plus text-[10px]"></i> Add Passenger
            </button>
        </div>

        <div class="p-6 space-y-3">
            <template x-for="(g, idx) in guests" :key="idx">
                <div :class="idx === 0
                    ? 'flex items-center gap-3 border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/10 rounded-xl p-3.5'
                    : 'flex items-center gap-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3.5'">
                    <!-- Number badge -->
                    <div :class="idx === 0
                        ? 'w-8 h-8 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center shrink-0'
                        : 'w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-500 text-white text-xs font-bold flex items-center justify-center shrink-0'"
                         x-text="idx + 1"></div>
                    <!-- Name -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                            <span x-show="idx === 0">Lead Passenger <span class="text-amber-500">★</span></span>
                            <span x-show="idx > 0">Passenger Name</span>
                        </label>
                        <input type="text" x-model="g.name"
                               :placeholder="idx === 0 ? 'Full name (appears on voucher)' : 'Guest ' + (idx + 1) + ' name'"
                               :required="idx === 0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Passport -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                            <i class="fas fa-passport text-amber-400 mr-0.5"></i> Passport / ID
                        </label>
                        <input type="text" x-model="g.passport"
                               placeholder="Passport number"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm font-mono tracking-wider uppercase focus:ring-2 focus:ring-blue-500">
                    </div>
                    <!-- Remove -->
                    <button type="button" x-show="idx > 0" @click="remove(idx)"
                            class="w-8 h-8 shrink-0 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                    <div x-show="idx === 0" class="w-8 shrink-0"></div>
                </div>
            </template>
        </div>

        <!-- Notes -->
        <div class="px-6 pb-6 border-t border-gray-100 dark:border-gray-700 pt-4">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                <i class="fas fa-sticky-note text-amber-400 mr-1 text-[10px]"></i>Notes / Special Instructions
            </label>
            <textarea name="notes" rows="2"
                      class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 resize-none"
                      placeholder="VIP pickup, meeting point, special requirements…"><?= e($v['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- ── ACTIONS ── -->
    <div class="flex items-center justify-end gap-3 pt-1">
        <a href="<?= url('vouchers') ?>"
           class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
            Cancel
        </a>
        <?php if ($isEdit): ?>
        <a href="<?= url('vouchers/show') ?>?id=<?= $v['id'] ?>"
           class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition flex items-center gap-2">
            <i class="fas fa-eye"></i> View
        </a>
        <?php endif; ?>
        <button type="submit" :disabled="sub" :class="{'opacity-50 cursor-not-allowed':sub}"
                class="px-7 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-save"></i><span x-text="sub ? 'Saving…' : '<?= $isEdit ? 'Save Changes' : 'Create Voucher' ?>'"></span>
        </button>
    </div>
</form>

<script>
/* ── Partner search ── */
function voucherPartnerSearch() {
    return {
        query: document.querySelector('[name="company_name"]')?.value || '',
        results: [], open: false,

        async initPartner() {
            const name = this.query.trim();
            if (!name) return;
            try {
                const data = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(name)).then(r => r.json());
                const match = data.find(p => p.company_name === name) || data[0];
                if (match) this._fillPartner(match, false);
            } catch(e) {}
        },

        async search() {
            if (this.query.length < 1) { this.results = []; this.open = false; return; }
            try {
                this.results = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.query)).then(r => r.json());
                this.open = this.results.length > 0;
            } catch(e) { this.results = []; }
        },

        select(r) {
            this.query = r.company_name;
            this.open = false;
            this._fillPartner(r, true);
        },

        _fillPartner(r, overwrite) {
            document.getElementById('vchr_company_id').value = r.id || '';
            const phoneEl = document.getElementById('vchr_phone');
            const addrEl  = document.getElementById('vchr_address');
            if (phoneEl && (overwrite || !phoneEl.value)) phoneEl.value = r.phone || '';
            if (addrEl  && (overwrite || !addrEl.value))  addrEl.value  = [r.address, r.city, r.country].filter(Boolean).join(', ');
        }
    };
}

/* ── Transfer form (stops + catalog search) ── */
function voucherTransferForm() {
    return {
        transferType: '<?= $initTransferType ?>',
        mainFrom: <?= json_encode($v['pickup_location'] ?? '') ?>,
        mainTo:   <?= json_encode($v['dropoff_location'] ?? '') ?>,

        /* stops array — each carries catalog search state */
        stops: (<?= $jsStops ?>).map(s => Object.assign(
            { from:'', to:'', date:'', time:'', flight:'', _q:'', _results:[], _open:false, _name:'' }, s
        )),

        /* main-leg catalog */
        mainQ: '', mainResults: [], mainOpen: false, mainCatalogName: '',

        addStop() {
            this.stops.push({ from:'', to:'', date:'', time:'', flight:'', _q:'', _results:[], _open:false, _name:'' });
        },
        removeStop(idx) { this.stops.splice(idx, 1); },

        async fetchCatalog(target, q) {
            const url = '<?= url('api/services/search') ?>?type=transfer' + (q ? '&q=' + encodeURIComponent(q) : '');
            try {
                const data = await fetch(url).then(r => r.json());
                if (target === 'main') {
                    this.mainResults = data;
                } else {
                    this.stops[target]._results = data;
                }
            } catch(e) {}
        },

        pickCatalog(target, r) {
            const from = r.from_location || (r.name.split('→')[0] || r.name).trim();
            const to   = r.to_location   || (r.name.split('→')[1] || '').trim();
            if (target === 'main') {
                this.mainFrom       = from;
                this.mainTo         = to;
                this.mainCatalogName = r.name;
                this.mainOpen       = false;
                this.mainQ          = '';
                // Sync back to real inputs
                const piEl = document.querySelector('[name="pickup_location"]');
                const doEl = document.querySelector('[name="dropoff_location"]');
                if (piEl) piEl.value = from;
                if (doEl) doEl.value = to;
            } else {
                const stop = this.stops[target];
                if (stop) {
                    stop.from  = from;
                    stop.to    = to;
                    stop._name = r.name;
                    stop._open = false;
                    stop._q    = '';
                }
            }
        }
    };
}

/* ── Guest list ── */
function voucherGuestList() {
    return {
        guests: <?= $jsGuests ?>,
        add()        { this.guests.push({ name: '', passport: '' }); },
        remove(idx)  { if (idx === 0) return; this.guests.splice(idx, 1); }
    };
}
</script>
