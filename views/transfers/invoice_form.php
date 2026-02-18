<?php
/**
 * Transfer Invoice Create Form — Enhanced with pricing catalog lookup
 * Searches transfer services from catalog for price auto-fill
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice-dollar text-blue-500 mr-2"></i>New Transfer Invoice</h1>
        <p class="text-sm text-gray-500 mt-1">Create a transfer invoice - search catalog for pricing</p>
    </div>
    <a href="<?= url('transfer-invoice') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="transferInvoiceForm()">
    <form method="POST" action="<?= url('transfer-invoice/store') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Company & Route -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-route text-emerald-500 mr-1"></i> Transfer Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Company Name *</label>
                    <input type="text" name="company_name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Enter company name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Hotel</label>
                    <input type="text" name="hotel_name" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Hotel name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Starting Point *</label>
                    <input type="text" name="starting_point" x-model="startingPoint" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. Istanbul Airport">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Destination *</label>
                    <input type="text" name="destination" x-model="destination" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. Hotel">
                </div>
            </div>
        </div>

        <!-- Date & Transfer Info -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"><i class="fas fa-calendar text-purple-500 mr-1"></i> Schedule & Pricing</h3>
                <button type="button" @click="lookupCatalog()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold hover:bg-emerald-200 transition">
                    <i class="fas fa-search-dollar"></i> Lookup Catalog Price
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pickup Date *</label>
                    <input type="date" name="pickup_date" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Transfer Type</label>
                    <select name="transfer_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                        <option value="one_way">One Way</option>
                        <option value="round_trip">Round Trip</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Pax</label>
                    <input type="number" name="total_pax" x-model.number="totalPax" @input="recalcTotal()" value="1" min="1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Price</label>
                    <div class="flex">
                        <input type="number" name="total_price" x-model.number="totalPrice" step="0.01" class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-l-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                        <select name="currency" class="px-3 py-2.5 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-xl bg-gray-50 dark:bg-gray-600 text-sm">
                            <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Catalog match info -->
            <div x-show="catalogMatch" class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm">
                <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                Price loaded from catalog: <strong x-text="catalogMatch"></strong>
            </div>
        </div>

        <!-- Passengers -->
        <div>
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Passengers</label>
            <textarea name="passengers" rows="4" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Enter passenger names separated by new lines"></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('transfer-invoice') ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-file-invoice mr-2"></i>Generate Invoice
            </button>
        </div>
    </form>
</div>

<script>
function transferInvoiceForm() {
    return {
        startingPoint: '',
        destination: '',
        totalPax: 1,
        totalPrice: 0,
        catalogMatch: '',

        recalcTotal() {
            // If pricing was from catalog per_person, recalc
        },

        async lookupCatalog() {
            const query = (this.startingPoint + ' ' + this.destination).trim();
            if (!query) { alert('Enter starting point or destination first'); return; }
            try {
                const res = await fetch('<?= url('api/services/search') ?>?type=transfer&q=' + encodeURIComponent(query));
                const services = await res.json();
                if (services.length > 0) {
                    const svc = services[0];
                    const price = parseFloat(svc.price) || 0;
                    if (svc.unit === 'per_person') {
                        this.totalPrice = price * this.totalPax;
                    } else {
                        this.totalPrice = price;
                    }
                    this.catalogMatch = svc.name + ' - ' + price.toFixed(2) + ' ' + svc.currency + ' (' + (svc.unit || 'flat') + ')';
                } else {
                    this.catalogMatch = '';
                    alert('No transfer pricing found in catalog. You can add it via Services & Pricing.');
                }
            } catch(e) {
                alert('Could not lookup pricing: ' + e.message);
            }
        }
    };
}
</script>
