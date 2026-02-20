<?php
/* ── Reference maps ── */
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','checked_in'=>'Checked In','checked_out'=>'Checked Out','cancelled'=>'Cancelled','no_show'=>'No Show'];
$statusColors = [
    'pending'     => 'bg-amber-100 text-amber-700 border-amber-200',
    'confirmed'   => 'bg-blue-100 text-blue-700 border-blue-200',
    'checked_in'  => 'bg-teal-100 text-teal-700 border-teal-200',
    'checked_out' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'cancelled'   => 'bg-red-100 text-red-700 border-red-200',
    'no_show'     => 'bg-gray-100 text-gray-600 border-gray-200',
];

$roomTypeLabels = [
    'SNG'=>'Single','DBL'=>'Double','TRP'=>'Triple','QUAD'=>'Quad',
    'SUIT'=>'Suite','VILLA'=>'Villa','STUDIO'=>'Studio','APART'=>'Apart',
    'standard'=>'Standard','superior'=>'Superior','deluxe'=>'Deluxe',
    'suite'=>'Suite','family'=>'Family','economy'=>'Economy',
];
$boardLabels = [
    'room_only'=>'Room Only','bed_breakfast'=>'Bed & Breakfast',
    'half_board'=>'Half Board','full_board'=>'Full Board',
    'all_inclusive'=>'All Inclusive','ultra_all_inclusive'=>'Ultra All Inclusive',
    'RO'=>'Room Only','BB'=>'Bed & Breakfast','HB'=>'Half Board',
    'FB'=>'Full Board','AI'=>'All Inclusive','UAI'=>'Ultra All Inclusive',
];
$boardColors = [
    'room_only'=>'bg-gray-100 text-gray-600','RO'=>'bg-gray-100 text-gray-600',
    'bed_breakfast'=>'bg-blue-100 text-blue-700','BB'=>'bg-blue-100 text-blue-700',
    'half_board'=>'bg-teal-100 text-teal-700','HB'=>'bg-teal-100 text-teal-700',
    'full_board'=>'bg-purple-100 text-purple-700','FB'=>'bg-purple-100 text-purple-700',
    'all_inclusive'=>'bg-amber-100 text-amber-700','AI'=>'bg-amber-100 text-amber-700',
    'ultra_all_inclusive'=>'bg-orange-100 text-orange-700','UAI'=>'bg-orange-100 text-orange-700',
];
$transferLabels = ['without'=>'Without Transfer','one_way'=>'One Way','round_trip'=>'Round Trip'];

$customers = json_decode($v['customers'] ?? '[]', true) ?: [];

/* ── Parse hotels/rooms from rooms_json ── */
$hotelsData = [];
$rooms      = [];
if (!empty($v['rooms_json'])) {
    $decoded = json_decode($v['rooms_json'], true) ?: [];
    if (!empty($decoded)) {
        if (isset($decoded[0]['rooms'])) {
            $hotelsData = $decoded;
            foreach ($hotelsData as $ht) {
                foreach ((array)($ht['rooms'] ?? []) as $r) $rooms[] = $r;
            }
        } else {
            $rooms = $decoded;
        }
    }
}
$isMultiHotel = count($hotelsData) > 1;

/* Build render blocks the same way as the PDF */
$renderBlocks = !empty($hotelsData) ? $hotelsData : [[
    'hotel_name' => $v['hotel_name'] ?? '',
    'city'       => '',
    'checkIn'    => $v['check_in'] ?? '',
    'checkOut'   => $v['check_out'] ?? '',
    'nights'     => $v['nights'] ?? 0,
    'rooms'      => $rooms,
]];

$st = $v['status'] ?? 'pending';
$totalAdults   = (int)($v['adults']   ?? 0);
$totalChildren = (int)($v['children'] ?? 0);
$totalInfants  = (int)($v['infants']  ?? 0);
?>

<?php if (isset($_GET['updated'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle mr-1"></i> Voucher updated successfully.
</div>
<?php endif; ?>

<div x-data="{ showShare: false, shareTab: 'email', sending: false, sent: false, error: '' }">

<!-- ══ TITLE BAR ══ -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-hotel text-teal-500"></i>
            <?= e($v['voucher_no']) ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $statusColors[$st] ?? 'bg-gray-100 text-gray-600 border-gray-200' ?>">
                <?= $statusLabels[$st] ?? ucwords(str_replace('_',' ',$st)) ?>
            </span>
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            <?= e($v['guest_name']) ?>
            &nbsp;·&nbsp; <?= $totalAdults ?>A / <?= $totalChildren ?>C / <?= $totalInfants ?>I
            <?php if ($isMultiHotel): ?>
            &nbsp;·&nbsp; <?= count($hotelsData) ?> hotels
            <?php else: ?>
            &nbsp;·&nbsp; <?= e($v['hotel_name']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $v['id'] ?>" target="_blank"
           class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <button @click="showShare = true"
           class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5">
            <i class="fas fa-share-alt"></i> Share
        </button>
        <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $v['id'] ?>&download=1"
           class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5">
            <i class="fas fa-download"></i> Download
        </a>
        <a href="<?= url('hotel-invoice/create') ?>?voucher_id=<?= $v['id'] ?>"
           class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5">
            <i class="fas fa-file-invoice-dollar"></i> Create Invoice
        </a>
        <a href="<?= url('hotel-voucher/edit') ?>?id=<?= $v['id'] ?>"
           class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition flex items-center gap-1.5">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="<?= url('hotel-voucher') ?>"
           class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<!-- ═══════════════════════════════════════
     MAIN COLUMN
═══════════════════════════════════════ -->
<div class="lg:col-span-2 space-y-6">

    <!-- BOOKING DETAILS -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-building text-blue-500"></i> Booking Details
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Travel Agency / Company</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['company_name'] ?: '—') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Lead Guest</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['guest_name'] ?: '—') ?></p>
            </div>
            <?php if (!empty($v['passenger_passport'])): ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5"><i class="fas fa-passport text-amber-400 mr-1"></i>Passport / ID</p>
                <p class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200"><?= e($v['passenger_passport']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['telephone'])): ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Phone</p>
                <p class="font-medium text-gray-700 dark:text-gray-300"><?= e($v['telephone']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['address'])): ?>
            <div class="md:col-span-2">
                <p class="text-xs text-gray-400 mb-0.5">Address</p>
                <p class="font-medium text-gray-700 dark:text-gray-300"><?= e($v['address']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <!-- Transfer info inline -->
        <?php $tr = $v['transfer_type'] ?? 'without'; if ($tr !== 'without'): ?>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-4 text-sm">
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Transfer</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= htmlspecialchars($transferLabels[$tr] ?? $tr) ?></span>
            </div>
            <?php if (!empty($v['transfer_flight'])): ?>
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Flight</span>
                <span class="font-mono font-semibold text-gray-700 dark:text-gray-200">✈ <?= e($v['transfer_flight']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['transfer_pickup'])): ?>
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Pickup</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= e($v['transfer_pickup']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['transfer_dropoff'])): ?>
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Drop-off</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= e($v['transfer_dropoff']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['transfer_date'])): ?>
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Transfer Date</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= date('d M Y', strtotime($v['transfer_date'])) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($v['transfer_time'])): ?>
            <div>
                <span class="text-xs text-gray-400 block mb-0.5">Transfer Time</span>
                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= e($v['transfer_time']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- HOTELS & ROOMS — one card per hotel -->
    <?php foreach ($renderBlocks as $hIdx => $htBlock):
        $htRooms  = (array)($htBlock['rooms'] ?? []);
        $htCi     = $htBlock['checkIn']  ?? $htBlock['check_in']  ?? '';
        $htCo     = $htBlock['checkOut'] ?? $htBlock['check_out'] ?? '';
        $htNights = (int)($htBlock['nights'] ?? 0);
        if (!$htNights && $htCi && $htCo) {
            $htNights = max(1, (int)round((strtotime($htCo) - strtotime($htCi)) / 86400));
        }
        $htName = $htBlock['hotel_name'] ?? ('Hotel ' . ($hIdx + 1));
        $htCity = $htBlock['city'] ?? '';
    ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Hotel header -->
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-slate-800 to-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center">
                    <i class="fas fa-hotel text-amber-400 text-base"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white text-base leading-tight"><?= e($htName) ?></h3>
                    <?php if ($htCity): ?><p class="text-xs text-slate-400 mt-0.5"><?= e($htCity) ?></p><?php endif; ?>
                </div>
            </div>
            <?php if ($htCi || $htNights): ?>
            <div class="flex items-center gap-3 text-right">
                <?php if ($htCi && $htCo): ?>
                <div class="text-xs text-slate-300 leading-relaxed">
                    <div class="text-amber-300 font-semibold"><?= date('d M Y', strtotime($htCi)) ?></div>
                    <div class="flex items-center gap-1 text-slate-400"><span>→</span><span class="text-slate-200"><?= date('d M Y', strtotime($htCo)) ?></span></div>
                </div>
                <?php elseif ($htCi): ?>
                <div class="text-xs text-slate-300">
                    <div class="text-amber-300 font-semibold">Check-in</div>
                    <div><?= date('d M Y', strtotime($htCi)) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($htNights): ?>
                <div class="text-center bg-amber-400/20 rounded-xl px-3 py-2">
                    <div class="text-2xl font-bold text-amber-400 leading-none"><?= $htNights ?></div>
                    <div class="text-xs text-slate-400 mt-0.5">night<?= $htNights !== 1 ? 's' : '' ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Rooms table -->
        <div class="p-0">
            <?php if (empty($htRooms)): ?>
            <p class="text-center text-gray-400 py-6 text-sm italic">No room data recorded.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Room Type</th>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Board Plan</th>
                            <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Adults</th>
                            <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Children</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        <?php foreach ($htRooms as $ri => $room):
                            $rtKey   = strtoupper(trim($room['type'] ?? ''));
                            $bdKey   = $room['board'] ?? '';
                            $rtLabel = $roomTypeLabels[$rtKey] ?? $roomTypeLabels[strtolower($rtKey)] ?? ucwords(strtolower($rtKey)) ?: '—';
                            $bdLabel = $boardLabels[$bdKey] ?? ucwords(str_replace('_', ' ', $bdKey)) ?: '—';
                            $bdCls   = $boardColors[$bdKey] ?? 'bg-gray-100 text-gray-600';
                        ?>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">
                            <td class="px-4 py-3 text-xs font-bold text-gray-400 text-center"><?= $ri + 1 ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded"><?= htmlspecialchars($rtKey ?: '—') ?></span>
                                    <span class="text-gray-700 dark:text-gray-200 font-medium"><?= htmlspecialchars($rtLabel) ?></span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $bdCls ?>"><?= htmlspecialchars($bdLabel) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-800 dark:text-gray-200"><?= (int)($room['adults'] ?? 0) ?></td>
                            <td class="px-4 py-3 text-center font-bold text-gray-800 dark:text-gray-200"><?= (int)($room['children'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- GUESTS LIST -->
    <?php if (!empty($customers)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-users text-purple-500"></i> Guests
            <span class="ml-auto text-xs font-semibold text-gray-500"><?= count($customers) ?> guest<?= count($customers) !== 1 ? 's' : '' ?></span>
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-400 uppercase w-10">#</th>
                        <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Title</th>
                        <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Name</th>
                        <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Type</th>
                        <th class="px-3 py-2.5 text-center text-xs font-bold text-gray-400 uppercase">Age</th>
                        <th class="px-3 py-2.5 text-left text-xs font-bold text-gray-400 uppercase">Passport / ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <?php foreach ($customers as $ci => $c):
                        $cType = strtolower($c['type'] ?? 'adult');
                        $typeCls = $cType === 'adult' ? 'bg-blue-50 text-blue-600' : ($cType === 'child' ? 'bg-green-50 text-green-600' : 'bg-pink-50 text-pink-600');
                    ?>
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">
                        <td class="px-3 py-2.5 text-xs font-bold text-gray-400 text-center"><?= $ci + 1 ?></td>
                        <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400"><?= e($c['title'] ?? '—') ?></td>
                        <td class="px-3 py-2.5">
                            <span class="font-semibold text-gray-800 dark:text-gray-200"><?= e($c['name'] ?? '') ?></span>
                            <?php if ($ci === 0): ?>
                            <span class="ml-1.5 inline-block text-xs px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded font-semibold">Lead</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2.5">
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded <?= $typeCls ?>"><?= ucfirst($cType) ?></span>
                        </td>
                        <td class="px-3 py-2.5 text-center font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($c['age'] ?? '—') ?></td>
                        <td class="px-3 py-2.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                            <?= !empty($c['passport']) ? e($c['passport']) : '<span class="text-gray-300 italic not-italic font-sans">—</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- GUEST PROGRAM -->
    <?php if (!empty($guestProgram)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-route text-indigo-500"></i> Guest Program
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase">Time</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase">Service</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase">Pickup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <?php foreach ($guestProgram as $row): ?>
                    <tr>
                        <td class="px-3 py-2"><?= e($row['date'] ?? '—') ?></td>
                        <td class="px-3 py-2"><?= e($row['time'] ?? '—') ?></td>
                        <td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-200"><?= e($row['service'] ?? '—') ?></td>
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400"><?= e($row['pickup'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- SPECIAL REQUESTS / NOTES -->
    <?php if (!empty($v['special_requests'])): ?>
    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700/30 rounded-2xl p-6">
        <h3 class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-3 flex items-center gap-2">
            <i class="fas fa-sticky-note"></i> Special Requests / Notes
        </h3>
        <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed"><?= nl2br(e($v['special_requests'])) ?></p>
    </div>
    <?php endif; ?>

</div><!-- /main col -->

<!-- ═══════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════ -->
<div class="space-y-6">

    <!-- STATUS & PAX SUMMARY -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
            <i class="fas fa-info-circle text-blue-500 mr-1"></i> Summary
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Status</span>
                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full border <?= $statusColors[$st] ?? 'bg-gray-100 text-gray-600 border-gray-200' ?>">
                    <?= $statusLabels[$st] ?? ucwords(str_replace('_',' ',$st)) ?>
                </span>
            </div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Voucher No.</span><span class="font-mono font-bold text-gray-800 dark:text-gray-200"><?= e($v['voucher_no']) ?></span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Rooms</span><span class="font-semibold text-gray-800 dark:text-gray-200"><?= $v['room_count'] ?? count($rooms) ?></span></div>
            <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Adults</span><span class="font-semibold text-gray-800 dark:text-gray-200"><?= $totalAdults ?></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Children</span><span class="font-semibold text-gray-800 dark:text-gray-200"><?= $totalChildren ?></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Infants</span><span class="font-semibold text-gray-800 dark:text-gray-200"><?= $totalInfants ?></span></div>
                <div class="flex justify-between pt-1 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-500">Total Pax</span>
                    <span class="text-xl font-bold text-gray-800 dark:text-white"><?= $v['total_pax'] ?? ($totalAdults + $totalChildren + $totalInfants) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- STAY OVERVIEW (from first hotel or DB) -->
    <?php
    $firstHt = $renderBlocks[0] ?? [];
    $showCi  = $firstHt['checkIn']  ?? $firstHt['check_in']  ?? ($v['check_in'] ?? '');
    $showCo  = $firstHt['checkOut'] ?? $firstHt['check_out'] ?? ($v['check_out'] ?? '');
    $showN   = (int)($firstHt['nights'] ?? $v['nights'] ?? 0);
    if (!$showN && $showCi && $showCo) {
        $showN = max(1, (int)round((strtotime($showCo) - strtotime($showCi)) / 86400));
    }
    ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
            <i class="fas fa-calendar-alt text-teal-500 mr-1"></i> Stay Overview
        </h3>
        <div class="space-y-3">
            <?php if ($showCi): ?>
            <div class="flex justify-between items-start">
                <span class="text-sm text-gray-500">Check-in</span>
                <div class="text-right">
                    <div class="font-bold text-gray-800 dark:text-gray-200 text-sm"><?= date('d M Y', strtotime($showCi)) ?></div>
                    <div class="text-xs text-gray-400"><?= date('l', strtotime($showCi)) ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($showCo): ?>
            <div class="flex justify-between items-start">
                <span class="text-sm text-gray-500">Check-out</span>
                <div class="text-right">
                    <div class="font-bold text-gray-800 dark:text-gray-200 text-sm"><?= date('d M Y', strtotime($showCo)) ?></div>
                    <div class="text-xs text-gray-400"><?= date('l', strtotime($showCo)) ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($showN): ?>
            <div class="flex justify-between items-center pt-1 border-t border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500">Duration</span>
                <span class="text-xl font-bold text-slate-800 dark:text-white"><?= $showN ?> <span class="text-sm font-normal text-gray-500">night<?= $showN !== 1 ? 's' : '' ?></span></span>
            </div>
            <?php endif; ?>
            <?php if ($isMultiHotel): ?>
            <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                <p class="text-xs text-gray-400 mb-2">All Hotels</p>
                <?php foreach ($hotelsData as $hb):
                    $hbN = (int)($hb['nights'] ?? 0);
                    $hbCi = $hb['checkIn'] ?? '';
                    $hbCo = $hb['checkOut'] ?? '';
                    if (!$hbN && $hbCi && $hbCo) $hbN = max(1, (int)round((strtotime($hbCo)-strtotime($hbCi))/86400));
                ?>
                <div class="flex items-start gap-2 mb-2 last:mb-0">
                    <span class="text-amber-500 mt-0.5 flex-shrink-0"><i class="fas fa-hotel text-xs"></i></span>
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-800 dark:text-gray-200 text-xs truncate"><?= e($hb['hotel_name'] ?? '—') ?></div>
                        <?php if ($hbCi): ?>
                        <div class="text-xs text-gray-400"><?= date('d M', strtotime($hbCi)) ?><?= $hbCo ? ' → '.date('d M', strtotime($hbCo)) : '' ?><?= $hbN ? ' · '.$hbN.'N' : '' ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DANGER ZONE -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-200 dark:border-red-700/30 p-6">
        <h3 class="text-xs font-bold text-red-400 uppercase tracking-widest mb-3">
            <i class="fas fa-exclamation-triangle mr-1"></i> Danger Zone
        </h3>
        <a href="<?= url('hotel-voucher/delete') ?>?id=<?= $v['id'] ?>"
           onclick="return confirm('Are you sure you want to delete this voucher? This cannot be undone.')"
           class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-semibold hover:bg-red-100 transition">
            <i class="fas fa-trash"></i> Delete Voucher
        </a>
    </div>

</div><!-- /sidebar -->
</div><!-- /grid -->

<!-- ══ SHARE MODAL ══ -->
<div x-show="showShare" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showShare = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6" @click.outside="showShare = false">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-share-alt text-blue-500"></i> Share Document
            </h2>
            <button @click="showShare = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex gap-2 mb-5">
            <button @click="shareTab = 'email'; error = ''; sent = false"
                :class="shareTab === 'email' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button @click="shareTab = 'whatsapp'; error = ''; sent = false"
                :class="shareTab === 'whatsapp' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
        </div>
        <div x-show="sent" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span x-text="shareTab === 'email' ? 'Email sent successfully!' : 'Redirecting to WhatsApp...'"></span>
        </div>
        <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <span x-text="error"></span>
        </div>
        <!-- Email tab -->
        <form x-show="shareTab === 'email'" @submit.prevent="
            sending = true; error = ''; sent = false;
            fetch('<?= url('export/email') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ type: 'hotel_voucher', id: '<?= $v['id'] ?>', email: $refs.emailTo.value, subject: $refs.emailSubject.value, message: $refs.emailMessage.value })
            }).then(r => r.json()).then(d => { sending = false; if(d.success) sent = true; else error = d.message || 'Failed.'; }).catch(() => { sending = false; error = 'Network error.'; });
        " class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recipient Email</label>
                <input x-ref="emailTo" type="email" required placeholder="recipient@example.com"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Subject</label>
                <input x-ref="emailSubject" type="text"
                    value="<?= htmlspecialchars($v['voucher_no']) ?> — <?= htmlspecialchars(COMPANY_NAME) ?>"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message (optional)</label>
                <textarea x-ref="emailMessage" rows="3" placeholder="Additional message..."
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <button type="submit" :disabled="sending"
                class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2">
                <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                <span x-text="sending ? 'Sending...' : 'Send Email with PDF'"></span>
            </button>
        </form>
        <!-- WhatsApp tab -->
        <form x-show="shareTab === 'whatsapp'" @submit.prevent="
            const phone = $refs.waPhone.value.replace(/[^0-9]/g, '');
            window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent('🏨 Hotel Voucher: <?= $v['voucher_no'] ?>\n🏢 <?= addslashes($v['company_name']) ?>\n🏷 <?= addslashes($v['hotel_name']) ?>\n📅 <?= !empty($v['check_in']) ? date('d/m/Y', strtotime($v['check_in'])) : '' ?> - <?= !empty($v['check_out']) ? date('d/m/Y', strtotime($v['check_out'])) : '' ?>\n👥 <?= $v['total_pax'] ?> Pax\n🏢 <?= addslashes(COMPANY_NAME) ?>'), '_blank');
            sent = true;
        " class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number (with country code)</label>
                <input x-ref="waPhone" type="tel" placeholder="+905551234567"
                    class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <p class="text-xs text-gray-400">Leave empty to open WhatsApp without a specific recipient.</p>
            <button type="submit"
                class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp text-lg"></i> Share via WhatsApp
            </button>
        </form>
    </div>
</div>

</div><!-- /x-data -->
