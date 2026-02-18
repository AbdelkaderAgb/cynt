<?php
/**
 * Services & Pricing — Admin List View
 * Tabbed interface for Tours, Transfers, Hotels with pricing
 * Includes XLSX import functionality
 */
$typeLabels = ['tour' => 'Tours', 'transfer' => 'Transfers', 'hotel' => 'Hotels'];
$typeIcons = ['tour' => 'fa-map-marked-alt', 'transfer' => 'fa-shuttle-van', 'hotel' => 'fa-hotel'];
$typeColors = ['tour' => 'purple', 'transfer' => 'blue', 'hotel' => 'teal'];
$unitLabels = ['per_person' => 'Per Person', 'per_night' => 'Per Night', 'per_vehicle' => 'Per Vehicle', 'per_group' => 'Per Group', 'flat' => 'Flat Rate'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Service saved successfully
</div>
<?php endif; ?>

<?php if (isset($_GET['imported'])): ?>
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)">
    <i class="fas fa-file-import"></i> Successfully imported <?= intval($_GET['imported']) ?> services from XLSX
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)">
    <i class="fas fa-exclamation-triangle"></i> Import error: <?= e($_GET['error']) ?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-tags text-brand-500 mr-2"></i>Services & Pricing</h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> services found — register prices here, then select them on invoices</p>
    </div>
    <div class="flex gap-2 flex-wrap" x-data="{importOpen: false}">
        <a href="<?= url('services/create?type=tour') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 text-white rounded-xl font-semibold shadow-lg hover:bg-purple-700 transition text-sm">
            <i class="fas fa-map-marked-alt"></i> Add Tour
        </a>
        <a href="<?= url('services/create?type=transfer') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:bg-blue-700 transition text-sm">
            <i class="fas fa-shuttle-van"></i> Add Transfer
        </a>
        <button @click="importOpen = !importOpen" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 text-white rounded-xl font-semibold shadow-lg hover:bg-amber-700 transition text-sm">
            <i class="fas fa-file-import"></i> Import XLSX
        </button>

        <!-- Import Modal -->
        <div x-show="importOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="importOpen = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-lg mx-4" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white"><i class="fas fa-file-import text-amber-500 mr-2"></i>Import Services from XLSX</h3>
                    <button @click="importOpen = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg"><i class="fas fa-times"></i></button>
                </div>

                <!-- Tour Import -->
                <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                    <h4 class="font-semibold text-purple-700 dark:text-purple-300 mb-2"><i class="fas fa-map-marked-alt mr-1"></i> Tour Pricing</h4>
                    <p class="text-xs text-gray-500 mb-3">Columns: Name, Description, Destination, Duration, Price Adult, Price Child, Price Infant, Currency, Unit, Status</p>
                    <form method="POST" action="<?= url('services/import-tours') ?>" enctype="multipart/form-data" class="flex gap-2">
                        <?= csrf_field() ?>
                        <input type="file" name="xlsx_file" accept=".xlsx" required class="flex-1 text-sm text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 transition">Import</button>
                    </form>
                </div>

                <!-- Transfer Import -->
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <h4 class="font-semibold text-blue-700 dark:text-blue-300 mb-2"><i class="fas fa-shuttle-van mr-1"></i> Transfer Pricing</h4>
                    <p class="text-xs text-gray-500 mb-3">Columns: Name, Description, Pickup, Dropoff, Vehicle Type, Max Pax, Price, Currency, Unit, Status</p>
                    <form method="POST" action="<?= url('services/import-transfers') ?>" enctype="multipart/form-data" class="flex gap-2">
                        <?= csrf_field() ?>
                        <input type="file" name="xlsx_file" accept=".xlsx" required class="flex-1 text-sm text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">Import</button>
                    </form>
                </div>

                <!-- Hotel Import Link -->
                <div class="p-4 bg-teal-50 dark:bg-teal-900/20 rounded-xl border border-teal-200 dark:border-teal-800">
                    <h4 class="font-semibold text-teal-700 dark:text-teal-300 mb-2"><i class="fas fa-hotel mr-1"></i> Hotel Pricing</h4>
                    <p class="text-xs text-gray-500 mb-2">Hotel pricing is managed through Hotel Profiles with room types and seasonal rates.</p>
                    <a href="<?= url('hotels/profiles') ?>" class="inline-flex items-center gap-2 text-sm text-teal-600 hover:text-teal-800 font-semibold">
                        <i class="fas fa-external-link-alt"></i> Go to Hotel Profiles
                    </a>
                </div>
            </div>
        </div>
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
        <p class="text-sm mt-1">Add your first service or import from XLSX to get started</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Service</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Type</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Price</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Child</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">Infant</th>
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
                                <?php if (!empty($s['description'])): ?>
                                <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs"><?= e($s['description']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($s['destination'])): ?>
                                <div class="text-xs text-purple-400 mt-0.5"><i class="fas fa-map-pin mr-1"></i><?= e($s['destination']) ?></div>
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
                        <?php if ($s['service_type'] === 'tour' && (float)($s['price_adult'] ?? 0) > 0): ?>
                        <span class="font-bold text-gray-800 dark:text-gray-200"><?= number_format((float)$s['price_adult'], 2) ?></span>
                        <?php else: ?>
                        <span class="font-bold text-gray-800 dark:text-gray-200"><?= number_format((float)$s['price'], 2) ?></span>
                        <?php endif; ?>
                        <span class="text-xs text-gray-400 ml-1"><?= $s['currency'] ?></span>
                    </td>
                    <td class="px-5 py-4 text-right text-gray-500">
                        <?php if ((float)($s['price_child'] ?? 0) > 0): ?>
                        <?= number_format((float)$s['price_child'], 2) ?>
                        <?php else: ?>
                        <span class="text-gray-300">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-right text-gray-500">
                        <?php if ((float)($s['price_infant'] ?? 0) > 0): ?>
                        <?= number_format((float)$s['price_infant'], 2) ?>
                        <?php else: ?>
                        <span class="text-gray-300">-</span>
                        <?php endif; ?>
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
                                <?= csrf_field() ?>
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
