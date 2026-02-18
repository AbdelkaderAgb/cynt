<?php
/**
 * CYN Tourism — Seasonal Pricing Management
 * Manage pricing seasons and per-room-type rates for a hotel.
 */
$hotel     = $hotel ?? [];
$seasons   = $seasons ?? [];
$roomTypes = $roomTypes ?? [];
$hotelId   = $hotel['id'] ?? 0;
?>

<!-- Toast Notification -->
<?php if (!empty($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('saved_successfully') ?>', 'success'));</script>
<?php elseif (!empty($_GET['deleted'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('deleted_successfully') ?>', 'success'));</script>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= __('season') ?> — <?= htmlspecialchars($hotel['name'] ?? '') ?></h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($hotel['city'] ?? '') ?> · <?= htmlspecialchars($hotel['star_rating'] ?? '') ?>★</p>
    </div>
    <div class="flex gap-3">
        <a href="<?= url('hotels/profiles/edit') ?>?id=<?= $hotelId ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left"></i> <?= __('go_back') ?>
        </a>
        <button onclick="openSeasonModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition font-medium">
            <i class="fas fa-plus"></i> <?= __('create') ?> <?= __('season') ?>
        </button>
    </div>
</div>

<!-- Seasons Grid -->
<?php if (empty($seasons)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
        <i class="fas fa-sun text-2xl text-yellow-500"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Seasons Defined</h3>
    <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first pricing season to manage rates by date range.</p>
    <button onclick="openSeasonModal()" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
        <i class="fas fa-plus mr-1"></i> <?= __('create') ?> <?= __('season') ?>
    </button>
</div>
<?php else: ?>
<div class="grid gap-6">
    <?php foreach ($seasons as $season): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden
        <?= $season['is_blackout'] ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-blue-500' ?>">
        <!-- Season Header -->
        <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl <?= $season['is_blackout'] ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30' ?> flex items-center justify-center">
                    <i class="fas <?= $season['is_blackout'] ? 'fa-ban text-red-500' : 'fa-calendar-alt text-blue-500' ?>"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg"><?= htmlspecialchars($season['name']) ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?= date('M d, Y', strtotime($season['date_from'])) ?> → <?= date('M d, Y', strtotime($season['date_to'])) ?>
                        <?php if ($season['is_blackout']): ?>
                            <span class="ml-2 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-medium rounded-full">Blackout</span>
                        <?php endif; ?>
                        <?php if ($season['multiplier'] != 1.0): ?>
                            <span class="ml-2 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-medium rounded-full">×<?= number_format($season['multiplier'], 2) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick='openSeasonModal(<?= json_encode($season) ?>)' class="px-3 py-1.5 text-sm bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                    <i class="fas fa-edit mr-1"></i> <?= __('edit') ?>
                </button>
                <a href="<?= url('hotels/seasons/delete') ?>?id=<?= $season['id'] ?>&hotel_id=<?= $hotelId ?>" 
                   onclick="return confirm('<?= __('confirm_delete') ?>')"
                   class="px-3 py-1.5 text-sm bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                    <i class="fas fa-trash mr-1"></i> <?= __('delete') ?>
                </a>
            </div>
        </div>

        <!-- Rates Table -->
        <?php if (!empty($season['rates'])): ?>
        <div class="border-t border-gray-100 dark:border-gray-700 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('room_type') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('price_single') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('price_double') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('price_triple') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('price_quad') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('price_child') ?></th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300"><?= __('currency') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($season['rates'] as $rate): ?>
                    <tr class="border-t border-gray-50 dark:border-gray-700/50">
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($rate['room_type']) ?></td>
                        <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format($rate['price_single'], 2) ?></td>
                        <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format($rate['price_double'], 2) ?></td>
                        <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format($rate['price_triple'], 2) ?></td>
                        <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format($rate['price_quad'], 2) ?></td>
                        <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300"><?= number_format($rate['price_child'], 2) ?></td>
                        <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-400"><?= htmlspecialchars($rate['currency']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Season Modal -->
<div id="seasonModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm" x-data>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto mx-4">
        <form action="<?= url('hotels/seasons/store') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">
            <input type="hidden" name="season_id" id="modal_season_id" value="">

            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 id="modal_title" class="text-xl font-bold text-gray-900 dark:text-white"><?= __('create') ?> <?= __('season') ?></h2>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                <!-- Season Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Season Name *</label>
                        <input type="text" name="name" id="modal_name" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g. Summer 2026, Peak Season">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('start_date') ?> *</label>
                        <input type="date" name="date_from" id="modal_date_from" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('end_date') ?> *</label>
                        <input type="date" name="date_to" id="modal_date_to" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Multiplier</label>
                        <input type="number" name="multiplier" id="modal_multiplier" step="0.01" min="0" value="1.00"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_blackout" id="modal_is_blackout" class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">🚫 Blackout Period</span>
                        </label>
                    </div>
                </div>

                <!-- Rates per room type -->
                <?php if (!empty($roomTypes)): ?>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Rates per Room Type</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="px-4 py-2.5 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('room_type') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('price_single') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('price_double') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('price_triple') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('price_quad') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('price_child') ?></th>
                                    <th class="px-4 py-2.5 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('currency') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roomTypes as $rt): ?>
                                <tr class="border-t border-gray-100 dark:border-gray-700/50">
                                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($rt['room_type']) ?></td>
                                    <td class="px-2 py-2"><input type="number" step="0.01" min="0" name="rates[<?= $rt['id'] ?>][single]" class="rate-input w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="0"></td>
                                    <td class="px-2 py-2"><input type="number" step="0.01" min="0" name="rates[<?= $rt['id'] ?>][double]" class="rate-input w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="0"></td>
                                    <td class="px-2 py-2"><input type="number" step="0.01" min="0" name="rates[<?= $rt['id'] ?>][triple]" class="rate-input w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="0"></td>
                                    <td class="px-2 py-2"><input type="number" step="0.01" min="0" name="rates[<?= $rt['id'] ?>][quad]" class="rate-input w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="0"></td>
                                    <td class="px-2 py-2"><input type="number" step="0.01" min="0" name="rates[<?= $rt['id'] ?>][child]" class="rate-input w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="0"></td>
                                    <td class="px-2 py-2">
                                        <select name="rates[<?= $rt['id'] ?>][currency]" class="w-full px-2 py-1.5 text-sm text-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="TRY">TRY</option>
                                            <option value="GBP">GBP</option>
                                            <option value="SAR">SAR</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeSeasonModal()" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
                    <?= __('cancel') ?>
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition font-medium">
                    <i class="fas fa-save mr-1"></i> <?= __('save') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSeasonModal(season = null) {
    const modal = document.getElementById('seasonModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    if (season) {
        document.getElementById('modal_title').textContent = '<?= __('edit') ?> <?= __('season') ?>';
        document.getElementById('modal_season_id').value = season.id;
        document.getElementById('modal_name').value = season.name;
        document.getElementById('modal_date_from').value = season.date_from;
        document.getElementById('modal_date_to').value = season.date_to;
        document.getElementById('modal_multiplier').value = season.multiplier;
        document.getElementById('modal_is_blackout').checked = !!parseInt(season.is_blackout);

        // Fill rates
        if (season.rates) {
            season.rates.forEach(r => {
                const prefix = `rates[${r.room_type_id}]`;
                const fields = { single: 'price_single', double: 'price_double', triple: 'price_triple', quad: 'price_quad', child: 'price_child' };
                for (const [k, col] of Object.entries(fields)) {
                    const input = document.querySelector(`[name="${prefix}[${k}]"]`);
                    if (input) input.value = parseFloat(r[col] || 0).toFixed(2);
                }
                const cur = document.querySelector(`[name="${prefix}[currency]"]`);
                if (cur) cur.value = r.currency || 'USD';
            });
        }
    } else {
        document.getElementById('modal_title').textContent = '<?= __('create') ?> <?= __('season') ?>';
        document.getElementById('modal_season_id').value = '';
        document.getElementById('modal_name').value = '';
        document.getElementById('modal_date_from').value = '';
        document.getElementById('modal_date_to').value = '';
        document.getElementById('modal_multiplier').value = '1.00';
        document.getElementById('modal_is_blackout').checked = false;
        document.querySelectorAll('.rate-input').forEach(i => i.value = '0');
    }
}

function closeSeasonModal() {
    const modal = document.getElementById('seasonModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close on backdrop click
document.getElementById('seasonModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeSeasonModal();
});
</script>
