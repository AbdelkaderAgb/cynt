<?php
/**
 * Hotel Profile Form — Add/Edit Hotel with Dynamic Room Pricing
 */
$h = $hotel;
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1>
</div>

<form method="POST" action="<?= url('hotels/profiles/store') ?>" class="space-y-6" id="hotelForm">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $h['id'] ?>"><?php endif; ?>

    <!-- Hotel Details Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-hotel text-purple-500 mr-2"></i>Hotel Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Hotel Name *</label>
                <input type="text" name="name" value="<?= e($h['name'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Address</label>
                <input type="text" name="address" value="<?= e($h['address'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">City</label>
                <input type="text" name="city" value="<?= e($h['city'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Country</label>
                <input type="text" name="country" value="<?= e($h['country'] ?? 'Turkey') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Stars</label>
                <select name="stars" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <option value="<?= $s ?>" <?= ($h['stars'] ?? 3) == $s ? 'selected' : '' ?>><?= str_repeat('⭐', $s) ?> (<?= $s ?> Star)</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
                    <option value="active" <?= ($h['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($h['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                <input type="text" name="phone" value="<?= e($h['phone'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                <input type="email" name="email" value="<?= e($h['email'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Website</label>
                <input type="url" name="website" value="<?= e($h['website'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-xl"><?= e($h['description'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Room Types & Pricing -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200"><i class="fas fa-bed text-indigo-500 mr-2"></i>Room Types & Pricing</h3>
            <button type="button" onclick="addRoom()" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition"><i class="fas fa-plus mr-1"></i>Add Room</button>
        </div>

        <div id="roomsContainer" class="space-y-4">
            <?php if (!empty($rooms)): ?>
                <?php foreach ($rooms as $ri => $room): ?>
                <div class="room-row bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300 room-label">Room #<?= $ri + 1 ?></span>
                        <button type="button" onclick="this.closest('.room-row').remove()" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-times-circle"></i> Remove</button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Room Type</label>
                            <input type="text" name="room_type[]" value="<?= e($room['room_type']) ?>" placeholder="Standard, Deluxe..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Capacity</label>
                            <input type="number" name="room_capacity[]" value="<?= $room['capacity'] ?? 2 ?>" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Single</label>
                            <input type="number" name="price_single[]" value="<?= $room['price_single'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Double</label>
                            <input type="number" name="price_double[]" value="<?= $room['price_double'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Triple</label>
                            <input type="number" name="price_triple[]" value="<?= $room['price_triple'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Quad</label>
                            <input type="number" name="price_quad[]" value="<?= $room['price_quad'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">👶 Child</label>
                            <input type="number" name="price_child[]" value="<?= $room['price_child'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Currency</label>
                            <select name="room_currency[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                <?php foreach (['USD','EUR','TRY','GBP','SAR'] as $cur): ?>
                                <option value="<?= $cur ?>" <?= ($room['currency'] ?? 'USD') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Board</label>
                            <select name="board_type[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                <?php
                                $boardTypes = ['RO'=>'Room Only','BB'=>'Bed & Breakfast','HB'=>'Half Board','FB'=>'Full Board','AI'=>'All Inclusive'];
                                foreach ($boardTypes as $bk => $bv): ?>
                                <option value="<?= $bk ?>" <?= ($room['board_type'] ?? 'BB') === $bk ? 'selected' : '' ?>><?= $bv ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Season</label>
                            <select name="season[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                <?php foreach (['all'=>'All Year','summer'=>'Summer','winter'=>'Winter','spring'=>'Spring','autumn'=>'Autumn'] as $sk => $sv): ?>
                                <option value="<?= $sk ?>" <?= ($room['season'] ?? 'all') === $sk ? 'selected' : '' ?>><?= $sv ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (empty($rooms)): ?>
        <div id="noRoomsMsg" class="text-center py-6 text-gray-400">
            <i class="fas fa-bed text-2xl mb-2"></i>
            <p class="text-sm">No rooms added yet. Click "Add Room" to start.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Submit -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
            <i class="fas fa-save mr-1"></i><?= $isEdit ? 'Update Hotel' : 'Save Hotel' ?>
        </button>
        <a href="<?= url('hotels/profiles') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
    </div>
</form>

<script>
let roomCount = <?= count($rooms ?? []) ?>;
function addRoom() {
    roomCount++;
    const noMsg = document.getElementById('noRoomsMsg');
    if (noMsg) noMsg.remove();

    const container = document.getElementById('roomsContainer');
    const html = `
    <div class="room-row bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-600 animate-fade-in-up">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-gray-600 dark:text-gray-300">Room #${roomCount}</span>
            <button type="button" onclick="this.closest('.room-row').remove()" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-times-circle"></i> Remove</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Room Type</label>
                <input type="text" name="room_type[]" placeholder="Standard, Deluxe..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Capacity</label>
                <input type="number" name="room_capacity[]" value="2" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Single</label>
                <input type="number" name="price_single[]" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Double</label>
                <input type="number" name="price_double[]" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Triple</label>
                <input type="number" name="price_triple[]" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">💰 Quad</label>
                <input type="number" name="price_quad[]" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">👶 Child</label>
                <input type="number" name="price_child[]" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Currency</label>
                <select name="room_currency[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                    <option value="USD">USD</option><option value="EUR">EUR</option><option value="TRY">TRY</option><option value="GBP">GBP</option><option value="SAR">SAR</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Board</label>
                <select name="board_type[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                    <option value="RO">Room Only</option><option value="BB" selected>Bed & Breakfast</option><option value="HB">Half Board</option><option value="FB">Full Board</option><option value="AI">All Inclusive</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Season</label>
                <select name="season[]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                    <option value="all">All Year</option><option value="summer">Summer</option><option value="winter">Winter</option><option value="spring">Spring</option><option value="autumn">Autumn</option>
                </select>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}
</script>
