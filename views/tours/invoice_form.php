<?php
/**
 * Tour Invoice Create Form — Enhanced with pricing catalog lookup
 * Searches tour services from catalog for price auto-fill
 */
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice text-purple-500 mr-2"></i>New Tour Invoice</h1>
        <p class="text-sm text-gray-500 mt-1">Create a tour invoice - search catalog for pricing</p>
    </div>
    <a href="<?= url('tour-invoice') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
     x-data="tourInvoiceForm()">
    <form method="POST" action="<?= url('tour-invoice/store') ?>" @submit.prevent="submitForm($el)" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Company Info -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-building text-purple-400 mr-1"></i> Company Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                    <input type="text" name="company_name" required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Company name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotel Name</label>
                    <input type="text" name="hotel_name" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Hotel name">
                </div>
            </div>
        </div>

        <!-- Tour Items -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider"><i class="fas fa-map-marked-alt text-purple-400 mr-1"></i> Tour Items</h3>
                <button type="button" @click="openCatalog()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold hover:bg-emerald-200 transition">
                    <i class="fas fa-search-dollar"></i> Browse Catalog
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Tour Name</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Date</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500">Pax</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Unit Price</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500">Total</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(tour, idx) in tours" :key="idx">
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="px-3 py-2"><input type="text" x-model="tour.name" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm" placeholder="Tour name"></td>
                                <td class="px-3 py-2"><input type="date" x-model="tour.date" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"></td>
                                <td class="px-3 py-2"><input type="number" x-model.number="tour.pax" @input="calcTourTotal(idx)" min="1" class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center"></td>
                                <td class="px-3 py-2"><input type="number" x-model.number="tour.price" @input="calcTourTotal(idx)" step="0.01" class="w-24 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-right"></td>
                                <td class="px-3 py-2 text-right font-bold text-gray-700 dark:text-gray-300" x-text="tour.total.toFixed(2)"></td>
                                <td class="px-3 py-2"><button type="button" @click="removeTour(idx)" class="text-red-400 hover:text-red-600 transition" x-show="tours.length > 1"><i class="fas fa-trash-alt"></i></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="addTour()" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-purple-600 hover:text-purple-700">
                <i class="fas fa-plus-circle"></i> Add Tour
            </button>
        </div>

        <!-- Pricing -->
        <div>
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-calculator text-purple-400 mr-1"></i> Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                    <select name="currency" x-model="currency" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR</option>
                        <option value="TRY">TRY</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Amount</label>
                    <div class="px-4 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <span class="text-2xl font-bold text-purple-700 dark:text-purple-300" x-text="grandTotal.toFixed(2)"></span>
                        <span class="text-sm text-purple-500 ml-1" x-text="currency"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('tour-invoice') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-1"></i> Create Invoice
            </button>
        </div>
    </form>

    <!-- Catalog Picker Modal -->
    <div x-show="catalogOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="catalogOpen = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4 max-h-[70vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white"><i class="fas fa-search-dollar text-emerald-500 mr-2"></i>Tour Catalog</h3>
                <button type="button" @click="catalogOpen = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"><i class="fas fa-times"></i></button>
            </div>
            <input type="text" x-model="catalogQuery" @input.debounce.300ms="searchCatalog()" placeholder="Search tour pricing..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm mb-4">
            <div class="flex-1 overflow-y-auto space-y-2">
                <template x-for="svc in catalogResults" :key="svc.id">
                    <div @click="addFromCatalog(svc)" class="p-3 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/10 cursor-pointer transition">
                        <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="svc.name"></div>
                        <div class="text-xs text-gray-400" x-text="svc.description"></div>
                        <div class="mt-1 text-sm font-bold text-purple-600">
                            Adult: <span x-text="parseFloat(svc.price_adult || svc.price).toFixed(2)"></span>
                            <span x-show="parseFloat(svc.price_child) > 0" class="text-gray-400 ml-2">Child: <span x-text="parseFloat(svc.price_child).toFixed(2)"></span></span>
                            <span class="text-xs text-gray-400 ml-1" x-text="svc.currency"></span>
                        </div>
                    </div>
                </template>
                <div x-show="catalogResults.length === 0" class="text-center py-6 text-gray-400">No tours found</div>
            </div>
        </div>
    </div>
</div>

<script>
function tourInvoiceForm() {
    return {
        tours: [{ name: '', date: '', pax: 1, price: 0, total: 0 }],
        currency: 'USD',
        grandTotal: 0,
        catalogOpen: false,
        catalogQuery: '',
        catalogResults: [],

        addTour() { this.tours.push({ name: '', date: '', pax: 1, price: 0, total: 0 }); },
        removeTour(i) { if (this.tours.length > 1) { this.tours.splice(i, 1); this.recalcTotal(); } },

        calcTourTotal(idx) {
            const t = this.tours[idx];
            t.total = (t.pax || 1) * (t.price || 0);
            this.recalcTotal();
        },

        recalcTotal() {
            this.grandTotal = this.tours.reduce((s, t) => s + (t.total || 0), 0);
        },

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
            const price = parseFloat(svc.price_adult || svc.price) || 0;
            this.tours.push({
                name: svc.name,
                date: '',
                pax: 1,
                price: price,
                total: price
            });
            this.catalogOpen = false;
            this.recalcTotal();
        },

        submitForm(el) {
            this.recalcTotal();
            const fd = new FormData(el);
            fd.set('tours', JSON.stringify(this.tours));
            fd.set('total_price', this.grandTotal);
            fetch(el.action, { method: 'POST', body: fd }).then(r => {
                if(r.redirected) window.location = r.url;
                else r.text().then(t => { document.open(); document.write(t); document.close(); });
            });
        }
    };
}
</script>
