<?php
/**
 * Tour Voucher Create Form — Matching reference system
 * Fields: Voucher No (auto), Company Name, Customer Phone, Hotel Name,
 *         Adult/Child/Infant, dynamic Tours (Name+Date+Duration), dynamic Customers (Name)
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-map-marked-alt text-purple-500 mr-2"></i>New Tour Voucher</h1>
        <p class="text-sm text-gray-500 mt-1">Create a tour voucher matching the reference system format</p>
    </div>
    <a href="<?= url('tour-voucher') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="tourVoucherForm()">
    <form method="POST" action="<?= url('tour-voucher/store') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Company & Contact -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i> Company & Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Voucher No</label>
                    <input type="text" name="voucher_no_display" disabled value="Auto-generated" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-400">
                </div>
                <div class="relative" x-data="tourPartnerSearch()" @click.outside="open = false">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Company Name *</label>
                    <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                           required autocomplete="off" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Search or enter company name">
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
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Customer Phone</label>
                    <input type="text" name="customer_phone" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="+90 555 123 4567">
                </div>
                <div x-data="tourHotelCascade()" x-init="init()">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-hotel text-teal-400 mr-1"></i>Hotel Name</label>
                    <input type="hidden" name="hotel_name" :value="selectedHotelName">
                    <div class="grid grid-cols-3 gap-2 mt-1">
                        <select x-model="selectedCountry" @change="onCountryChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                            <option value="">Country</option>
                            <template x-for="c in countries" :key="c"><option :value="c" x-text="c"></option></template>
                        </select>
                        <select x-model="selectedCity" @change="onCityChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                            <option value="">City</option>
                            <template x-for="ci in cities" :key="ci"><option :value="ci" x-text="ci"></option></template>
                        </select>
                        <select x-model="selectedHotelId" @change="onHotelChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                            <option value="">Hotel</option>
                            <template x-for="h in filteredHotels" :key="h.id"><option :value="h.id" x-text="h.name + ' ' + '★'.repeat(h.stars||0)"></option></template>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guest & Passport -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-passport text-amber-500 mr-1"></i> Guest Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                    <input type="text" name="guest_name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Main guest name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passenger Passport' ?></label>
                    <input type="text" name="passenger_passport" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Passport number">
                </div>
            </div>
        </div>

        <!-- Location & Details -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i> Location & Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('city') ?: 'City' ?></label>
                    <input type="text" name="city" id="tour_city_field" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Auto-filled from hotel" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('country') ?: 'Country' ?></label>
                    <input type="text" name="country" id="tour_country_field" value="Turkey" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Auto-filled from hotel" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('address') ?: 'Address' ?></label>
                    <input type="text" name="address" id="tour_address_field" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Auto-filled from hotel">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-map-pin text-purple-500 mr-1"></i><?= __('meeting_point') ?: 'Meeting Point' ?></label>
                    <input type="text" name="meeting_point" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. Hotel Lobby">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('meeting_point_address') ?: 'Meeting Point Address' ?></label>
                    <input type="text" name="meeting_point_address" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Full address of meeting point">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-clock text-blue-500 mr-1"></i><?= __('duration') ?: 'Duration (hours)' ?></label>
                    <input type="number" name="duration_hours" step="0.5" min="0" value="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. 6">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-check-circle text-green-500 mr-1"></i><?= __('includes') ?: 'Includes' ?></label>
                    <textarea name="includes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Transport, lunch, guide, entrance fees..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><i class="fas fa-times-circle text-red-500 mr-1"></i><?= __('excludes') ?: 'Excludes' ?></label>
                    <textarea name="excludes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Personal expenses, tips, drinks..."></textarea>
                </div>
            </div>
        </div>

        <!-- Pax Counts -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-users text-teal-500 mr-1"></i> Pax Counts</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Adult</label>
                    <input type="number" name="adults" x-model.number="adults" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Child</label>
                    <input type="number" name="children" x-model.number="children" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Infant</label>
                    <input type="number" name="infants" x-model.number="infants" value="0" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                </div>
            </div>
            <!-- Pricing removed — prices are managed via invoices/receipts only -->
        </div>

        <!-- Tours (Dynamic Rows) -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-map text-indigo-500 mr-1"></i> Tours</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="pb-2 pr-4">Tour Name</th>
                            <th class="pb-2 pr-4">Tour Date</th>
                            <th class="pb-2 pr-4">Duration</th>
                            <th class="pb-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(tour, index) in tours" :key="index">
                            <tr>
                                <td class="pb-2 pr-4">
                                    <input type="text" x-model="tour.name" placeholder="Tour name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500">
                                </td>
                                <td class="pb-2 pr-4">
                                    <input type="date" x-model="tour.date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </td>
                                <td class="pb-2 pr-4">
                                    <input type="text" x-model="tour.duration" placeholder="e.g. Full Day" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </td>
                                <td class="pb-2">
                                    <button type="button" @click="tours.splice(index, 1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="tours.push({name:'', date:'', duration:''})" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg text-sm hover:bg-indigo-100 transition">
                <i class="fas fa-plus text-xs"></i> Add Tour
            </button>
            <input type="hidden" name="tour_items" :value="JSON.stringify(tours)">
        </div>

        <!-- Customers (Dynamic Rows) -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-user-friends text-emerald-500 mr-1"></i> Customers</h3>
            <div class="space-y-2">
                <template x-for="(customer, index) in customers" :key="index">
                    <div class="flex gap-2 items-center">
                        <input type="text" x-model="customer.name" placeholder="Customer name" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500">
                        <button type="button" @click="customers.splice(index, 1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="customers.push({name:''})" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-plus text-xs"></i> Add Customer
            </button>
            <input type="hidden" name="customers" :value="JSON.stringify(customers)">
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('tour-voucher') ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i>Save & Generate PDF
            </button>
        </div>
    </form>
</div>

<script>
function tourVoucherForm() {
    return {
        tours: [{name: '', date: '', duration: ''}],
        customers: [{name: ''}]
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
            // Auto-fill address from partner data
            const addr = r.address || ((r.city || '') + (r.country ? ', ' + r.country : ''));
            const hotelField = document.querySelector('[name="hotel_name"]');
            // Don't overwrite hotel_name, just fill phone
        }
    };
}
function tourHotelCascade() {
    return {
        allHotels: [], countries: [], cities: [], filteredHotels: [],
        selectedCountry: '', selectedCity: '', selectedHotelId: '', selectedHotelName: '',
        async init() {
            try {
                const res = await fetch('<?= url('api/hotels/list') ?>');
                this.allHotels = await res.json();
                this.countries = [...new Set(this.allHotels.map(h => h.country).filter(Boolean))].sort();
            } catch(e) { this.allHotels = []; }
        },
        onCountryChange() {
            this.cities = [...new Set(this.allHotels.filter(h => h.country === this.selectedCountry).map(h => h.city).filter(Boolean))].sort();
            this.selectedCity = ''; this.filteredHotels = []; this.selectedHotelId = ''; this.selectedHotelName = '';
            document.getElementById('tour_city_field').value = '';
            document.getElementById('tour_country_field').value = this.selectedCountry;
        },
        onCityChange() {
            this.filteredHotels = this.allHotels.filter(h => h.country === this.selectedCountry && h.city === this.selectedCity);
            this.selectedHotelId = ''; this.selectedHotelName = '';
            document.getElementById('tour_city_field').value = this.selectedCity;
        },
        onHotelChange() {
            const h = this.allHotels.find(x => x.id == this.selectedHotelId);
            if (h) {
                this.selectedHotelName = h.name;
                document.getElementById('tour_city_field').value = h.city || '';
                document.getElementById('tour_country_field').value = h.country || '';
                document.getElementById('tour_address_field').value = h.address || '';
            } else {
                this.selectedHotelName = '';
            }
        }
    };
}
</script>
