<?php
/**
 * Voucher Form View — Create/Edit
 * Enhanced with AJAX partner autocomplete and translated labels
 */
$v = $voucher;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
    <p class="text-sm text-gray-500 mt-1"><?= $isEdit ? __('edit', [], 'Edit') . ' voucher' : __('create', [], 'Create') . ' voucher' ?></p>
</div>

<form method="POST" action="<?= url('vouchers/store') ?>" class="space-y-6">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $v['id'] ?>"><?php endif; ?>
    <input type="hidden" name="company_id" id="company_id" value="<?= e($v['company_id'] ?? '') ?>">

    <!-- Company & Hotel -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2"><i class="fas fa-building text-blue-500"></i><?= __('company_name') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative" x-data="partnerSearch()" @click.outside="open = false">
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('company_name') ?> *</label>
                <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                       value="<?= e($v['company_name'] ?? '') ?>" required autocomplete="off"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1"><?= __('search_partner') ?> <?= __('or_type_new') ?></p>

                <!-- Dropdown -->
                <div x-show="open && results.length > 0" x-transition
                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="selectPartner(r)" class="px-4 py-3 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.city ? ' · ' + r.city : '') + (r.email ? ' · ' + r.email : '')"></div>
                        </div>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('hotel_name') ?></label>
                <input type="text" name="hotel_name" value="<?= e($v['hotel_name'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Transfer Details -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2"><i class="fas fa-route text-emerald-500"></i><?= __('transfers') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('pickup_location') ?> *</label>
                <input type="text" name="pickup_location" value="<?= e($v['pickup_location'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('dropoff_location') ?> *</label>
                <input type="text" name="dropoff_location" value="<?= e($v['dropoff_location'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('transfer_type') ?></label>
                <select name="transfer_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <option value="one_way" <?= ($v['transfer_type'] ?? '') === 'one_way' ? 'selected' : '' ?>><?= __('one_way') ?></option>
                    <option value="round_trip" <?= ($v['transfer_type'] ?? '') === 'round_trip' ? 'selected' : '' ?>><?= __('round_trip') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('pickup_date') ?> *</label>
                <input type="date" name="pickup_date" value="<?= $v['pickup_date'] ?? '' ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('pickup_time') ?> *</label>
                <input type="time" name="pickup_time" value="<?= $v['pickup_time'] ?? '' ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('total_pax') ?></label>
                <input type="number" name="total_pax" value="<?= $v['total_pax'] ?? 1 ?>" min="1" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('return_date') ?></label>
                <input type="date" name="return_date" value="<?= $v['return_date'] ?? '' ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('return_time') ?></label>
                <input type="time" name="return_time" value="<?= $v['return_time'] ?? '' ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('flight_number') ?></label>
                <input type="text" name="flight_number" value="<?= e($v['flight_number'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
            </div>
        </div>
    </div>

    <!-- Assignment & Price -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2"><i class="fas fa-user-tie text-purple-500"></i><?= __('driver') ?> / <?= __('vehicle') ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('driver') ?></label>
                <select name="driver_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <option value="">-- <?= __('select') ?> --</option>
                    <?php foreach ($drivers ?? [] as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($v['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('vehicle') ?></label>
                <select name="vehicle_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <option value="">-- <?= __('select') ?> --</option>
                    <?php foreach ($vehicles ?? [] as $vh): ?>
                    <option value="<?= $vh['id'] ?>" <?= ($v['vehicle_id'] ?? '') == $vh['id'] ? 'selected' : '' ?>><?= e($vh['plate_number']) ?> — <?= e($vh['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('tour_guide') ?></label>
                <select name="guide_id" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <option value="">-- <?= __('select') ?> --</option>
                    <?php foreach ($guides ?? [] as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($v['guide_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('amount') ?></label>
                <input type="number" name="price" value="<?= $v['price'] ?? 0 ?>" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('currency') ?></label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($v['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700">
                    <option value="pending" <?= ($v['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>><?= __('pending') ?></option>
                    <option value="confirmed" <?= ($v['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>><?= __('confirmed') ?></option>
                    <option value="completed" <?= ($v['status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= __('completed') ?></option>
                    <option value="cancelled" <?= ($v['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('cancelled') ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Notes & Passengers -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('passengers') ?></label>
                <textarea name="passengers" rows="4" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"><?= e($v['passengers'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"><?= __('notes') ?></label>
                <textarea name="notes" rows="4" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-2 focus:ring-blue-500"><?= e($v['notes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?>
        </button>
        <a href="<?= url('vouchers') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
            <?= __('cancel') ?>
        </a>
    </div>
</form>

<!-- Partner Search Script -->
<script>
function partnerSearch() {
    return {
        query: '<?= e($v['company_name'] ?? '') ?>',
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
            document.getElementById('company_id').value = r.id;
            // Auto-fill additional fields if they exist
            const fields = {
                'contact_person': r.contact_person,
                'email': r.email,
                'phone': r.phone,
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
