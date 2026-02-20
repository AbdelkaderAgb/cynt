<?php
/**
 * New Transfer Form — with Multi-Stop Builder
 */
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('new_transfer') ?: 'New Transfer' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= __('create_transfer_desc') ?: 'Create a new transfer voucher' ?></p>
    </div>
    <a href="<?= url('vouchers') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-list"></i> <?= __('view_all_vouchers') ?: 'View All Vouchers' ?>
    </a>
</div>

<!-- Form -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
     x-data="transferForm()" x-init="init()">
    <form method="POST" action="<?= url('transfers/store') ?>" @submit.prevent="handleSubmit($el)" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="stops_json" :value="JSON.stringify(stops)">

        <!-- Company -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-400 mr-1"></i> Company</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative" x-data="transferPartnerSearch()" @click.outside="open = false">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('company_name') ?: 'Company' ?> *</label>
                    <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                           required autocomplete="off" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="<?= __('search_partner') ?: 'Search or type company name' ?>">
                    <div x-show="open && results.length > 0" x-transition
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="r in results" :key="r.id">
                            <div @click="selectPartner(r)" class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' - ' + r.phone : '')"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-phone text-green-500 mr-1"></i><?= __('phone') ?: 'Phone' ?></label>
                    <input type="text" name="company_phone" id="company_phone" readonly class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-gray-500" placeholder="Auto-filled from partner">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i><?= __('address') ?: 'Address' ?></label>
                <input type="text" name="company_address" id="company_address" readonly class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-gray-500" placeholder="Auto-filled from partner">
            </div>
        </div>

        <!-- Transfer Type & Pax -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-route text-blue-400 mr-1"></i> Transfer Info</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transfer Type</label>
                    <select name="transfer_type" x-model="transferType" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="one_way">One Way</option>
                        <option value="round_trip">Round Trip</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Pax</label>
                    <input type="number" name="total_pax" value="1" min="1" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                    <select name="currency" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="TRY">TRY</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Route & Schedule — main leg + optional extra stops via "+" -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider"><i class="fas fa-map-signs text-teal-400 mr-1"></i> Route & Schedule</h3>
                <button type="button" @click="addStop()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50 transition">
                    <i class="fas fa-plus text-[10px]"></i> Add Transfer
                </button>
            </div>

            <!-- Main (first) transfer leg -->
            <div class="bg-teal-50 dark:bg-teal-900/10 border border-teal-200 dark:border-teal-800 rounded-xl p-4 mb-3">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded-full bg-teal-500 text-white text-[11px] font-bold flex items-center justify-center flex-shrink-0">1</span>
                    <span class="text-xs font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider">Main Transfer</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-circle text-teal-400 mr-1" style="font-size:8px"></i>Starting Point *</label>
                        <input type="text" name="pickup_location" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="e.g. Istanbul Airport">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>Destination *</label>
                        <input type="text" name="dropoff_location" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="e.g. Hotel Bosphorus">
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-calendar mr-1 text-gray-400" style="font-size:8px"></i>Date *</label>
                        <input type="date" name="pickup_date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-clock mr-1 text-gray-400" style="font-size:8px"></i>Time *</label>
                        <input type="time" name="pickup_time" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div x-show="transferType === 'round_trip'" x-transition class="col-span-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-undo-alt mr-1 text-indigo-400" style="font-size:8px"></i>Return Date</label>
                        <input type="date" name="return_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div x-show="transferType === 'round_trip'" x-transition class="col-span-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-undo-alt mr-1 text-indigo-400" style="font-size:8px"></i>Return Time</label>
                        <input type="time" name="return_time" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Extra stops (added via "+" button) -->
            <template x-for="(stop, idx) in stops" :key="idx">
                <div class="relative bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-[11px] font-bold flex items-center justify-center flex-shrink-0" x-text="idx + 2"></span>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Additional Transfer</span>
                        </div>
                        <button type="button" @click="stops.splice(idx, 1); syncStopsJson()"
                                class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="Remove">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-circle text-blue-400 mr-1" style="font-size:8px"></i>From</label>
                            <input type="text" x-model="stop.from" @input="syncStopsJson()" placeholder="Pickup point"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>To</label>
                            <input type="text" x-model="stop.to" @input="syncStopsJson()" placeholder="Drop-off point"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-calendar mr-1 text-gray-400" style="font-size:8px"></i>Date</label>
                            <input type="date" x-model="stop.date" @change="syncStopsJson()"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-clock mr-1 text-gray-400" style="font-size:8px"></i>Time</label>
                            <input type="time" x-model="stop.time" @change="syncStopsJson()"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </template>

            <!-- Add another transfer button (shown after first extra stop is added) -->
            <div x-show="stops.length > 0" x-transition class="flex justify-center mt-1">
                <button type="button" @click="addStop()"
                        class="inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-blue-300 dark:border-blue-600 text-blue-500 dark:text-blue-400 rounded-xl text-sm font-medium hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                    <i class="fas fa-plus-circle"></i> Add Another Transfer
                </button>
            </div>
        </div>

        <!-- Guest & Passport -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-user text-blue-400 mr-1"></i> Passenger Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                    <input type="text" name="guest_name" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Main guest name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passenger Passport' ?></label>
                    <input type="text" name="passenger_passport" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Passport number">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">All Passenger Names</label>
                    <textarea name="passengers" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="One name per line"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-plane text-indigo-500 mr-1"></i>Flight Number</label>
                    <input type="text" name="flight_number" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. TK123">
                </div>
            </div>
        </div>

        <!-- Vehicle / Driver / Guide -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-car text-blue-400 mr-1"></i> Assignment <span class="font-normal text-gray-400 normal-case">(Optional)</span></h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehicle</label>
                    <select name="vehicle_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($vehicles ?? [] as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= e($v['plate_number']) ?> — <?= e($v['make'] . ' ' . $v['model']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver</label>
                    <select name="driver_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($drivers ?? [] as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= e($d['name'] ?? trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tour Guide</label>
                    <select name="guide_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($guides ?? [] as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= e($g['name'] ?? trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-sticky-note text-amber-400 mr-1"></i>Notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('vouchers') ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" :disabled="submitting" :class="{'opacity-50 cursor-not-allowed':submitting}" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><span x-text="submitting ? 'Saving…' : 'Create Transfer'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function transferForm() {
    return {
        transferType: 'one_way',
        stops: [],   // extra stops beyond the main leg
        submitting: false,

        init() {},

        addStop() {
            this.stops.push({ from: '', to: '', date: '', time: '' });
            this.syncStopsJson();
        },

        syncStopsJson() {
            const el = document.querySelector('[name="stops_json"]');
            if (el) el.value = JSON.stringify(this.stops);
            // If any extra stops exist, mark transfer_type as multi_stop in DB
            const ttEl = document.querySelector('[name="transfer_type"]');
            if (ttEl) {
                // Keep UI value for display, but submit multi_stop when extra stops present
                // We handle this server-side by reading stops_json length
            }
        },

        handleSubmit(form) {
            this.syncStopsJson();
            this.submitting = true;
            form.submit();
        }
    };
}

function transferPartnerSearch() {
    return {
        query: '', results: [], open: false,
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
            this.open = false;
            const phoneEl = document.getElementById('company_phone');
            const addrEl  = document.getElementById('company_address');
            if (phoneEl) phoneEl.value = r.phone || '';
            if (addrEl)  addrEl.value  = [r.address, r.city, r.country].filter(Boolean).join(', ');
        }
    };
}
</script>
