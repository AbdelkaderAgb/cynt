<?php
/**
 * Hotel Invoice Create Form — Matching reference system
 * Fields: Company Name, Hotel Name, dynamic Rooms (Room Type + Price Per Night)
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-invoice text-teal-500 mr-2"></i>New Hotel Invoice</h1>
        <p class="text-sm text-gray-500 mt-1">Create a hotel invoice matching the reference system format</p>
    </div>
    <a href="<?= url('hotel-invoice') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="hotelInvoiceForm()">
    <form method="POST" action="<?= url('hotel-invoice/store') ?>" class="space-y-6">

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
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-bed text-indigo-500 mr-1"></i> Rooms</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="pb-2 pr-4">Room Type</th>
                            <th class="pb-2 pr-4">Price Per Night</th>
                            <th class="pb-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(room, index) in rooms" :key="index">
                            <tr>
                                <td class="pb-2 pr-4">
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
                                <td class="pb-2 pr-4">
                                    <input type="number" x-model="room.price" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500" placeholder="0.00">
                                </td>
                                <td class="pb-2">
                                    <button type="button" @click="rooms.splice(index, 1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="rooms.push({type:'DBL', price: 0})" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fas fa-plus text-xs"></i> Add Room
            </button>
            <input type="hidden" name="rooms" :value="JSON.stringify(rooms)">
        </div>

        <!-- Currency -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Currency</label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Calculated Total</label>
                <div class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-sm font-bold text-emerald-600" x-text="calcTotal()">0.00</div>
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
        rooms: [{type: 'DBL', price: 0}],
        calcTotal() {
            return this.rooms.reduce((sum, r) => sum + (parseFloat(r.price) || 0), 0).toFixed(2);
        }
    };
}
</script>
