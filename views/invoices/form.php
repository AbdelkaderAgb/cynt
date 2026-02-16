<?php
/**
 * Invoice Form View — Create/Edit
 * Enhanced with AJAX partner autocomplete and translated labels
 */
$inv = $invoice;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
</div>

<form method="POST" action="<?= url('invoices/store') ?>" class="space-y-6">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $inv['id'] ?>"><?php endif; ?>
    <input type="hidden" name="company_id" id="inv_company_id" value="<?= e($inv['company_id'] ?? '') ?>">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-file-invoice text-emerald-500 mr-2"></i><?= __('details') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- Company with autocomplete -->
            <div class="relative" x-data="invPartnerSearch()" @click.outside="open = false">
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('company_name') ?> *</label>
                <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                       value="<?= e($inv['company_name'] ?? '') ?>" required autocomplete="off"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1"><?= __('search_partner') ?></p>

                <div x-show="open && results.length > 0" x-transition
                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="selectPartner(r)" class="px-4 py-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.city ? ' · ' + r.city : '')"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('date') ?></label>
                <input type="date" name="invoice_date" value="<?= $inv['invoice_date'] ?? date('Y-m-d') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('due_date') ?></label>
                <input type="date" name="due_date" value="<?= $inv['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('subtotal') ?></label>
                <input type="number" name="subtotal" id="subtotal" value="<?= $inv['subtotal'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('tax') ?> (%)</label>
                <input type="number" name="tax_rate" id="tax_rate" value="<?= $inv['tax_rate'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('tax') ?></label>
                <input type="number" name="tax_amount" id="tax_amount" value="<?= $inv['tax_amount'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('discount') ?></label>
                <input type="number" name="discount" value="<?= $inv['discount'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('total_amount') ?></label>
                <input type="number" name="total_amount" id="total_amount" value="<?= $inv['total_amount'] ?? 0 ?>" step="0.01" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl font-bold">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('currency') ?></label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($inv['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
                    <?php foreach (['draft'=>__('draft'),'sent'=>__('sent'),'paid'=>__('paid'),'overdue'=>__('overdue')] as $k=>$lbl): ?>
                    <option value="<?= $k ?>" <?= ($inv['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('payment_method') ?></label>
                <select name="payment_method" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl">
                    <option value="">-- <?= __('select') ?> --</option>
                    <?php foreach (['cash'=>__('cash'),'card'=>__('credit_card'),'transfer'=>__('bank_transfer'),'check'=>__('check')] as $k=>$lbl): ?>
                    <option value="<?= $k ?>" <?= ($inv['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('notes') ?></label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?= e($inv['notes'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('terms_conditions') ?></label>
                <textarea name="terms" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?= e($inv['terms'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?>
        </button>
        <a href="<?= url('invoices') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>

<!-- Partner Search Script -->
<script>
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
            // Auto-fill additional fields if they exist
            const fields = {
                'contact_person': r.contact_person,
                'email': r.email,
                'phone': r.phone,
                'address': r.address,
                'city': r.city,
                'country': r.country
            };
            for (const [name, val] of Object.entries(fields)) {
                const el = document.querySelector(`[name="${name}"]`);
                if (el && val) el.value = val;
            }
        }
    };
}
</script>
