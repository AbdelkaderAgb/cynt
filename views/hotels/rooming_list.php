<?php
/**
 * CYN Tourism — Rooming List
 * Guest list editor for hotel vouchers with dynamic rows.
 */
$voucher = $voucher ?? [];
$guests  = $guests ?? [];
$voucherId = $voucher['id'] ?? 0;
?>

<?php if (!empty($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('saved_successfully') ?>', 'success'));</script>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rooming List</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Voucher #<?= htmlspecialchars($voucher['voucher_no'] ?? '') ?> · <?= htmlspecialchars($voucher['hotel_name'] ?? '') ?>
            · <?= htmlspecialchars($voucher['customer_name'] ?? '') ?>
        </p>
    </div>
    <div class="flex gap-3">
        <a href="<?= url('hotel-voucher/show') ?>?id=<?= $voucherId ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left"></i> <?= __('go_back') ?>
        </a>
        <a href="<?= url('hotels/rooming-list/export') ?>?voucher_id=<?= $voucherId ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-xl hover:bg-green-200 dark:hover:bg-green-900/40 transition">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>
</div>

<!-- Rooming List Form -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700" x-data="roomingList()">
    <form action="<?= url('hotels/rooming-list/store') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="voucher_id" value="<?= $voucherId ?>">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300 w-6">#</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('guest_name') ?> *</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('passport_no') ?></th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nationality</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Room No</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('room_type') ?></th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Check-in</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Check-out</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('notes') ?></th>
                        <th class="px-3 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in rows" :key="idx">
                        <tr class="border-t border-gray-100 dark:border-gray-700/50">
                            <td class="px-3 py-2 text-gray-400" x-text="idx + 1"></td>
                            <td class="px-1 py-2"><input type="text" :name="'guest_name[' + idx + ']'" x-model="row.name" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="text" :name="'passport_no[' + idx + ']'" x-model="row.passport" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="text" :name="'nationality[' + idx + ']'" x-model="row.nationality" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="text" :name="'room_number[' + idx + ']'" x-model="row.room" class="w-24 px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="text" :name="'room_type[' + idx + ']'" x-model="row.type" class="w-24 px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="date" :name="'check_in[' + idx + ']'" x-model="row.checkIn" class="w-36 px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="date" :name="'check_out[' + idx + ']'" x-model="row.checkOut" class="w-36 px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-1 py-2"><input type="text" :name="'guest_notes[' + idx + ']'" x-model="row.notes" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></td>
                            <td class="px-2 py-2"><button type="button" @click="removeRow(idx)" class="p-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><i class="fas fa-times"></i></button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <button type="button" @click="addRow()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition font-medium">
                <i class="fas fa-plus"></i> Add Guest
            </button>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="rows.length + ' guests'"></span>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition font-medium">
                    <i class="fas fa-save mr-1"></i> <?= __('save') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function roomingList() {
    const existing = <?= json_encode(array_map(fn($g) => [
        'name'        => $g['guest_name'],
        'passport'    => $g['passport_no'],
        'nationality' => $g['nationality'],
        'room'        => $g['room_number'],
        'type'        => $g['room_type'],
        'checkIn'     => $g['check_in'] ?? '',
        'checkOut'    => $g['check_out'] ?? '',
        'notes'       => $g['notes'] ?? '',
    ], $guests)) ?>;

    return {
        rows: existing.length ? existing : [{ name:'', passport:'', nationality:'', room:'', type:'', checkIn:'', checkOut:'', notes:'' }],
        addRow() {
            this.rows.push({ name:'', passport:'', nationality:'', room:'', type:'', checkIn:'', checkOut:'', notes:'' });
        },
        removeRow(idx) {
            if (this.rows.length > 1) this.rows.splice(idx, 1);
        }
    };
}
</script>
