<?php
/**
 * Invoice Form View — Create/Edit
 * Enhanced with service picker, line-item builder, and auto-calculation
 */
$inv = $invoice;
$existingItems = $invoiceItems ?? [];
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 mt-1">Select services from the pricing catalog or add custom line items</p>
</div>

<form method="POST" action="<?= url('invoices/store') ?>" class="space-y-6" x-data="invoiceBuilder()" @submit="prepareSubmit()">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $inv['id'] ?>"><?php endif; ?>
    <input type="hidden" name="company_id" id="inv_company_id" value="<?= e($inv['company_id'] ?? '') ?>">
    <input type="hidden" name="items_json" :value="JSON.stringify(items)">
    <input type="hidden" name="subtotal" :value="subtotal.toFixed(2)">
    <input type="hidden" name="tax_amount" :value="taxAmount.toFixed(2)">
    <input type="hidden" name="total_amount" :value="grandTotal.toFixed(2)">

    <!-- Invoice Details -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-file-invoice text-emerald-500 mr-2"></i><?= __('details') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Company with autocomplete -->
            <div class="relative lg:col-span-2" x-data="invPartnerSearch()" @click.outside="open = false">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('company_name') ?> *</label>
                <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                       value="<?= e($inv['company_name'] ?? '') ?>" required autocomplete="off"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1"><?= __('search_partner') ?></p>

                <div x-show="open && results.length > 0" x-transition
                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="selectPartner(r)" class="px-4 py-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.city ? ' - ' + r.city : '')"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('date') ?></label>
                <input type="date" name="invoice_date" value="<?= $inv['invoice_date'] ?? date('Y-m-d') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('due_date') ?></label>
                <input type="date" name="due_date" value="<?= $inv['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('currency') ?></label>
                <select name="currency" x-model="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($inv['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['draft'=>__('draft'),'sent'=>__('sent'),'paid'=>__('paid'),'overdue'=>__('overdue')] as $k=>$lbl): ?>
                    <option value="<?= $k ?>" <?= ($inv['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('payment_method') ?></label>
                <select name="payment_method" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <option value="">-- <?= __('select') ?> --</option>
                    <?php foreach (['cash'=>__('cash'),'card'=>__('credit_card'),'transfer'=>__('bank_transfer'),'check'=>__('check')] as $k=>$lbl): ?>
                    <option value="<?= $k ?>" <?= ($inv['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Line Items -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200"><i class="fas fa-list text-blue-500 mr-2"></i>Line Items</h3>
            <div class="flex gap-2">
                <button type="button" @click="openServicePicker()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition">
                    <i class="fas fa-search-dollar"></i> Add from Catalog
                </button>
                <button type="button" @click="addManualItem()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition">
                    <i class="fas fa-plus"></i> Add Manual
                </button>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm" x-show="items.length > 0">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 w-2/5">Description</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 w-16">Qty</th>
                        <th class="text-right px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 w-28">Unit Price</th>
                        <th class="text-right px-3 py-2 font-semibold text-gray-600 dark:text-gray-300 w-28">Total</th>
                        <th class="text-center px-3 py-2 w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="px-3 py-2">
                                <input type="text" x-model="item.description" class="w-full px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm" placeholder="Service description">
                                <div x-show="item.service_id" class="text-[10px] text-emerald-500 mt-0.5">
                                    <i class="fas fa-link"></i> <span x-text="item.item_type"></span> #<span x-text="item.service_id"></span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="number" x-model.number="item.quantity" @input="calcLineTotal(idx)" min="1" class="w-16 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                            </td>
                            <td class="px-3 py-2 text-right">
                                <input type="number" x-model.number="item.unit_price" @input="calcLineTotal(idx)" step="0.01" min="0" class="w-28 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-right">
                            </td>
                            <td class="px-3 py-2 text-right font-bold text-gray-800 dark:text-gray-200">
                                <span x-text="item.total_price.toFixed(2)"></span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeItem(idx)" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-show="items.length === 0" class="py-8 text-center text-gray-400">
            <i class="fas fa-receipt text-3xl mb-2 opacity-30"></i>
            <p class="text-sm">No line items yet. Add from the pricing catalog or create a manual entry.</p>
        </div>

        <!-- Totals -->
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4" x-show="items.length > 0">
            <div class="flex justify-end">
                <div class="w-full max-w-sm space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500"><?= __('subtotal') ?></span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="subtotal.toFixed(2) + ' ' + currency"></span>
                    </div>
                    <div class="flex justify-between text-sm items-center gap-2">
                        <span class="text-gray-500"><?= __('tax') ?> (%)</span>
                        <div class="flex items-center gap-2">
                            <input type="number" name="tax_rate" x-model.number="taxRate" @input="recalcTotals()" step="0.01" min="0" max="100" class="w-20 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-right">
                            <span class="text-gray-500 w-24 text-right" x-text="taxAmount.toFixed(2)"></span>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm items-center gap-2">
                        <span class="text-gray-500"><?= __('discount') ?></span>
                        <input type="number" name="discount" x-model.number="discount" @input="recalcTotals()" step="0.01" min="0" class="w-24 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-right">
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-gray-300 dark:border-gray-600 pt-2 mt-2">
                        <span class="text-gray-700 dark:text-gray-200"><?= __('total_amount') ?></span>
                        <span class="text-emerald-600 text-lg" x-text="grandTotal.toFixed(2) + ' ' + currency"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('notes') ?></label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm"><?= e($inv['notes'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?= __('terms_conditions') ?></label>
                <textarea name="terms" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm"><?= e($inv['terms'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?>
        </button>
        <a href="<?= url('invoices') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>

    <!-- Service Picker Modal -->
    <div x-show="pickerOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="pickerOpen = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-2xl mx-4 max-h-[80vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white"><i class="fas fa-search-dollar text-emerald-500 mr-2"></i>Select from Pricing Catalog</h3>
                <button type="button" @click="pickerOpen = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"><i class="fas fa-times"></i></button>
            </div>

            <!-- Type Tabs -->
            <div class="flex gap-2 mb-4">
                <button type="button" @click="pickerType = ''" :class="pickerType === '' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition">All</button>
                <button type="button" @click="pickerType = 'tour'" :class="pickerType === 'tour' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition"><i class="fas fa-map-marked-alt mr-1"></i>Tours</button>
                <button type="button" @click="pickerType = 'transfer'" :class="pickerType === 'transfer' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition"><i class="fas fa-shuttle-van mr-1"></i>Transfers</button>
                <button type="button" @click="pickerType = 'hotel'" :class="pickerType === 'hotel' ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600'" class="px-3 py-1.5 rounded-lg text-sm font-semibold transition"><i class="fas fa-hotel mr-1"></i>Hotels</button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input type="text" x-model="pickerQuery" @input.debounce.300ms="searchServices()" placeholder="Search services..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Results -->
            <div class="flex-1 overflow-y-auto space-y-2 min-h-[200px]">
                <template x-if="pickerLoading">
                    <div class="text-center py-8 text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>
                </template>
                <template x-if="!pickerLoading && pickerResults.length === 0">
                    <div class="text-center py-8 text-gray-400"><i class="fas fa-search mr-2"></i>No services found. Try a different search.</div>
                </template>
                <template x-for="svc in pickerResults" :key="svc.id">
                    <div @click="addServiceItem(svc)" class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 cursor-pointer transition flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                      :class="{
                                          'bg-purple-100 text-purple-700': svc.service_type === 'tour',
                                          'bg-blue-100 text-blue-700': svc.service_type === 'transfer',
                                          'bg-teal-100 text-teal-700': svc.service_type === 'hotel',
                                          'bg-gray-100 text-gray-700': svc.service_type === 'other'
                                      }" x-text="svc.service_type"></span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="svc.name"></span>
                            </div>
                            <div class="text-xs text-gray-400 mt-1" x-text="svc.description"></div>
                            <div x-show="svc.destination" class="text-xs text-purple-400 mt-0.5"><i class="fas fa-map-pin mr-1"></i><span x-text="svc.destination"></span></div>
                        </div>
                        <div class="text-right ml-4 flex-shrink-0">
                            <div class="font-bold text-emerald-600" x-text="(parseFloat(svc.price_adult || svc.price) || 0).toFixed(2) + ' ' + svc.currency"></div>
                            <div x-show="svc.service_type === 'tour' && parseFloat(svc.price_child) > 0" class="text-xs text-gray-400">
                                Child: <span x-text="parseFloat(svc.price_child).toFixed(2)"></span>
                            </div>
                            <div class="text-[10px] text-gray-400" x-text="svc.unit || 'flat'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</form>

<script>
function invoiceBuilder() {
    return {
        items: <?= json_encode(array_map(function($item) {
            return [
                'description' => $item['description'] ?? '',
                'quantity' => (int)($item['quantity'] ?? 1),
                'unit_price' => (float)($item['unit_price'] ?? 0),
                'total_price' => (float)($item['total_price'] ?? 0),
                'item_type' => $item['item_type'] ?? 'other',
                'item_id' => (int)($item['item_id'] ?? 0),
                'service_id' => (int)($item['service_id'] ?? 0),
                'unit_type' => $item['unit_type'] ?? 'flat',
            ];
        }, $existingItems)) ?>,
        currency: '<?= e($inv['currency'] ?? 'USD') ?>',
        taxRate: <?= (float)($inv['tax_rate'] ?? 0) ?>,
        discount: <?= (float)($inv['discount'] ?? 0) ?>,
        subtotal: 0,
        taxAmount: 0,
        grandTotal: 0,
        pickerOpen: false,
        pickerType: '',
        pickerQuery: '',
        pickerResults: [],
        pickerLoading: false,

        init() {
            this.recalcTotals();
            // Auto-search on picker type change
            this.$watch('pickerType', () => { if (this.pickerOpen) this.searchServices(); });
        },

        addManualItem() {
            this.items.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                total_price: 0,
                item_type: 'other',
                item_id: 0,
                service_id: 0,
                unit_type: 'flat',
            });
        },

        addServiceItem(svc) {
            const price = parseFloat(svc.price_adult || svc.price) || 0;
            this.items.push({
                description: svc.name + (svc.description ? ' - ' + svc.description : ''),
                quantity: 1,
                unit_price: price,
                total_price: price,
                item_type: svc.service_type || 'service',
                item_id: 0,
                service_id: parseInt(svc.id) || 0,
                unit_type: svc.unit || 'flat',
            });
            this.pickerOpen = false;
            this.recalcTotals();
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.recalcTotals();
        },

        calcLineTotal(idx) {
            const item = this.items[idx];
            item.total_price = (item.quantity || 1) * (item.unit_price || 0);
            this.recalcTotals();
        },

        recalcTotals() {
            this.subtotal = this.items.reduce((sum, item) => sum + (item.total_price || 0), 0);
            this.taxAmount = this.taxRate > 0 ? this.subtotal * (this.taxRate / 100) : 0;
            this.grandTotal = Math.max(0, this.subtotal + this.taxAmount - this.discount);
        },

        openServicePicker() {
            this.pickerOpen = true;
            this.pickerQuery = '';
            this.searchServices();
        },

        async searchServices() {
            this.pickerLoading = true;
            try {
                const params = new URLSearchParams();
                if (this.pickerType) params.set('type', this.pickerType);
                if (this.pickerQuery) params.set('q', this.pickerQuery);
                const res = await fetch('<?= url('api/services/search') ?>?' + params.toString());
                this.pickerResults = await res.json();
            } catch(e) {
                this.pickerResults = [];
            }
            this.pickerLoading = false;
        },

        prepareSubmit() {
            this.recalcTotals();
        }
    };
}

function invPartnerSearch() {
    return {
        query: '<?= e($inv['company_name'] ?? '') ?>',
        results: [],
        open: false,
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
            document.getElementById('inv_company_id').value = r.id;
        }
    };
}
</script>
