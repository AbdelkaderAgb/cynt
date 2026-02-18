<?php
/**
 * Group File Create/Edit Form
 */
$isEdit = !empty($group);
$g = $group ?? [];
$existingItems = json_encode($items ?: [], JSON_UNESCAPED_UNICODE);
?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-folder-open mr-2 text-violet-500"></i>
                <?= $isEdit ? (__('edit_group_file') ?: 'Edit Group File') : (__('new_group_file') ?: 'New Group File') ?>
            </h1>
            <?php if ($isEdit): ?>
            <p class="text-sm text-gray-500 mt-1"><?= e($g['file_number']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= url('group-files') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i> <?= __('back') ?: 'Back' ?>
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? url('group-files/update') : url('group-files/store') ?>" id="groupFileForm" class="space-y-6">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <?php endif; ?>
        <input type="hidden" name="items_json" id="itemsJsonField" value='<?= e($existingItems) ?>'>

        <!-- Group Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-users mr-2 text-violet-400"></i><?= __('group_info') ?: 'Group Information' ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('group_name') ?: 'Group Name' ?> *</label>
                    <input type="text" name="group_name" value="<?= e($g['group_name'] ?? '') ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('partner') ?: 'Partner / Agency' ?></label>
                    <select name="partner_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value=""><?= __('none') ?: '— None —' ?></option>
                        <?php foreach ($partners as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ((int)($g['partner_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isEdit): ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?: 'Status' ?></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <?php foreach (['planning','confirmed','in_progress','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($g['status'] ?? 'planning') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('leader_name') ?: 'Group Leader Name' ?></label>
                    <input type="text" name="leader_name" value="<?= e($g['leader_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('leader_phone') ?: 'Leader Phone' ?></label>
                    <input type="text" name="leader_phone" value="<?= e($g['leader_phone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
            </div>
        </div>

        <!-- Dates & Pax -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-calendar-alt mr-2 text-blue-400"></i><?= __('dates_pax') ?: 'Dates & Passengers' ?></h3>
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('arrival') ?: 'Arrival' ?></label>
                    <input type="date" name="arrival_date" value="<?= e($g['arrival_date'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('departure') ?: 'Departure' ?></label>
                    <input type="date" name="departure_date" value="<?= e($g['departure_date'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('total_pax') ?: 'Total Pax' ?></label>
                    <input type="number" name="total_pax" value="<?= (int)($g['total_pax'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('adults') ?: 'Adults' ?></label>
                    <input type="number" name="adults" value="<?= (int)($g['adults'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('children') ?: 'Children' ?></label>
                    <input type="number" name="children" value="<?= (int)($g['children'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('infants') ?: 'Infants' ?></label>
                    <input type="number" name="infants" value="<?= (int)($g['infants'] ?? 0) ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                </div>
            </div>
        </div>

        <!-- Linked Items Builder -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="groupItemsBuilder(<?= e($existingItems) ?>)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><i class="fas fa-link mr-2 text-green-400"></i><?= __('linked_bookings') ?: 'Linked Bookings' ?></h3>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-100 transition">
                    <i class="fas fa-plus"></i> <?= __('add_booking') ?: 'Add Booking' ?>
                </button>
            </div>

            <template x-if="items.length === 0">
                <p class="text-center text-gray-400 py-8"><i class="fas fa-link text-3xl mb-2 block"></i><?= __('no_linked_bookings') ?: 'No linked bookings yet.' ?></p>
            </template>

            <template x-for="(item, idx) in items" :key="idx">
                <div class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-xl mb-2 bg-gray-50 dark:bg-gray-700/30">
                    <div class="w-16">
                        <label class="block text-[10px] font-medium text-gray-400 mb-1">Day</label>
                        <input type="number" x-model.number="item.day_number" min="1" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                    </div>
                    <div class="w-40">
                        <label class="block text-[10px] font-medium text-gray-400 mb-1">Type</label>
                        <select x-model="item.item_type" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                            <option value="hotel_voucher">Hotel Voucher</option>
                            <option value="tour">Tour</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="block text-[10px] font-medium text-gray-400 mb-1">Ref ID</label>
                        <input type="number" x-model.number="item.reference_id" min="1" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-medium text-gray-400 mb-1">Notes</label>
                        <input type="text" x-model="item.notes" placeholder="Optional notes..." class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    </div>
                    <button type="button" @click="removeItem(idx)" class="p-1.5 text-red-400 hover:text-red-600 transition mt-4"><i class="fas fa-times"></i></button>
                </div>
            </template>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-sticky-note mr-1"></i><?= __('notes') ?: 'Notes' ?></label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500"><?= e($g['notes'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?= url('group-files') ?>" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold text-sm hover:bg-gray-200 transition"><?= __('cancel') ?: 'Cancel' ?></a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><?= $isEdit ? (__('update') ?: 'Update') : (__('create') ?: 'Create') ?>
            </button>
        </div>
    </form>
</div>

<script>
function groupItemsBuilder(existing) {
    return {
        items: existing || [],
        addItem() {
            this.items.push({
                day_number: this.items.length > 0 ? this.items[this.items.length-1].day_number : 1,
                item_type: 'hotel_voucher',
                reference_id: 0,
                notes: ''
            });
            this.syncJson();
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
            this.syncJson();
        },
        syncJson() {
            document.getElementById('itemsJsonField').value = JSON.stringify(this.items);
        },
        init() {
            this.$watch('items', () => this.syncJson(), { deep: true });
        }
    }
}
document.getElementById('groupFileForm').addEventListener('submit', function() {
    const el = document.querySelector('[x-data]');
    if (el && el.__x) {
        document.getElementById('itemsJsonField').value = JSON.stringify(el.__x.$data.items || []);
    }
});
</script>
