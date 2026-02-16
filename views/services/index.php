<?php
/**
 * Services & Pricing — Admin List View
 * Tabbed interface for Tours, Transfers with pricing
 */
$typeLabels = ['tour' => 'Tours', 'transfer' => 'Transfers'];
$typeIcons = ['tour' => 'fa-map-marked-alt', 'transfer' => 'fa-shuttle-van'];
$typeColors = ['tour' => 'purple', 'transfer' => 'blue'];
$unitLabels = ['per_person' => 'Per Person', 'per_night' => 'Per Night', 'per_vehicle' => 'Per Vehicle', 'per_group' => 'Per Group', 'flat' => 'Flat Rate'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Service saved successfully
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-tags text-brand-500 mr-2"></i>Services & Pricing</h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> services found</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('services/create?type=tour') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-xl font-semibold shadow-lg hover:bg-purple-700 transition text-sm">
            <i class="fas fa-map-marked-alt"></i> Add Tour
        </a>
        <a href="<?= url('services/create?type=transfer') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:bg-blue-700 transition text-sm">
            <i class="fas fa-shuttle-van"></i> Add Transfer
        </a>
    </div>
</div>

<!-- Type Tabs -->
<div class="flex gap-2 mb-6 flex-wrap">
    <a href="<?= url('services') ?>" class="px-4 py-2 rounded-xl text-sm font-semibold transition <?= empty($filters['type']) ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-800' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' ?>">
        All <span class="ml-1 text-xs opacity-75">(<?= array_sum($counts) ?>)</span>
    </a>
    <?php foreach ($typeLabels as $key => $label): ?>
    <a href="<?= url('services?type=' . $key) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-2 <?= ($filters['type'] ?? '') === $key ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-800' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' ?>">
        <i class="fas <?= $typeIcons[$key] ?>"></i> <?= $label ?>
        <span class="text-xs opacity-75">(<?= $counts[$key] ?? 0 ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Search -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('services') ?>" class="flex gap-4">
        <?php if ($filters['type']): ?><input type="hidden" name="type" value="<?= e($filters['type']) ?>"><?php endif; ?>
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search services..." class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-brand-500">
        <button type="submit" class="px-5 py-2.5 bg-brand-600 text-white rounded-xl font-semibold text-sm hover:bg-brand-700 transition"><i class="fas fa-search mr-1"></i> Search</button>
    </form>
</div>

<!-- Services Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($services)): ?>
    <div class="p-12 text-center text-gray-400">
        <i class="fas fa-tags text-4xl mb-3 opacity-30"></i>
        <p>No services found</p>
        <p class="text-sm mt-1">Add your first service to get started</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Service</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Type</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Price</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Unit</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($services as $s): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-100 dark:bg-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-900/20 flex items-center justify-center flex-shrink-0">
                                <i class="fas <?= $typeIcons[$s['service_type']] ?? 'fa-tag' ?> text-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-200"><?= e($s['name']) ?></div>
                                <?php if ($s['description']): ?>
                                <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs"><?= e($s['description']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-100 text-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-700 dark:bg-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-900/30 dark:text-<?= $typeColors[$s['service_type']] ?? 'gray' ?>-400">
                            <i class="fas <?= $typeIcons[$s['service_type']] ?? 'fa-tag' ?> text-[10px]"></i>
                            <?= ucfirst($s['service_type']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <span class="font-bold text-gray-800 dark:text-gray-200"><?= number_format($s['price'], 2) ?></span>
                        <span class="text-xs text-gray-400 ml-1"><?= $s['currency'] ?></span>
                    </td>
                    <td class="px-5 py-4 text-gray-500 text-xs"><?= $unitLabels[$s['unit']] ?? $s['unit'] ?></td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $s['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= ucfirst($s['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= url('services/edit?id=' . $s['id']) ?>" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            <form method="POST" action="<?= url('services/delete') ?>" onsubmit="return confirm('Delete this service?')" class="inline">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="text-sm text-gray-500">Page <?= $page ?> of <?= $totalPages ?></div>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('services?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>" 
               class="px-3 py-1 rounded-lg text-sm <?= $i === $page ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
