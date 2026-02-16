<?php
/**
 * Partner Portal — Voucher Detail
 */
$vType = $voucher['voucher_type'] ?? 'transfer';
$typeIcons = ['transfer' => 'fa-car-side', 'hotel' => 'fa-hotel', 'tour' => 'fa-map-marked-alt'];
$typeColors = ['transfer' => 'blue', 'hotel' => 'purple', 'tour' => 'emerald'];
$icon = $typeIcons[$vType] ?? 'fa-receipt';
$color = $typeColors[$vType] ?? 'gray';
?>
<div class="mb-6 flex items-center gap-3">
    <a href="<?= url('portal/vouchers') ?>" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:bg-gray-200">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <span class="text-[10px] uppercase font-bold text-<?= $color ?>-600 tracking-wider"><?= ucfirst($vType) ?> Voucher</span>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= e($voucher['voucher_no'] ?? $voucher['tour_code'] ?? '') ?></h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <?php if ($vType === 'transfer'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div><p class="text-xs text-gray-400 uppercase mb-1">Pickup</p><p class="font-semibold"><?= e($voucher['pickup_location'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Dropoff</p><p class="font-semibold"><?= e($voucher['dropoff_location'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Date</p><p class="font-semibold"><?= e($voucher['pickup_date'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Time</p><p class="font-semibold"><?= e($voucher['pickup_time'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Hotel</p><p class="font-semibold"><?= e($voucher['hotel_name'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Flight</p><p class="font-semibold"><?= e($voucher['flight_number'] ?? '—') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Total Pax</p><p class="font-semibold"><?= $voucher['total_pax'] ?? 0 ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Transfer Type</p><p class="font-semibold"><?= ucfirst(str_replace('_', ' ', $voucher['transfer_type'] ?? '')) ?></p></div>
            </div>
        <?php elseif ($vType === 'hotel'): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div><p class="text-xs text-gray-400 uppercase mb-1">Hotel</p><p class="font-semibold"><?= e($voucher['hotel_name'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Guest Name</p><p class="font-semibold"><?= e($voucher['guest_name'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Check In</p><p class="font-semibold"><?= e($voucher['check_in'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Check Out</p><p class="font-semibold"><?= e($voucher['check_out'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Room Type</p><p class="font-semibold"><?= e($voucher['room_type'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Board</p><p class="font-semibold"><?= ucfirst(str_replace('_', ' ', $voucher['board_type'] ?? '')) ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Rooms</p><p class="font-semibold"><?= $voucher['room_count'] ?? 1 ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Nights</p><p class="font-semibold"><?= $voucher['nights'] ?? 1 ?></p></div>
            </div>
        <?php else: /* tour */ ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div><p class="text-xs text-gray-400 uppercase mb-1">Tour</p><p class="font-semibold"><?= e($voucher['tour_name'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Date</p><p class="font-semibold"><?= e($voucher['tour_date'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Destination</p><p class="font-semibold"><?= e($voucher['destination'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Duration</p><p class="font-semibold"><?= $voucher['duration_days'] ?? 1 ?> days</p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Total Pax</p><p class="font-semibold"><?= $voucher['total_pax'] ?? 0 ?></p></div>
                <div><p class="text-xs text-gray-400 uppercase mb-1">Hotel</p><p class="font-semibold"><?= e($voucher['hotel_name'] ?? '—') ?></p></div>
            </div>
        <?php endif; ?>

        <?php $notes = $voucher['special_requests'] ?? $voucher['notes'] ?? ''; if ($notes): ?>
            <div class="mt-6 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Special Requests / Notes</p>
                <p class="text-sm"><?= nl2br(e($notes)) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between mb-2">
                <span class="text-sm text-gray-500">Status</span>
                <?php
                    $st = $voucher['status'] ?? 'pending';
                    $stColors = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-emerald-100 text-emerald-700', 'completed' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700'];
                    $stc = $stColors[$st] ?? 'bg-gray-100 text-gray-600';
                ?>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $stc ?>"><?= ucfirst($st) ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-sm text-gray-500">Payment</span>
                <?php
                    $ps = $voucher['payment_status'] ?? 'unpaid';
                    $psc = $ps === 'paid' ? 'text-emerald-600' : 'text-amber-600';
                ?>
                <span class="text-sm font-bold <?= $psc ?>"><?= ucfirst($ps) ?></span>
            </div>
            <div class="border-t pt-3 mt-3 flex justify-between">
                <span class="font-bold">Total</span>
                <span class="font-bold text-lg text-blue-600"><?= number_format($voucher['total_price'] ?? $voucher['price'] ?? 0, 2) ?> <?= e($voucher['currency'] ?? 'USD') ?></span>
            </div>
        </div>
    </div>
</div>
