<?php /** Hotel Profiles — List View */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-hotel text-purple-500 mr-2"></i>Hotel Profiles</h1>
        <p class="text-sm text-gray-500 mt-1">Manage hotel details, rooms & pricing</p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition"><i class="fas fa-file-excel mr-1"></i>Import XLSX</button>
        <a href="<?= url('hotels/profiles/create') ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition"><i class="fas fa-plus mr-1"></i>Add Hotel</a>
    </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm"><i class="fas fa-check-circle mr-1"></i>Hotel saved successfully!</div>
<?php elseif (isset($_GET['deleted'])): ?>
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 text-sm"><i class="fas fa-trash mr-1"></i>Hotel deleted.</div>
<?php elseif (isset($_GET['imported'])): ?>
<div class="mb-4 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-blue-700 dark:text-blue-300 text-sm"><i class="fas fa-file-import mr-1"></i><?= intval($_GET['imported']) ?> room(s) imported successfully!</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>Error: <?= e($_GET['error']) ?></div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?= url('hotels/profiles') ?>" class="flex gap-3 items-end flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Hotel name, city..."
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm">
        </div>
        <div class="w-32">
            <label class="block text-xs font-medium text-gray-500 mb-1">Stars</label>
            <select name="stars" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl text-sm">
                <option value="">All</option>
                <?php for ($s = 1; $s <= 5; $s++): ?>
                <option value="<?= $s ?>" <?= $stars == $s ? 'selected' : '' ?>><?= str_repeat('⭐', $s) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold"><i class="fas fa-search mr-1"></i>Filter</button>
        <?php if ($search || $stars): ?>
        <a href="<?= url('hotels/profiles') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Hotel Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($hotels as $h): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all group">
        <!-- Hotel Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-5 py-4 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-bold"><?= e($h['name']) ?></h3>
                    <p class="text-sm text-purple-200 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i><?= e($h['city'] ?? '') ?>, <?= e($h['country'] ?? '') ?></p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-white/20 text-xs font-bold">
                    <?= $h['stars'] ?? 0 ?>⭐
                </span>
            </div>
        </div>

        <!-- Hotel Details -->
        <div class="p-5">
            <?php if ($h['address']): ?>
            <p class="text-xs text-gray-400 mb-3"><i class="fas fa-location-dot mr-1"></i><?= e($h['address']) ?></p>
            <?php endif; ?>

            <div class="flex items-center gap-4 mb-4">
                <div class="flex-1 text-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-lg font-bold text-purple-600"><?= (int)($h['room_count'] ?? 0) ?></p>
                    <p class="text-[10px] text-gray-400 uppercase">Room Types</p>
                </div>
                <div class="flex-1 text-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-lg font-bold text-emerald-600"><?= $h['min_price'] ? number_format($h['min_price'], 0) : '—' ?></p>
                    <p class="text-[10px] text-gray-400 uppercase">Min Price</p>
                </div>
                <div class="flex-1 text-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-lg font-bold text-amber-600"><?= $h['max_price'] ? number_format($h['max_price'], 0) : '—' ?></p>
                    <p class="text-[10px] text-gray-400 uppercase">Max Price</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <?php if ($h['phone']): ?>
                <span class="text-xs text-gray-400"><i class="fas fa-phone mr-1"></i><?= e($h['phone']) ?></span>
                <?php endif; ?>
                <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= ($h['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($h['status'] ?? 'active') ?></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex gap-2">
            <a href="<?= url('hotels/profiles/edit') ?>?id=<?= $h['id'] ?>" class="flex-1 text-center px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-semibold hover:bg-indigo-200 transition"><i class="fas fa-edit mr-1"></i>Edit</a>
            <form method="POST" action="<?= url('hotels/profiles/delete') ?>" onsubmit="return confirm('Delete this hotel and all its rooms?')" class="flex-1">
                <input type="hidden" name="id" value="<?= $h['id'] ?>">
                <button class="w-full px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-xs font-semibold hover:bg-red-200 transition"><i class="fas fa-trash mr-1"></i>Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($hotels)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 bg-purple-50 dark:bg-purple-900/20 rounded-full flex items-center justify-center"><i class="fas fa-hotel text-2xl text-purple-400"></i></div>
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No Hotels Yet</h3>
    <p class="text-sm text-gray-400 mb-4">Add your first hotel or import from an XLSX file.</p>
    <div class="flex gap-2 justify-center">
        <a href="<?= url('hotels/profiles/create') ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold"><i class="fas fa-plus mr-1"></i>Add Hotel</a>
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold"><i class="fas fa-file-excel mr-1"></i>Import XLSX</button>
    </div>
</div>
<?php endif; ?>

<!-- XLSX Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white"><i class="fas fa-file-excel text-emerald-500 mr-2"></i>Import Hotels from XLSX</h3>
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= url('hotels/profiles/import') ?>" enctype="multipart/form-data" class="p-6">
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Upload an XLSX file with the following columns:</p>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 text-xs text-gray-500 dark:text-gray-400 font-mono leading-relaxed">
                    Hotel Name | Address | City | Country | Stars | Room Type | Capacity | Single | Double | Triple | Quad | Child | Currency | Board | Season
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-2">Select XLSX File</label>
                <input type="file" name="xlsx_file" accept=".xlsx" required
                       class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition"><i class="fas fa-upload mr-1"></i>Import</button>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>
