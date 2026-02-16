<?php
/**
 * Partner Portal — All Vouchers (Transfer + Hotel + Tour)
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-receipt text-emerald-500 mr-2"></i>My Vouchers</h1>
    <p class="text-sm text-gray-500 mt-1"><?= $total ?> vouchers found</p>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?= url('portal/vouchers') ?>" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search vouchers..."
               class="flex-1 min-w-[200px] px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
        <select name="type" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            <option value="">All Types</option>
            <option value="transfer" <?= $type === 'transfer' ? 'selected' : '' ?>>Transfer</option>
            <option value="hotel" <?= $type === 'hotel' ? 'selected' : '' ?>>Hotel</option>
            <option value="tour" <?= $type === 'tour' ? 'selected' : '' ?>>Tour</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
    </form>
</div>

<!-- Voucher Cards -->
<?php if (empty($vouchers)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-receipt text-4xl mb-3"></i>
        <p>No vouchers found</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($vouchers as $v): ?>
            <?php
                $vType = $v['voucher_type'] ?? 'transfer';
                $typeIcons = ['transfer' => 'fa-car-side', 'hotel' => 'fa-hotel', 'tour' => 'fa-map-marked-alt'];
                $typeColors = ['transfer' => 'blue', 'hotel' => 'purple', 'tour' => 'emerald'];
                $icon = $typeIcons[$vType] ?? 'fa-receipt';
                $color = $typeColors[$vType] ?? 'gray';

                // Get display fields based on type
                if ($vType === 'transfer') {
                    $title = ($v['pickup_location'] ?? '') . ' → ' . ($v['dropoff_location'] ?? '');
                    $date = $v['pickup_date'] ?? '';
                    $ref = $v['voucher_no'] ?? '';
                    $guest = $v['company_name'] ?? '';
                } elseif ($vType === 'hotel') {
                    $title = $v['hotel_name'] ?? '';
                    $date = ($v['check_in'] ?? '') . ' - ' . ($v['check_out'] ?? '');
                    $ref = $v['voucher_no'] ?? '';
                    $guest = $v['guest_name'] ?? '';
                } else {
                    $title = $v['tour_name'] ?? '';
                    $date = $v['tour_date'] ?? '';
                    $ref = $v['tour_code'] ?? '';
                    $guest = $v['company_name'] ?? '';
                }

                $statusBadge = '';
                $st = $v['status'] ?? 'pending';
                $stColors = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-emerald-100 text-emerald-700', 'completed' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700'];
                $stc = $stColors[$st] ?? 'bg-gray-100 text-gray-600';
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-<?= $color ?>-100 dark:bg-<?= $color ?>-900/30 rounded-xl flex items-center justify-center">
                            <i class="fas <?= $icon ?> text-<?= $color ?>-600"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-<?= $color ?>-600 tracking-wider"><?= ucfirst($vType) ?></span>
                            <p class="font-semibold text-sm text-gray-800 dark:text-white"><?= e($ref) ?></p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $stc ?>"><?= ucfirst($st) ?></span>
                </div>

                <p class="text-sm text-gray-700 dark:text-gray-300 mb-1 font-medium"><?= e($title) ?></p>
                <?php if ($guest): ?>
                    <p class="text-xs text-gray-500"><i class="fas fa-user mr-1"></i><?= e($guest) ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-calendar mr-1"></i><?= e($date) ?></p>
                <p class="text-xs text-gray-400"><i class="fas fa-users mr-1"></i><?= $v['total_pax'] ?? 0 ?> pax</p>

                <div class="mt-3 flex items-center justify-between border-t pt-3 border-gray-100 dark:border-gray-700">
                    <span class="font-bold text-sm">
                        <?= number_format($v['total_price'] ?? $v['price'] ?? 0, 2) ?> <?= e($v['currency'] ?? 'USD') ?>
                    </span>
                    <a href="<?= url("portal/vouchers/view?id={$v['id']}&type=$vType") ?>" class="text-blue-500 text-sm hover:underline">
                        View Details →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
