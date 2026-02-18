<?php
/**
 * CYN Tourism — Room Allotment Management
 * Manage allotted rooms per hotel with availability tracking.
 */
$hotel      = $hotel ?? [];
$allotments = $allotments ?? [];
$roomTypes  = $roomTypes ?? [];
$hotelId    = $hotel['id'] ?? 0;
?>

<?php if (!empty($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('saved_successfully') ?>', 'success'));</script>
<?php elseif (!empty($_GET['deleted'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('deleted_successfully') ?>', 'success'));</script>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Allotments — <?= htmlspecialchars($hotel['name'] ?? '') ?></h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($hotel['city'] ?? '') ?> · <?= htmlspecialchars($hotel['star_rating'] ?? '') ?>★</p>
    </div>
    <div class="flex gap-3">
        <a href="<?= url('hotels/profiles/edit') ?>?id=<?= $hotelId ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left"></i> <?= __('go_back') ?>
        </a>
        <button onclick="openAllotModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:shadow-lg transition font-medium">
            <i class="fas fa-plus"></i> Add Allotment
        </button>
    </div>
</div>

<!-- Allotments Table -->
<?php if (empty($allotments)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
        <i class="fas fa-door-open text-2xl text-emerald-500"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Allotments Yet</h3>
    <p class="text-gray-500 dark:text-gray-400 mb-6">Allocate rooms to manage availability and track usage.</p>
    <button onclick="openAllotModal()" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition font-medium">
        <i class="fas fa-plus mr-1"></i> Add Allotment
    </button>
</div>
<?php else: ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('room_type') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('start_date') ?></th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('end_date') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Total</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Used</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Available</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Release Days</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('status') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allotments as $a): ?>
                <?php $avail = max(0, $a['total_rooms'] - $a['used_rooms']); ?>
                <tr class="border-t border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($a['room_type']) ?></td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= date('M d, Y', strtotime($a['date_from'])) ?></td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= date('M d, Y', strtotime($a['date_to'])) ?></td>
                    <td class="px-5 py-3 text-center font-semibold text-gray-900 dark:text-white"><?= $a['total_rooms'] ?></td>
                    <td class="px-5 py-3 text-center text-gray-700 dark:text-gray-300"><?= $a['used_rooms'] ?></td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold 
                            <?= $avail > 3 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                             : ($avail > 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' 
                             : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400') ?>">
                            <?= $avail ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-gray-700 dark:text-gray-300"><?= $a['release_days'] ?></td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                            <?= $a['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                             : ($a['status'] === 'released' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' 
                             : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400') ?>">
                            <?= ucfirst($a['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openAllotModal(<?= json_encode($a) ?>)' class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" title="<?= __('edit') ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?= url('hotels/allotments/delete') ?>?id=<?= $a['id'] ?>&hotel_id=<?= $hotelId ?>" 
                               onclick="return confirm('<?= __('confirm_delete') ?>')"
                               class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="<?= __('delete') ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Allotment Modal -->
<div id="allotModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <form action="<?= url('hotels/allotments/store') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="hotel_id" value="<?= $hotelId ?>">
            <input type="hidden" name="allotment_id" id="allot_id" value="">

            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 id="allot_title" class="text-xl font-bold text-gray-900 dark:text-white">Add Allotment</h2>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('room_type') ?> *</label>
                    <select name="room_type_id" id="allot_room_type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <option value=""><?= __('select') ?></option>
                        <?php foreach ($roomTypes as $rt): ?>
                        <option value="<?= $rt['id'] ?>"><?= htmlspecialchars($rt['room_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('start_date') ?> *</label>
                        <input type="date" name="date_from" id="allot_from" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('end_date') ?> *</label>
                        <input type="date" name="date_to" id="allot_to" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Rooms *</label>
                        <input type="number" name="total_rooms" id="allot_total" min="0" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Release Days</label>
                        <input type="number" name="release_days" id="allot_release" min="0" value="7" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('status') ?></label>
                    <select name="status" id="allot_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="active"><?= __('active') ?></option>
                        <option value="released">Released</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeAllotModal()" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
                    <?= __('cancel') ?>
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:shadow-lg transition font-medium">
                    <i class="fas fa-save mr-1"></i> <?= __('save') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAllotModal(data = null) {
    const m = document.getElementById('allotModal');
    m.classList.remove('hidden');
    m.classList.add('flex');

    if (data) {
        document.getElementById('allot_title').textContent = '<?= __('edit') ?> Allotment';
        document.getElementById('allot_id').value = data.id;
        document.getElementById('allot_room_type').value = data.room_type_id;
        document.getElementById('allot_from').value = data.date_from;
        document.getElementById('allot_to').value = data.date_to;
        document.getElementById('allot_total').value = data.total_rooms;
        document.getElementById('allot_release').value = data.release_days;
        document.getElementById('allot_status').value = data.status;
    } else {
        document.getElementById('allot_title').textContent = 'Add Allotment';
        document.getElementById('allot_id').value = '';
        document.getElementById('allot_room_type').value = '';
        document.getElementById('allot_from').value = '';
        document.getElementById('allot_to').value = '';
        document.getElementById('allot_total').value = '';
        document.getElementById('allot_release').value = '7';
        document.getElementById('allot_status').value = 'active';
    }
}

function closeAllotModal() {
    const m = document.getElementById('allotModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

document.getElementById('allotModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAllotModal();
});
</script>
