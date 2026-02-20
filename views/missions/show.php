<?php
/**
 * Mission Detail View
 */
$statusLabels = [
    'pending'     => 'Pending',
    'assigned'    => 'Assigned',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
];
$statusColors = [
    'pending'     => 'bg-amber-100 text-amber-700',
    'assigned'    => 'bg-blue-100 text-blue-700',
    'in_progress' => 'bg-cyan-100 text-cyan-700',
    'completed'   => 'bg-emerald-100 text-emerald-700',
    'cancelled'   => 'bg-red-100 text-red-700',
];
$typeLabels = [
    'tour'          => 'Tour',
    'transfer'      => 'Transfer',
    'hotel_service' => 'Hotel Service',
];
$typeIcons = [
    'tour'          => 'fa-route',
    'transfer'      => 'fa-shuttle-van',
    'hotel_service' => 'fa-concierge-bell',
];
?>

<?php if (isset($_GET['updated'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> <?= __('mission_updated') ?: 'Mission updated successfully' ?>
</div>
<?php endif; ?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas <?= $typeIcons[$m['mission_type']] ?? 'fa-tasks' ?> mr-2 text-indigo-500"></i>
                <?= __('mission') ?: 'Mission' ?> #<?= (int)$m['id'] ?>
            </h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$m['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $statusLabels[$m['status']] ?? ucfirst($m['status']) ?>
                </span>
                <span class="text-sm text-gray-500">
                    <i class="fas <?= $typeIcons[$m['mission_type']] ?? 'fa-circle' ?> mr-1"></i>
                    <?= $typeLabels[$m['mission_type']] ?? ucfirst($m['mission_type']) ?>
                </span>
                <?php if ((int)$m['reference_id'] > 0): ?>
                <span class="text-sm text-gray-400">Ref #<?= (int)$m['reference_id'] ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="<?= url('missions/pdf') ?>?id=<?= (int)$m['id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 rounded-xl text-sm font-semibold hover:bg-red-100 transition">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="<?= url('missions/edit') ?>?id=<?= (int)$m['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">
                <i class="fas fa-edit"></i> <?= __('edit') ?: 'Edit' ?>
            </a>
            <a href="<?= url('missions') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left"></i> <?= __('back') ?: 'Back' ?>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Schedule & Route -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-calendar-alt mr-2 text-blue-400"></i><?= __('schedule_route') ?: 'Schedule & Route' ?></h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('date') ?: 'Date' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= $m['mission_date'] ? date('d M Y', strtotime($m['mission_date'])) : '—' ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('time') ?: 'Time' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <?= $m['start_time'] ? date('H:i', strtotime($m['start_time'])) : '—' ?>
                        <?= $m['end_time'] ? ' — ' . date('H:i', strtotime($m['end_time'])) : '' ?>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('pickup') ?: 'Pickup' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($m['pickup_location'] ?: '—') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('dropoff') ?: 'Drop-off' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($m['dropoff_location'] ?: '—') ?></dd>
                </div>
            </dl>
        </div>

        <!-- Guest Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-user mr-2 text-teal-400"></i><?= __('guest_info') ?: 'Guest Information' ?></h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('guest_name') ?: 'Guest Name' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($m['guest_name'] ?: '—') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><i class="fas fa-passport mr-1 text-amber-500"></i><?= __('passport') ?: 'Passport' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($m['guest_passport'] ?: '—') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500"><?= __('total_pax') ?: 'Total Pax' ?></dt>
                    <dd>
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 text-sm font-bold"><?= (int)$m['pax_count'] ?></span>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Assignment -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-user-cog mr-2 text-purple-400"></i><?= __('assignment') ?: 'Assignment' ?></h3>
            <dl class="space-y-3">
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500"><i class="fas fa-id-card mr-1"></i><?= __('driver') ?: 'Driver' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <?php if (!empty($m['driver_first'])): ?>
                            <?= e($m['driver_first'] . ' ' . $m['driver_last']) ?>
                            <?php if (!empty($m['driver_phone'])): ?>
                            <a href="tel:<?= e($m['driver_phone']) ?>" class="ml-2 text-xs text-indigo-500 hover:underline"><i class="fas fa-phone"></i> <?= e($m['driver_phone']) ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400 italic"><?= __('unassigned') ?: 'Unassigned' ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500"><i class="fas fa-user-tie mr-1"></i><?= __('guide') ?: 'Guide' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <?php if (!empty($m['guide_first'])): ?>
                            <?= e($m['guide_first'] . ' ' . $m['guide_last']) ?>
                            <?php if (!empty($m['guide_phone'])): ?>
                            <a href="tel:<?= e($m['guide_phone']) ?>" class="ml-2 text-xs text-indigo-500 hover:underline"><i class="fas fa-phone"></i> <?= e($m['guide_phone']) ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400 italic"><?= __('unassigned') ?: 'Unassigned' ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500"><i class="fas fa-car mr-1"></i><?= __('vehicle') ?: 'Vehicle' ?></dt>
                    <dd class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <?php if (!empty($m['plate_number'])): ?>
                            <span class="font-mono"><?= e($m['plate_number']) ?></span>
                            <span class="text-xs text-gray-400 ml-1"><?= e(($m['vehicle_make'] ?? '') . ' ' . ($m['vehicle_model'] ?? '')) ?></span>
                            <?php if (!empty($m['vehicle_capacity'])): ?>
                            <span class="text-xs text-gray-400 ml-1">(<?= (int)$m['vehicle_capacity'] ?> seats)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400 italic"><?= __('unassigned') ?: 'Unassigned' ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-sticky-note mr-2 text-amber-400"></i><?= __('notes') ?: 'Notes' ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap"><?= e($m['notes'] ?: '—') ?></p>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                <?= __('created') ?: 'Created' ?>: <?= $m['created_at'] ? date('d M Y H:i', strtotime($m['created_at'])) : '—' ?>
                <?php if ($m['updated_at']): ?> · <?= __('updated') ?: 'Updated' ?>: <?= date('d M Y H:i', strtotime($m['updated_at'])) ?><?php endif; ?>
            </div>
        </div>
    </div>
</div>
