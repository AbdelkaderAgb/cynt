<?php
/**
 * Service Form — Create/Edit
 * Handles Tours, Transfers with detailed pricing
 * Type-specific fields shown/hidden via Alpine.js
 */
$s = $service;
$isEdit = $isEdit ?? false;
?>

<div class="mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= url('services') ?>" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= $isEdit ? 'Edit service details and pricing' : 'Add a new service with pricing' ?></p>
        </div>
    </div>
</div>

<form method="POST" action="<?= url('services/store') ?>" class="space-y-6 max-w-4xl" x-data="serviceForm()">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $s['id'] ?>"><?php endif; ?>

    <!-- Service Type & Name -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i> Service Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Service Type *</label>
                <select name="service_type" x-model="serviceType" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
                    <option value="tour">Tour</option>
                    <option value="transfer">Transfer</option>
                    <option value="hotel">Hotel</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Name *</label>
                <input type="text" name="name" value="<?= e($s['name'] ?? '') ?>" required 
                       placeholder="e.g. Bosphorus Cruise Tour, Airport Transfer IST..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description of this service..."
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500"><?= e($s['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Tour-specific fields -->
    <div x-show="serviceType === 'tour'" x-transition class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-map-marked-alt text-purple-500"></i> Tour Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Destination</label>
                <input type="text" name="destination" value="<?= e($s['destination'] ?? '') ?>" 
                       placeholder="e.g. Sultanahmet, Cappadocia..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Duration</label>
                <input type="text" name="duration" value="<?= e($s['duration'] ?? '') ?>" 
                       placeholder="e.g. Full Day, 4 hours, 2 days..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
        </div>
    </div>

    <!-- Transfer-specific fields -->
    <div x-show="serviceType === 'transfer'" x-transition class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-shuttle-van text-blue-500"></i> Transfer Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pickup Location</label>
                <input type="text" name="pickup_location" value="<?= e($s['pickup_location'] ?? '') ?>" 
                       placeholder="e.g. Istanbul Airport (IST)"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Dropoff Location</label>
                <input type="text" name="dropoff_location" value="<?= e($s['dropoff_location'] ?? '') ?>" 
                       placeholder="e.g. Sultanahmet Hotel"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Vehicle Type</label>
                <input type="text" name="vehicle_type" value="<?= e($s['vehicle_type'] ?? '') ?>" 
                       placeholder="e.g. Sedan, Van, Minibus..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Max Passengers</label>
                <input type="number" name="max_pax" value="<?= $s['max_pax'] ?? 0 ?>" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-dollar-sign text-emerald-500"></i> Pricing
        </h3>

        <!-- Tour pricing: Adult/Child/Infant -->
        <div x-show="serviceType === 'tour'" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Adult Price *</label>
                <input type="number" name="price_adult" value="<?= $s['price_adult'] ?? 0 ?>" step="0.01" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500 font-bold text-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Child Price</label>
                <input type="number" name="price_child" value="<?= $s['price_child'] ?? 0 ?>" step="0.01" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Infant Price</label>
                <input type="number" name="price_infant" value="<?= $s['price_infant'] ?? 0 ?>" step="0.01" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Currency</label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($s['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Non-tour pricing: Single price -->
        <div x-show="serviceType !== 'tour'" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Price *</label>
                <input type="number" name="price" value="<?= $s['price'] ?? 0 ?>" step="0.01" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500 font-bold text-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Currency</label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($s['currency'] ?? 'USD') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Unit</label>
                <select name="unit" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <option value="per_person" <?= ($s['unit'] ?? '') === 'per_person' ? 'selected' : '' ?>>Per Person</option>
                    <option value="per_night" <?= ($s['unit'] ?? '') === 'per_night' ? 'selected' : '' ?>>Per Night</option>
                    <option value="per_vehicle" <?= ($s['unit'] ?? '') === 'per_vehicle' ? 'selected' : '' ?>>Per Vehicle</option>
                    <option value="per_group" <?= ($s['unit'] ?? '') === 'per_group' ? 'selected' : '' ?>>Per Group</option>
                    <option value="flat" <?= ($s['unit'] ?? '') === 'flat' ? 'selected' : '' ?>>Flat Rate</option>
                </select>
            </div>
        </div>

        <!-- Tour unit (always per_person for tours) -->
        <div x-show="serviceType === 'tour'" class="text-xs text-gray-400 mt-1">
            <i class="fas fa-info-circle mr-1"></i> Tour pricing is per person (adult/child/infant breakdown)
            <input type="hidden" x-bind:name="serviceType === 'tour' ? '' : ''" x-bind:value="'per_person'">
        </div>
    </div>

    <!-- Extra Details & Status -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-purple-500"></i> Additional
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Extra Details</label>
                <textarea name="details" rows="3" placeholder="Included services, special notes..."
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm"><?= e($s['details'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <option value="active" <?= ($s['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($s['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-emerald-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Update Service' : 'Save Service' ?>
        </button>
        <a href="<?= url('services') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
    </div>
</form>

<script>
function serviceForm() {
    return {
        serviceType: '<?= e($s['service_type'] ?? 'tour') ?>',
    };
}
</script>
