<?php
/**
 * Group File Detail View
 */
$statusLabels = [
    'planning' => 'Planning', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress',
    'completed' => 'Completed', 'cancelled' => 'Cancelled',
];
$statusColors = [
    'planning' => 'bg-gray-100 text-gray-600', 'confirmed' => 'bg-blue-100 text-blue-700',
    'in_progress' => 'bg-cyan-100 text-cyan-700', 'completed' => 'bg-emerald-100 text-emerald-700',
    'cancelled' => 'bg-red-100 text-red-700',
];
$typeIcons = [
    'hotel_voucher' => 'fa-hotel text-teal-500',
    'tour'          => 'fa-route text-blue-500',
    'transfer'      => 'fa-shuttle-van text-green-500',
];
$typeLabels = [
    'hotel_voucher' => 'Hotel Voucher',
    'tour'          => 'Tour',
    'transfer'      => 'Transfer',
];

// Group items by day
$days = [];
foreach ($items as $item) {
    $d = (int)($item['day_number'] ?? 1);
    $days[$d][] = $item;
}
ksort($days);
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Group file created successfully
</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Group file updated
</div>
<?php endif; ?>

<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-folder-open mr-2 text-violet-500"></i><?= e($g['group_name']) ?>
            </h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="font-mono text-sm text-violet-600"><?= e($g['file_number']) ?></span>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$g['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $statusLabels[$g['status']] ?? ucfirst($g['status']) ?>
                </span>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= url('group-files/pdf') ?>?id=<?= (int)$g['id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition">
                <i class="fas fa-file-pdf"></i> PDF Dossier
            </a>
            <a href="<?= url('group-files/edit') ?>?id=<?= (int)$g['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?= url('group-files') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Group Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Arrival</dt><dd class="font-medium"><?= $g['arrival_date'] ? date('d M Y', strtotime($g['arrival_date'])) : '—' ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Departure</dt><dd class="font-medium"><?= $g['departure_date'] ? date('d M Y', strtotime($g['departure_date'])) : '—' ?></dd></div>
                <?php if ($g['arrival_date'] && $g['departure_date']): ?>
                <div class="flex justify-between"><dt class="text-gray-500">Duration</dt><dd class="font-medium"><?= (int)((strtotime($g['departure_date']) - strtotime($g['arrival_date'])) / 86400) ?> nights</dd></div>
                <?php endif; ?>
            </dl>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Passengers</h3>
            <div class="text-3xl font-bold text-violet-600"><?= (int)$g['total_pax'] ?></div>
            <div class="flex gap-4 mt-2 text-sm text-gray-500">
                <span><i class="fas fa-user mr-1"></i><?= (int)$g['adults'] ?> adults</span>
                <?php if ((int)$g['children']): ?><span><i class="fas fa-child mr-1"></i><?= (int)$g['children'] ?> children</span><?php endif; ?>
                <?php if ((int)$g['infants']): ?><span><i class="fas fa-baby mr-1"></i><?= (int)$g['infants'] ?> infants</span><?php endif; ?>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Leader & Partner</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Leader</dt><dd class="font-medium"><?= e($g['leader_name'] ?: '—') ?></dd></div>
                <?php if ($g['leader_phone']): ?>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium"><a href="tel:<?= e($g['leader_phone']) ?>" class="text-violet-600 hover:underline"><?= e($g['leader_phone']) ?></a></dd></div>
                <?php endif; ?>
                <div class="flex justify-between"><dt class="text-gray-500">Partner</dt><dd class="font-medium"><?= e($g['partner_name'] ?? '—') ?></dd></div>
            </dl>
        </div>
    </div>

    <!-- Timeline / Day-by-Day -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-stream mr-2 text-green-400"></i>Timeline</h3>
        <?php if (empty($days)): ?>
        <p class="text-center text-gray-400 py-8"><i class="fas fa-link text-3xl mb-2 block"></i>No linked bookings yet.</p>
        <?php else: ?>
        <div class="relative pl-8 border-l-2 border-violet-200 dark:border-violet-800 space-y-6">
            <?php foreach ($days as $dayNum => $dayItems): ?>
            <div class="relative">
                <div class="absolute -left-[2.55rem] top-0 w-6 h-6 rounded-full bg-violet-500 text-white text-[10px] font-bold flex items-center justify-center"><?= $dayNum ?></div>
                <h4 class="text-sm font-bold text-violet-600 mb-2">Day <?= $dayNum ?></h4>
                <div class="space-y-2">
                    <?php foreach ($dayItems as $item): ?>
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                        <i class="fas <?= $typeIcons[$item['item_type']] ?? 'fa-circle text-gray-400' ?> mt-0.5"></i>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] uppercase font-semibold text-gray-400"><?= $typeLabels[$item['item_type']] ?? ucfirst($item['item_type']) ?></span>
                                <span class="text-[10px] text-gray-400">#<?= (int)$item['reference_id'] ?></span>
                                <?php if (!empty($item['detail']['status'])): ?>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $statusColors[$item['detail']['status']] ?? 'bg-gray-100 text-gray-500' ?>"><?= ucfirst($item['detail']['status']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-0.5"><?= e($item['detail']['label'] ?: 'Booking #' . $item['reference_id']) ?></p>
                            <?php if ($item['detail']['date']): ?>
                            <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-clock mr-1"></i><?= e($item['detail']['date']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['notes'])): ?>
                            <p class="text-xs text-gray-400 mt-1 italic"><?= e($item['notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($g['notes']): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6">
        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap"><?= e($g['notes']) ?></p>
    </div>
    <?php endif; ?>
</div>
