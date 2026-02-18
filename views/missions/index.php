<?php
/**
 * Missions List View
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

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> <?= __('mission_saved') ?: 'Mission saved successfully' ?>
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-trash"></i> <?= __('mission_deleted') ?: 'Mission deleted' ?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-tasks mr-2 text-indigo-500"></i><?= __('missions') ?: 'Missions' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('records_found') ?: 'records found' ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('missions/calendar') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold text-sm hover:bg-gray-50 transition">
            <i class="fas fa-calendar-alt"></i> <?= __('calendar_view') ?: 'Calendar' ?>
        </a>
        <a href="<?= url('missions/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> <?= __('new_mission') ?: 'New Mission' ?>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('missions') ?>" class="grid grid-cols-1 sm:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?: 'Search' ?></label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('guest_location') ?: 'Guest, location...' ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('type') ?: 'Type' ?></label>
            <select name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?: 'All' ?></option>
                <?php foreach ($typeLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?: 'Status' ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?: 'All' ?></option>
                <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('date') ?: 'Date' ?></label>
            <input type="date" name="date" value="<?= e($filters['date']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-search mr-1"></i><?= __('filter') ?: 'Filter' ?></button>
            <a href="<?= url('missions') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('type') ?: 'Type' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('date') ?: 'Date' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('guest') ?: 'Guest' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('route') ?: 'Route' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('pax') ?: 'Pax' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('driver') ?: 'Driver' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('vehicle') ?: 'Vehicle' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?: 'Status' ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?: 'Actions' ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($missions)): ?>
                <tr><td colspan="10" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-tasks text-4xl mb-3 block"></i><?= __('no_missions_found') ?: 'No missions found' ?></td></tr>
                <?php else: ?>
                <?php foreach ($missions as $m): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500"><?= (int)$m['id'] ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium">
                            <i class="fas <?= $typeIcons[$m['mission_type']] ?? 'fa-circle' ?> text-indigo-500"></i>
                            <?= $typeLabels[$m['mission_type']] ?? ucfirst($m['mission_type']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium"><?= $m['mission_date'] ? date('d M Y', strtotime($m['mission_date'])) : '—' ?></div>
                        <?php if ($m['start_time']): ?>
                        <div class="text-xs text-gray-400"><?= date('H:i', strtotime($m['start_time'])) ?><?= $m['end_time'] ? ' - ' . date('H:i', strtotime($m['end_time'])) : '' ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-gray-200"><?= e($m['guest_name'] ?: '—') ?></div>
                        <?php if (!empty($m['guest_passport'])): ?>
                        <div class="text-xs text-gray-400"><i class="fas fa-passport mr-1"></i><?= e($m['guest_passport']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                        <?php if ($m['pickup_location'] || $m['dropoff_location']): ?>
                        <?= e($m['pickup_location'] ?: '—') ?> → <?= e($m['dropoff_location'] ?: '—') ?>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold"><?= (int)$m['pax_count'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <?php if (!empty($m['driver_first'])): ?>
                        <span class="text-gray-700 dark:text-gray-300"><?= e($m['driver_first'] . ' ' . $m['driver_last']) ?></span>
                        <?php else: ?>
                        <span class="text-gray-400 italic"><?= __('unassigned') ?: 'Unassigned' ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <?php if (!empty($m['plate_number'])): ?>
                        <span class="font-mono text-gray-700 dark:text-gray-300"><?= e($m['plate_number']) ?></span>
                        <?php else: ?>
                        <span class="text-gray-400 italic">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusColors[$m['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$m['status']] ?? ucfirst($m['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= url('missions/show') ?>?id=<?= (int)$m['id'] ?>" class="p-1.5 text-gray-400 hover:text-indigo-600 transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('missions/edit') ?>?id=<?= (int)$m['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('missions/delete') ?>?id=<?= (int)$m['id'] ?>" onclick="return confirm('Delete this mission?')" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <p class="text-xs text-gray-500"><?= __('page') ?: 'Page' ?> <?= $page ?> / <?= $pages ?></p>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="<?= url('missions') ?>?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition">&laquo; <?= __('prev') ?: 'Prev' ?></a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="<?= url('missions') ?>?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition"><?= __('next') ?: 'Next' ?> &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
