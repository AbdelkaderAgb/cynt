<?php
/**
 * Mission Create/Edit Form
 */
$isEdit = !empty($mission);
$m = $mission ?? [];
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-tasks mr-2 text-indigo-500"></i>
                <?= $isEdit ? (__('edit_mission') ?: 'Edit Mission') : (__('new_mission') ?: 'New Mission') ?>
            </h1>
            <?php if ($isEdit): ?>
            <p class="text-sm text-gray-500 mt-1"><?= __('mission') ?: 'Mission' ?> #<?= (int)$m['id'] ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= url('missions') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i> <?= __('back') ?: 'Back' ?>
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? url('missions/update') : url('missions/store') ?>" class="space-y-6">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
        <?php endif; ?>

        <!-- Mission Type & Reference -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-info-circle mr-2 text-indigo-400"></i><?= __('mission_details') ?: 'Mission Details' ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('mission_type') ?: 'Mission Type' ?> *</label>
                    <select name="mission_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="transfer" <?= ($m['mission_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                        <option value="tour" <?= ($m['mission_type'] ?? '') === 'tour' ? 'selected' : '' ?>>Tour</option>
                        <option value="hotel_service" <?= ($m['mission_type'] ?? '') === 'hotel_service' ? 'selected' : '' ?>>Hotel Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('reference_id') ?: 'Booking Reference ID' ?></label>
                    <input type="number" name="reference_id" value="<?= (int)($m['reference_id'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0 = manual">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?: 'Status' ?></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="pending" <?= ($m['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="assigned" <?= ($m['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                        <option value="in_progress" <?= ($m['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= ($m['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($m['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Schedule & Route -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-calendar-alt mr-2 text-blue-400"></i><?= __('schedule_route') ?: 'Schedule & Route' ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('mission_date') ?: 'Mission Date' ?> *</label>
                    <input type="date" name="mission_date" value="<?= e($m['mission_date'] ?? date('Y-m-d')) ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('start_time') ?: 'Start Time' ?></label>
                    <input type="time" name="start_time" value="<?= e($m['start_time'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('end_time') ?: 'End Time' ?></label>
                    <input type="time" name="end_time" value="<?= e($m['end_time'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('pickup_location') ?: 'Pickup Location' ?></label>
                    <input type="text" name="pickup_location" value="<?= e($m['pickup_location'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Hotel, airport, address...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('dropoff_location') ?: 'Drop-off Location' ?></label>
                    <input type="text" name="dropoff_location" value="<?= e($m['dropoff_location'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Hotel, airport, address...">
                </div>
            </div>
        </div>

        <!-- Guest Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-user mr-2 text-teal-400"></i><?= __('guest_info') ?: 'Guest Information' ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                    <input type="text" name="guest_name" value="<?= e($m['guest_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-passport mr-1 text-amber-500"></i><?= __('passport_no') ?: 'Passport No.' ?></label>
                    <input type="text" name="guest_passport" value="<?= e($m['guest_passport'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('total_pax') ?: 'Total Pax' ?></label>
                    <input type="number" name="pax_count" value="<?= (int)($m['pax_count'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Assignment -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-user-cog mr-2 text-purple-400"></i><?= __('assignment') ?: 'Assignment' ?></h3>
            <p class="text-xs text-gray-400 mb-4"><?= __('all_optional') ?: 'All fields below are optional' ?></p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-id-card mr-1"></i><?= __('driver') ?: 'Driver' ?></label>
                    <select name="driver_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value=""><?= __('none') ?: '— None —' ?></option>
                        <?php foreach ($drivers as $d): ?>
                        <option value="<?= (int)$d['id'] ?>" <?= ((int)($m['driver_id'] ?? 0)) === (int)$d['id'] ? 'selected' : '' ?>>
                            <?= e($d['first_name'] . ' ' . $d['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-user-tie mr-1"></i><?= __('guide') ?: 'Guide' ?></label>
                    <select name="guide_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value=""><?= __('none') ?: '— None —' ?></option>
                        <?php foreach ($guides as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= ((int)($m['guide_id'] ?? 0)) === (int)$g['id'] ? 'selected' : '' ?>>
                            <?= e($g['first_name'] . ' ' . $g['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-car mr-1"></i><?= __('vehicle') ?: 'Vehicle' ?></label>
                    <select name="vehicle_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value=""><?= __('none') ?: '— None —' ?></option>
                        <?php foreach ($vehicles as $v): ?>
                        <option value="<?= (int)$v['id'] ?>" <?= ((int)($m['vehicle_id'] ?? 0)) === (int)$v['id'] ? 'selected' : '' ?>>
                            <?= e($v['plate_number'] . ' — ' . $v['make'] . ' ' . $v['model']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-sticky-note mr-1"></i><?= __('notes') ?: 'Notes' ?></label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500"><?= e($m['notes'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?= url('missions') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold text-sm hover:bg-gray-200 transition"><?= __('cancel') ?: 'Cancel' ?></a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><?= $isEdit ? (__('update_mission') ?: 'Update Mission') : (__('create_mission') ?: 'Create Mission') ?>
            </button>
        </div>
    </form>
</div>
