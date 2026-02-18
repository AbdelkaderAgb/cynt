<?php
/**
 * Edit Transfer Form — Tailwind CSS
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('edit') ?> Transfer — <?= e($v['voucher_no']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($v['pickup_location']) ?> → <?= e($v['dropoff_location']) ?></p>
    </div>
    <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> <?= __('back') ?>
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?= url('transfers/update') ?>" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $v['id'] ?>">

        <!-- Company & Hotel -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                <select name="company_name" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Company</option>
                    <?php foreach ($partners ?? [] as $p): ?>
                    <option value="<?= e($p['company_name']) ?>" <?= ($v['company_name'] ?? '') === $p['company_name'] ? 'selected' : '' ?>><?= e($p['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Hotel -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotel</label>
                <select name="hotel_id" onchange="var o=this.options[this.selectedIndex]; document.querySelector('input[name=hotel_name]').value=o.dataset.name||''"
                        class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="0" data-name="">-- Select Hotel --</option>
                    <?php foreach ($hotels ?? [] as $htl): ?>
                    <option value="<?= $htl['id'] ?>" data-name="<?= e($htl['name']) ?>" <?= ($v['hotel_id'] ?? 0) == $htl['id'] ? 'selected' : '' ?>><?= e($htl['name']) ?> <?= $htl['city'] ? '(' . e($htl['city']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="hotel_name" value="<?= e($v['hotel_name'] ?? '') ?>">
            </div>
        </div>

        <!-- Locations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Starting Point</label>
                <input type="text" name="pickup_location" value="<?= e($v['pickup_location'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destination</label>
                <input type="text" name="dropoff_location" value="<?= e($v['dropoff_location'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Date/Time -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Date</label>
                <input type="date" name="pickup_date" value="<?= e($v['pickup_date'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Time</label>
                <input type="time" name="pickup_time" value="<?= e($v['pickup_time'] ?? '') ?>" required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Return Date</label>
                <input type="date" name="return_date" value="<?= e($v['return_date'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Return Time</label>
                <input type="time" name="return_time" value="<?= e($v['return_time'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Transfer Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transfer Type</label>
                <select name="transfer_type" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <option value="one_way" <?= ($v['transfer_type'] ?? '') === 'one_way' ? 'selected' : '' ?>>One Way</option>
                    <option value="round_trip" <?= ($v['transfer_type'] ?? '') === 'round_trip' ? 'selected' : '' ?>>Round Trip</option>
                    <option value="multi_stop" <?= ($v['transfer_type'] ?? '') === 'multi_stop' ? 'selected' : '' ?>>Multi Stop</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Pax</label>
                <input type="number" name="total_pax" value="<?= $v['total_pax'] ?? 1 ?>" min="1" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <!-- Pricing removed — prices are managed via invoices/receipts only -->
        </div>

        <!-- Guest & Passport -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                <input type="text" name="guest_name" value="<?= e($v['guest_name'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Main guest name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passenger Passport' ?></label>
                <input type="text" name="passenger_passport" value="<?= e($v['passenger_passport'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Passport number">
            </div>
        </div>

        <!-- Passengers & Flight -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passengers</label>
                <textarea name="passengers" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"><?= e($v['passengers'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Flight Number</label>
                <input type="text" name="flight_number" value="<?= e($v['flight_number'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. TK123">
            </div>
        </div>

        <!-- Vehicle / Driver / Guide -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehicle</label>
                <select name="vehicle_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <option value="">Select Vehicle</option>
                    <?php foreach ($vehicles ?? [] as $vh): ?>
                    <option value="<?= $vh['id'] ?>" <?= ($v['vehicle_id'] ?? '') == $vh['id'] ? 'selected' : '' ?>><?= e($vh['plate_number']) ?> — <?= e($vh['make'] . ' ' . $vh['model']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver</label>
                <select name="driver_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <option value="">Select Driver</option>
                    <?php foreach ($drivers ?? [] as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($v['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guide</label>
                <select name="guide_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <option value="">Select Guide</option>
                    <?php foreach ($guides ?? [] as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($v['guide_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= e($g['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('status') ?></label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach (['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'] as $sk => $sl): ?>
                    <option value="<?= $sk ?>" <?= ($v['status'] ?? '') === $sk ? 'selected' : '' ?>><?= $sl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"><?= e($v['notes'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i>Update Transfer
            </button>
        </div>
    </form>
</div>

