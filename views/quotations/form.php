<?php
/**
 * Quotation Create/Edit Form — Day-by-day builder
 */
$isEdit = !empty($quotation);
$q = $quotation ?? [];
$existingItems = json_encode($items ?: [], JSON_UNESCAPED_UNICODE);
?>

<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-file-alt mr-2 text-orange-500"></i>
                <?= $isEdit ? (__('edit_quotation') ?: 'Edit Quotation') : (__('new_quotation') ?: 'New Quotation') ?>
            </h1>
            <?php if ($isEdit): ?>
            <p class="text-sm text-gray-500 mt-1"><?= e($q['quote_number']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= url('quotations') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i> <?= __('back') ?: 'Back' ?>
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? url('quotations/update') : url('quotations/store') ?>" id="quotationForm" class="space-y-6">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
        <?php endif; ?>
        <input type="hidden" name="items_json" id="itemsJsonField" value='<?= e($existingItems) ?>'>

        <!-- Client & Partner -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-user mr-2 text-orange-400"></i><?= __('client_info') ?: 'Client Information' ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('client_name') ?: 'Client Name' ?> *</label>
                    <input type="text" name="client_name" value="<?= e($q['client_name'] ?? '') ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('email') ?: 'Email' ?></label>
                    <input type="email" name="client_email" value="<?= e($q['client_email'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('phone') ?: 'Phone' ?></label>
                    <input type="text" name="client_phone" value="<?= e($q['client_phone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('partner') ?: 'Partner / Agency' ?></label>
                    <select name="partner_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value=""><?= __('none') ?: '— None —' ?></option>
                        <?php foreach ($partners as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ((int)($q['partner_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isEdit): ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?: 'Status' ?></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <?php foreach (['draft','sent','accepted','rejected','expired'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($q['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Travel Details -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-plane mr-2 text-blue-400"></i><?= __('travel_details') ?: 'Travel Details' ?></h3>
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('from') ?: 'From' ?></label>
                    <input type="date" name="travel_dates_from" value="<?= e($q['travel_dates_from'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('to') ?: 'To' ?></label>
                    <input type="date" name="travel_dates_to" value="<?= e($q['travel_dates_to'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('adults') ?: 'Adults' ?></label>
                    <input type="number" name="adults" value="<?= (int)($q['adults'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('children') ?: 'Children' ?></label>
                    <input type="number" name="children" value="<?= (int)($q['children'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('infants') ?: 'Infants' ?></label>
                    <input type="number" name="infants" value="<?= (int)($q['infants'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('currency') ?: 'Currency' ?></label>
                    <select name="currency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <?php foreach (['USD','EUR','TRY','GBP'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($q['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Day-by-Day Items Builder -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="quotationBuilder(<?= e($existingItems) ?>)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><i class="fas fa-list-ol mr-2 text-green-400"></i><?= __('itinerary_items') ?: 'Itinerary Items' ?></h3>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-100 transition">
                    <i class="fas fa-plus"></i> <?= __('add_item') ?: 'Add Item' ?>
                </button>
            </div>

            <template x-if="items.length === 0">
                <p class="text-center text-gray-400 py-8"><i class="fas fa-inbox text-3xl mb-2 block"></i><?= __('no_items') ?: 'No items yet. Click "Add Item" to start building the itinerary.' ?></p>
            </template>

            <template x-for="(item, idx) in items" :key="idx">
                <div class="border border-gray-200 dark:border-gray-600 rounded-xl p-4 mb-3 bg-gray-50 dark:bg-gray-700/30">
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-1">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Day</label>
                            <input type="number" x-model.number="item.day_number" min="1" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Type</label>
                            <select x-model="item.item_type" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <option value="hotel">Hotel</option>
                                <option value="tour">Tour</option>
                                <option value="transfer">Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Name</label>
                            <input type="text" x-model="item.item_name" placeholder="Service name..." class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Qty</label>
                            <input type="number" x-model.number="item.quantity" min="1" @change="calcItemTotal(idx)" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Unit Price</label>
                            <input type="number" x-model.number="item.unit_price" min="0" step="0.01" @change="calcItemTotal(idx)" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-right">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-400 mb-1">Total</label>
                            <div class="flex items-center gap-1">
                                <input type="text" :value="item.total_price.toFixed(2)" readonly class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-sm text-right font-semibold">
                                <button type="button" @click="removeItem(idx)" class="p-1.5 text-red-400 hover:text-red-600 transition flex-shrink-0"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <input type="text" x-model="item.description" placeholder="Description / details..." class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs text-gray-500">
                        </div>
                    </div>
                </div>
            </template>

            <!-- Totals -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2 max-w-xs ml-auto text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold" x-text="subtotal().toFixed(2)"></span></div>
            </div>
        </div>

        <!-- Discount, Tax, Valid Until -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('discount') ?: 'Discount %' ?></label>
                    <input type="number" name="discount_percent" value="<?= (float)($q['discount_percent'] ?? 0) ?>" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('tax') ?: 'Tax %' ?></label>
                    <input type="number" name="tax_percent" value="<?= (float)($q['tax_percent'] ?? 0) ?>" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('valid_until') ?: 'Valid Until' ?></label>
                    <input type="date" name="valid_until" value="<?= e($q['valid_until'] ?? date('Y-m-d', strtotime('+14 days'))) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
            </div>
        </div>

        <!-- Terms & Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('payment_terms') ?: 'Payment Terms' ?></label>
                <textarea name="payment_terms" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"><?= e($q['payment_terms'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('notes') ?: 'Notes' ?></label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"><?= e($q['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?= url('quotations') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold text-sm hover:bg-gray-200 transition"><?= __('cancel') ?: 'Cancel' ?></a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-orange-600 to-amber-600 text-white rounded-xl font-semibold shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><?= $isEdit ? (__('update') ?: 'Update') : (__('create') ?: 'Create') ?>
            </button>
        </div>
    </form>
</div>

<script>
function quotationBuilder(existing) {
    return {
        items: existing || [],
        addItem() {
            this.items.push({
                day_number: this.items.length > 0 ? this.items[this.items.length-1].day_number : 1,
                item_type: 'hotel',
                item_name: '',
                description: '',
                quantity: 1,
                unit_price: 0,
                total_price: 0,
                currency: 'USD'
            });
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
            this.syncJson();
        },
        calcItemTotal(idx) {
            this.items[idx].total_price = (this.items[idx].quantity || 1) * (this.items[idx].unit_price || 0);
            this.syncJson();
        },
        subtotal() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.total_price) || 0), 0);
        },
        syncJson() {
            document.getElementById('itemsJsonField').value = JSON.stringify(this.items);
        },
        init() {
            this.$watch('items', () => this.syncJson(), { deep: true });
        }
    }
}
document.getElementById('quotationForm').addEventListener('submit', function() {
    const el = document.querySelector('[x-data]');
    if (el && el.__x) {
        document.getElementById('itemsJsonField').value = JSON.stringify(el.__x.$data.items || []);
    }
});
</script>
