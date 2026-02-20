<?php
/**
 * Tour Voucher Create Form — Restructured
 * Removed: hotel_name, city, country, address, meeting_point, meeting_point_address,
 *          duration_hours, includes, excludes, separate pax counts
 * Added: per-tour-item pricing (adults, children, infants, price per type, subtotal)
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-map-marked-alt text-purple-500 mr-2"></i><?= __('tour_voucher') ?: 'New Tour Voucher' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= __('create') ?: 'Create' ?> <?= __('tour_voucher') ?: 'Tour Voucher' ?></p>
    </div>
    <a href="<?= url('tour-voucher') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> <?= __('back') ?: 'Back to List' ?>
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="tourVoucherForm()">
    <form method="POST" action="<?= url('tour-voucher/store') ?>" class="space-y-6" @submit="submitting=true">
        <?= csrf_field() ?>

        <!-- Hidden aggregated fields -->
        <input type="hidden" name="adults" :value="totalAdults">
        <input type="hidden" name="children" :value="totalChildren">
        <input type="hidden" name="infants" :value="totalInfants">
        <input type="hidden" name="tour_items" :value="tourItemsJson">

        <!-- Company & Contact -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i> <?= __('company_name') ?: 'Company' ?> & <?= __('contact') ?: 'Contact' ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('voucher_no') ?: 'Voucher No' ?></label>
                    <input type="text" name="voucher_no_display" disabled value="Auto-generated" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-400">
                </div>
                <div class="relative" x-data="tourPartnerSearch()" @click.outside="open = false">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('company_name') ?: 'Company Name' ?> *</label>
                    <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                           required autocomplete="off" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="<?= __('search_partner') ?: 'Search or enter company name' ?>">
                    <input type="hidden" name="company_id" id="tour_company_id" value="">
                    <div x-show="open && results.length > 0" x-transition
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="r in results" :key="r.id">
                            <div @click="selectPartner(r)" class="px-4 py-2.5 cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('customer_phone') ?: 'Customer Phone' ?></label>
                    <input type="text" name="customer_phone" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="+90 555 123 4567">
                </div>
            </div>
        </div>

        <!-- Guest Details -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-passport text-amber-500 mr-1"></i> <?= __('guest_info') ?: 'Guest Details' ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                    <input type="text" name="guest_name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="<?= __('guest_name') ?: 'Main guest name' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passenger Passport' ?></label>
                    <input type="text" name="passenger_passport" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="<?= __('passport_no') ?: 'Passport number' ?>">
                </div>
            </div>
        </div>

        <!-- Tours with Per-Item Pricing -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"><i class="fas fa-map text-indigo-500 mr-1"></i> <?= __('tours') ?: 'Tours' ?></h3>
                <button type="button" @click="openCatalog()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-semibold hover:bg-emerald-200 transition">
                    <i class="fas fa-search-dollar"></i> <?= __('browse_catalog') ?: 'Browse Catalog' ?>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500"><?= __('tour_name') ?: 'Tour Name' ?></th>
                            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500"><?= __('tour_date') ?: 'Date' ?></th>
                            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500"><?= __('duration') ?: 'Duration' ?></th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500"><?= __('adults') ?: 'Adults' ?></th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500"><?= __('children') ?: 'Children' ?></th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500"><?= __('infants') ?: 'Infants' ?></th>
                            <th class="px-2 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(tour, index) in tourItems" :key="index">
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="px-2 py-2">
                                    <input type="text" x-model="tour.name" placeholder="<?= __('tour_name') ?: 'Tour name' ?>" class="w-full min-w-[140px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="date" x-model="tour.date" class="w-full min-w-[130px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" x-model="tour.duration" placeholder="e.g. Full Day" class="w-full min-w-[100px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" x-model.number="tour.adults" min="0" class="w-full min-w-[60px] px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" x-model.number="tour.children" min="0" class="w-full min-w-[60px] px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" x-model.number="tour.infants" min="0" class="w-full min-w-[60px] px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <button type="button" @click="removeTour(index)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between mt-3">
                <button type="button" @click="addTour()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm hover:bg-indigo-100 transition">
                    <i class="fas fa-plus text-xs"></i> <?= __('add_tour') ?: 'Add Tour' ?>
                </button>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <?= __('total_pax') ?: 'Total PAX' ?>:
                    <span class="font-bold text-gray-700 dark:text-gray-200">
                        <span x-text="totalAdults"></span> <?= __('adults') ?: 'adults' ?>,
                        <span x-text="totalChildren"></span> <?= __('children') ?: 'children' ?>,
                        <span x-text="totalInfants"></span> <?= __('infants') ?: 'infants' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('tour-voucher') ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?: 'Cancel' ?></a>
            <button type="submit" :disabled="submitting" :class="{'opacity-50 cursor-not-allowed':submitting}" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><span x-text="submitting ? '<?= __('processing') ?: 'Saving…' ?>' : '<?= __('save') ?: 'Save & Generate PDF' ?>'"></span>
            </button>
        </div>
    </form>

    <!-- Catalog Picker Modal -->
    <div x-show="catalogOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="catalogOpen = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[70vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white"><i class="fas fa-search-dollar text-emerald-500 mr-2"></i><?= __('pricing_catalog') ?: 'Tour Catalog' ?></h3>
                <button type="button" @click="catalogOpen = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"><i class="fas fa-times"></i></button>
            </div>
            <input type="text" x-model="catalogQuery" @input.debounce.300ms="searchCatalog()" placeholder="<?= __('search') ?: 'Search tour pricing...' ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm mb-4">
            <div class="flex-1 overflow-y-auto space-y-2">
                <template x-for="svc in catalogResults" :key="svc.id">
                    <div @click="addFromCatalog(svc)" class="p-3 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/10 cursor-pointer transition">
                        <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="svc.name"></div>
                        <div class="text-xs text-gray-400" x-text="svc.description"></div>
                        <div class="mt-1 text-sm font-bold text-purple-600">
                            <?= __('adult') ?: 'Adult' ?>: <span x-text="parseFloat(svc.price_adult || svc.price || 0).toFixed(2)"></span>
                            <span x-show="parseFloat(svc.price_child) > 0" class="text-gray-400 ml-2"><?= __('child') ?: 'Child' ?>: <span x-text="parseFloat(svc.price_child).toFixed(2)"></span></span>
                            <span x-show="parseFloat(svc.price_infant) > 0" class="text-gray-400 ml-2"><?= __('infant') ?: 'Infant' ?>: <span x-text="parseFloat(svc.price_infant).toFixed(2)"></span></span>
                            <span class="text-xs text-gray-400 ml-1" x-text="svc.currency"></span>
                        </div>
                    </div>
                </template>
                <div x-show="catalogResults.length === 0" class="text-center py-6 text-gray-400"><?= __('no_results') ?: 'No tours found' ?></div>
            </div>
        </div>
    </div>
</div>

<script>
function tourVoucherForm() {
    return {
        tourItems: [],
        submitting: false,
        catalogOpen: false,
        catalogQuery: '',
        catalogResults: [],

        get totalAdults()   { return this.tourItems.reduce((s,r) => s + (+r.adults   || 0), 0); },
        get totalChildren() { return this.tourItems.reduce((s,r) => s + (+r.children || 0), 0); },
        get totalInfants()  { return this.tourItems.reduce((s,r) => s + (+r.infants  || 0), 0); },
        get tourItemsJson() {
            return JSON.stringify(this.tourItems.map(r => ({
                name:     r.name,
                date:     r.date,
                duration: r.duration,
                pax:      (+r.adults||0) + (+r.children||0) + (+r.infants||0),
                adults:   r.adults,
                children: r.children,
                infants:  r.infants,
            })));
        },

        addTour() {
            this.tourItems.push({name:'', date:'', duration:'', adults:1, children:0, infants:0});
        },
        removeTour(i) { this.tourItems.splice(i, 1); },

        openCatalog() { this.catalogOpen = true; this.searchCatalog(); },
        async searchCatalog() {
            try {
                const params = new URLSearchParams({ type: 'tour' });
                if (this.catalogQuery) params.set('q', this.catalogQuery);
                const res = await fetch('<?= url('api/services/search') ?>?' + params);
                this.catalogResults = await res.json();
            } catch(e) { this.catalogResults = []; }
        },
        addFromCatalog(svc) {
            this.tourItems.push({ name: svc.name, date: '', duration: '', adults: 1, children: 0, infants: 0 });
            this.catalogOpen = false;
        },
    };
}
function tourPartnerSearch() {
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
            document.getElementById('tour_company_id').value = r.id;
            const phone = document.querySelector('[name="customer_phone"]');
            if (phone && r.phone) phone.value = r.phone;
        }
    };
}
</script>
