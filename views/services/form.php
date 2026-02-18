<?php
/**
 * Service Form — Create/Edit
 * Handles Tours, Transfers with pricing
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
            <p class="text-sm text-gray-500 mt-1"><?= $isEdit ? 'Edit service details' : 'Add a new service with pricing' ?></p>
        </div>
    </div>
</div>

<form method="POST" action="<?= url('services/store') ?>" class="space-y-6 max-w-3xl">
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
                <select name="service_type" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
                    <option value="tour" <?= ($s['service_type'] ?? '') === 'tour' ? 'selected' : '' ?>>🗺️ Tour</option>
                    <option value="transfer" <?= ($s['service_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>🚐 Transfer</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Name *</label>
                <input type="text" name="name" value="<?= e($s['name'] ?? '') ?>" required 
                       placeholder="e.g. Grand Hyatt Istanbul, Cappadocia Tour..."
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of this service..."
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500"><?= e($s['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-dollar-sign text-emerald-500"></i> Pricing
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
    </div>

    <!-- Extra Details & Status -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-purple-500"></i> Additional
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Extra Details</label>
                <textarea name="details" rows="3" placeholder="Room types, duration, included services..."
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm"><?= e($s['details'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm">
                    <option value="active" <?= ($s['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>✅ Active</option>
                    <option value="inactive" <?= ($s['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>⏸️ Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Update Service' : 'Save Service' ?>
        </button>
        <a href="<?= url('services') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
            Cancel
        </a>
    </div>
</form>
