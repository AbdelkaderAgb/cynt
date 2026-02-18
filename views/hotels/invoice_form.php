<?php
/**
 * Hotel Invoice Create Form — Enhanced with pricing catalog integration
 * Looks up hotel room prices and seasonal rates from the catalog
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice text-teal-500 mr-2"></i>New Hotel Invoice</h1>
        <p class="text-sm text-gray-500 mt-1">Create a hotel invoice - prices loaded from hotel profiles and seasonal rates</p>
    </div>
    <a href="<?= url('hotel-invoice') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="hotelInvoiceForm()">
    <form method="POST" action="<?= url('hotel-invoice/store') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Invoice Details -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i> Invoice Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Company Name *</label>
                    <input type="text" name="company_name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="Enter company name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Hotel Name *</label>
                    <input type="text" name="hotel_name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="Enter hotel name">
                </div>
            </div>
        </div>

        <!-- Rooms (Dynamic) -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"><i class="fas fa-bed text-indigo-500 mr-1"></i> Rooms</h3>
                <button type="button" @click="lookupPricing()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold hover:bg-emerald-200 transition">
                    <i class="fas fa-search-dollar"></i> Lookup Catalog Prices
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="pb-2 pr-3">Room Type</th>
                            <th class="pb-2 pr-3">Nights</th>
                            <th class="pb-2 pr-3">Rooms</th>
                            <th class="pb-2 pr-3">Price/Night</th>
                            <th class="pb-2 pr-3 text-right">Subtotal</th>
                            <th class="pb-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(room, index) in rooms" :key="index">
                            <tr>
                                <td class="pb-2 pr-3">
                                    <select x-model="room.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                        <option value="SNG">Single (SNG)</option>
                                        <option value="DBL">Double (DBL)</option>
                                        <option value="TRP">Triple (TRP)</option>
                                        <option value="QUAD">Quad (QUAD)</option>
                                        <option value="SUIT">Suite (SUIT)</option>
                                        <option value="VILLA">Villa (VILLA)</option>
                                        <option value="STUDIO">Studio (STUDIO)</option>
                                        <option value="APART">Apart (APART)</option>
                                    </select>
                                </td>
                                <td class="pb-2 pr-3">
                                    <input type="number" x-model.number="room.nights" @input="calcRoomTotal(index)" min="1" value="1" class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                </td>
                                <td class="pb-2 pr-3">
                                    <input type="number" x-model.number="room.count" @input="calcRoomTotal(index)" min="1" value="1" class="w-16 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                </td>
                                <td class="pb-2 pr-3">
                                    <input type="number" x-model.number="room.price" @input="calcRoomTotal(index)" step="0.01" min="0" class="w-28 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="0.00">
                                </td>
                                <td class="pb-2 pr-3 text-right font-bold text-gray-700 dark:text-gray-300">
                                    <span x-text="room.subtotal.toFixed(2)"></span>
                                </td>
                                <td class="pb-2">
                                    <button type="button" @click="rooms.splice(index, 1); recalcTotal()" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="rooms.push({type:'DBL', price: 0, nights: 1, count: 1, subtotal: 0})" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-plus text-xs"></i> Add Room
            </button>
            <input type="hidden" name="rooms" :value="JSON.stringify(rooms)">
        </div>

        <!-- Currency & Total -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Currency</label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Amount</label>
                <div class="px-4 py-3 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl border border-teal-200 dark:border-teal-800">
                    <span class="text-2xl font-bold text-teal-700 dark:text-teal-300" x-text="total.toFixed(2)"></span>
                    <span class="text-sm text-teal-500 ml-1">per night calculation</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('hotel-invoice') ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-file-invoice mr-2"></i>Generate Invoice
            </button>
        </div>
    </form>
</div>

<script>
function hotelInvoiceForm() {
    return {
        rooms: [{type: 'DBL', price: 0, nights: 1, count: 1, subtotal: 0}],
        total: 0,

        calcRoomTotal(idx) {
            const r = this.rooms[idx];
            r.subtotal = (r.price || 0) * (r.nights || 1) * (r.count || 1);
            this.recalcTotal();
        },

        recalcTotal() {
            this.total = this.rooms.reduce((sum, r) => sum + (r.subtotal || 0), 0);
        },

        async lookupPricing() {
            // Search hotel services in the catalog
            const hotelName = document.querySelector('[name="hotel_name"]').value;
            if (!hotelName) { alert('Enter a hotel name first'); return; }
            try {
                const res = await fetch('<?= url('api/services/search') ?>?type=hotel&q=' + encodeURIComponent(hotelName));
                const services = await res.json();
                if (services.length > 0) {
                    alert('Found ' + services.length + ' matching hotel services in catalog. Prices will be pre-filled where possible.');
                    // Auto-fill first matching price
                    if (this.rooms.length > 0 && services[0].price > 0) {
                        this.rooms[0].price = parseFloat(services[0].price);
                        this.calcRoomTotal(0);
                    }
                } else {
                    alert('No hotel pricing found in catalog for "' + hotelName + '". You can add it via Services & Pricing.');
                }
            } catch(e) {
                alert('Could not lookup pricing: ' + e.message);
            }
        }
    };
}
</script>
