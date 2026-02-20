<?php
/**
 * Hotel Vouchers — List View + Full-Screen Create Panel
 * Architecture: card list on desktop/mobile + slide-in panel form with numbered sections.
 */
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','checked_in'=>'Checked In','completed'=>'Completed','cancelled'=>'Cancelled'];
$statusColors = [
    'pending'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'confirmed'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'checked_in' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
    'completed'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'cancelled'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
];
$roomTypes = ['SNG'=>'Single','DBL'=>'Double','TRP'=>'Triple','QUAD'=>'Quad','SUIT'=>'Suite','VILLA'=>'Villa','STUDIO'=>'Studio','APART'=>'Apart'];
$boardTypes = ['room_only'=>'RO','bed_breakfast'=>'BB','half_board'=>'HB','full_board'=>'FB','all_inclusive'=>'AI','ultra_all_inclusive'=>'UAI'];
$transferTypes = ['without'=>'No Transfer','one_way'=>'One Way','round_trip'=>'Round Trip'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 rounded-xl flex items-center gap-2"
     x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">
    <i class="fas fa-check-circle"></i> Hotel voucher saved successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'capacity' && !empty($_GET['message'])): ?>
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i> <?= e(urldecode((string)$_GET['message'])) ?>
</div>
<?php endif; ?>

<!-- ── PAGE HEADER ── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-hotel text-teal-500"></i> Hotel Vouchers
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total ?? 0) ?> records</p>
    </div>
    <button @click="$dispatch('open-hotel-panel')"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> New Hotel Voucher
    </button>
</div>

<!-- ── FILTERS ── -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('hotel-voucher') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none"><i class="fas fa-search text-xs"></i></span>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>"
                       placeholder="Voucher no, guest, hotel…"
                       class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                <option value="">All Statuses</option>
                <?php foreach ($statusLabels as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition flex items-center gap-1.5">
                <i class="fas fa-search text-xs"></i> Filter
            </button>
            <a href="<?= url('hotel-voucher') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition" title="Reset">
                <i class="fas fa-undo"></i>
            </a>
        </div>
    </form>
</div>

<!-- ── DESKTOP TABLE ── -->
<div class="hidden md:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Voucher</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Company / Guest</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hotel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Nights</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pax</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($vouchers)): ?>
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                        <i class="fas fa-hotel text-4xl mb-3 block opacity-30"></i>
                        <p class="text-sm">No hotel vouchers found.</p>
                        <button @click="$dispatch('open-hotel-panel')" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition">
                            <i class="fas fa-plus text-xs"></i> Create First Voucher
                        </button>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($vouchers as $vrow): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3">
                        <a href="<?= url('hotel-voucher/show') ?>?id=<?= $vrow['id'] ?>"
                           class="font-mono font-semibold text-teal-600 hover:underline"><?= e($vrow['voucher_no']) ?></a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-gray-200"><?= e($vrow['company_name']) ?></div>
                        <div class="text-xs text-gray-400 truncate max-w-[140px]"><?= e($vrow['guest_name']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300"><?= e($vrow['hotel_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= !empty($vrow['check_in']) ? date('d/m/Y', strtotime($vrow['check_in'])) : '—' ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 text-xs font-bold"><?= $vrow['nights'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            <span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-[10px] font-bold"><?= $roomTypes[$vrow['room_type']] ?? $vrow['room_type'] ?></span>
                            <?php if (!empty($boardTypes[$vrow['board_type'] ?? ''])): ?>
                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px]"><?= $boardTypes[$vrow['board_type']] ?></span>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">
                        <?= (int)($vrow['adults'] ?? 0) + (int)($vrow['children'] ?? 0) + (int)($vrow['infants'] ?? 0) ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$vrow['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$vrow['status']] ?? $vrow['status'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= url('hotel-voucher/show') ?>?id=<?= $vrow['id'] ?>" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $vrow['id'] ?>" target="_blank" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $vrow['id'] ?>&download=1" class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition" title="Download"><i class="fas fa-download"></i></a>
                            <a href="<?= url('hotel-voucher/edit') ?>?id=<?= $vrow['id'] ?>" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('hotel-voucher/delete') ?>?id=<?= $vrow['id'] ?>"
                               onclick="return confirm('Delete this hotel voucher?')"
                               class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── MOBILE CARDS ── -->
<div class="md:hidden space-y-3">
    <?php foreach ($vouchers as $vrow): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 flex items-center justify-between bg-teal-50 dark:bg-teal-900/20 border-b border-teal-100 dark:border-teal-800">
            <a href="<?= url('hotel-voucher/show') ?>?id=<?= $vrow['id'] ?>" class="font-mono font-bold text-teal-700 dark:text-teal-400 text-sm hover:underline"><?= e($vrow['voucher_no']) ?></a>
            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$vrow['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$vrow['status']] ?? $vrow['status'] ?></span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= e($vrow['company_name']) ?></p>
                    <p class="text-xs text-gray-400 mt-0.5"><?= e($vrow['guest_name']) ?></p>
                </div>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-1 rounded-lg shrink-0">
                    <?= $vrow['nights'] ?> nts
                </span>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-lg px-3 py-2 text-xs text-gray-600 dark:text-gray-300">
                <div class="font-semibold"><?= e($vrow['hotel_name']) ?></div>
                <div class="text-gray-400 mt-0.5">
                    Check-in: <?= !empty($vrow['check_in']) ? date('d/m/Y', strtotime($vrow['check_in'])) : '—' ?>
                    &nbsp;·&nbsp; <?= $roomTypes[$vrow['room_type']] ?? $vrow['room_type'] ?>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <a href="<?= url('hotel-voucher/show') ?>?id=<?= $vrow['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 transition"><i class="fas fa-eye mr-1"></i>View</a>
                <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $vrow['id'] ?>" target="_blank" class="flex-1 py-2 text-center text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 transition"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
                <a href="<?= url('hotel-voucher/edit') ?>?id=<?= $vrow['id'] ?>" class="flex-1 py-2 text-center text-xs font-semibold text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 transition"><i class="fas fa-edit mr-1"></i>Edit</a>
                <a href="<?= url('hotel-voucher/delete') ?>?id=<?= $vrow['id'] ?>" onclick="return confirm('Delete?')" class="py-2 px-3 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if (($pages ?? 1) > 1): ?>
<div class="mt-5 flex items-center justify-between">
    <p class="text-sm text-gray-500 dark:text-gray-400">Page <span class="font-medium"><?= $page ?></span> of <span class="font-medium"><?= $pages ?></span></p>
    <div class="flex gap-1">
        <a href="?page=<?= max(1,$page-1) ?>&search=<?= e($filters['search']??'') ?>&status=<?= e($filters['status']??'') ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">&#8592;</a>
        <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= e($filters['search']??'') ?>&status=<?= e($filters['status']??'') ?>"
           class="px-3 py-1.5 text-sm border rounded-lg transition <?= $i == $page ? 'bg-teal-600 border-teal-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?page=<?= min($pages,$page+1) ?>&search=<?= e($filters['search']??'') ?>&status=<?= e($filters['status']??'') ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition">&#8594;</a>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     FULL-SCREEN SLIDE-IN CREATE PANEL
══════════════════════════════════════════════════════════ -->
<div x-data="hotelVoucherPanel()"
     @open-hotel-panel.window="open()"
     @keydown.escape.window="close()">

    <!-- Backdrop -->
    <div x-show="isOpen" x-transition:enter="transition duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
         @click="close()" style="display:none;"></div>

    <!-- Panel -->
    <div x-show="isOpen"
         x-transition:enter="transition transform duration-300"
         x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform duration-200"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl flex flex-col bg-white dark:bg-gray-900 shadow-2xl"
         style="display:none;">

        <!-- Panel header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-teal-600 to-cyan-600 shrink-0">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-hotel"></i> New Hotel Voucher
                </h2>
                <p class="text-xs text-teal-100 mt-0.5">1 Company · 2 Hotels &amp; Rooms · 3 Guests · 4 Notes</p>
            </div>
            <button @click="close()" class="text-white/70 hover:text-white transition p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto">
            <form method="POST" action="<?= url('hotel-voucher/store') ?>" id="hvCreateForm" @submit.prevent="submitForm($el)">
                <?= csrf_field() ?>
                <input type="hidden" name="hotel_id"   :value="hotels[0]?.hotelId || ''">
                <input type="hidden" name="hotel_name" :value="hotels[0]?.hotelName || ''">
                <input type="hidden" name="company_id" :value="companyId">
                <input type="hidden" name="rooms_json" :value="hotelsJson()">
                <input type="hidden" name="room_count" :value="totalRooms()">
                <input type="hidden" name="room_type"  :value="hotels[0]?.rooms[0]?.type || ''">
                <input type="hidden" name="board_type" :value="hotels[0]?.rooms[0]?.board || ''">
                <input type="hidden" name="check_in"   :value="hotels[0]?.checkIn || ''">
                <input type="hidden" name="check_out"  :value="hotels[0]?.checkOut || ''">
                <input type="hidden" name="nights"     :value="hotels[0]?.nights || 1">
                <input type="hidden" name="adults"     :value="totalAdults()">
                <input type="hidden" name="children"   :value="totalChildren()">
                <input type="hidden" name="customers"  :value="JSON.stringify(guests)">
                <input type="hidden" name="guest_name" :value="guests.length ? ((guests[0].title||'')+' '+(guests[0].name||'')).trim() : ''">
                <input type="hidden" name="passenger_passport" :value="guests.length ? (guests[0].passport||'') : ''">

                <div class="p-6 space-y-6">

                    <!-- ── 1. COMPANY ── -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Company Information</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-1 relative" @click.outside="partnerOpen=false">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Company Name *</label>
                                <input type="text" name="company_name" x-model="companyName"
                                       @input.debounce.300ms="searchPartner()" @focus="if(partnerResults.length) partnerOpen=true"
                                       required autocomplete="off"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                                       placeholder="Search partners…">
                                <div x-show="partnerOpen && partnerResults.length > 0" x-transition
                                     class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                                    <template x-for="r in partnerResults" :key="r.id">
                                        <div @click="selectPartner(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                            <div class="text-xs text-gray-400" x-text="(r.contact_person||'')+(r.phone?' · '+r.phone:'')"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Address</label>
                                <input type="text" name="address" x-model="companyAddress" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Telephone</label>
                                <input type="text" name="telephone" x-model="companyPhone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- ── 2. HOTELS & ROOMS ── -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Hotels &amp; Rooms</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-medium text-gray-500">Transfer:</label>
                                <select name="transfer_type" class="px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs focus:ring-2 focus:ring-teal-500">
                                    <?php foreach ($transferTypes as $k => $lbl): ?>
                                    <option value="<?= $k ?>"><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" @click="addHotel()"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 rounded-lg text-xs font-semibold hover:bg-teal-100 transition">
                                    <i class="fas fa-plus text-[10px]"></i> Add Hotel
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                        <template x-for="(ht, hi) in hotels" :key="hi">
                            <div class="rounded-xl border-2 border-teal-200 dark:border-teal-700 bg-teal-50/40 dark:bg-teal-900/10 overflow-hidden">
                                <!-- Hotel block header -->
                                <div class="flex items-center justify-between px-4 py-2.5 bg-teal-600/10 dark:bg-teal-800/20 border-b border-teal-200 dark:border-teal-700">
                                    <span class="text-xs font-bold text-teal-700 dark:text-teal-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-hotel text-[10px]"></i>
                                        <span x-text="ht.hotelName || ('Hotel ' + (hi+1))"></span>
                                        <span x-show="ht.hotelName" class="font-normal text-teal-500 normal-case tracking-normal" x-text="ht.city ? '· ' + ht.city : ''"></span>
                                    </span>
                                    <button type="button" @click="removeHotel(hi)" x-show="hotels.length > 1"
                                            class="text-red-400 hover:text-red-600 text-xs transition p-1 hover:bg-red-50 dark:hover:bg-red-900/30 rounded">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>

                                <div class="p-4 space-y-3">
                                    <!-- Cascade: Country → City → Hotel -->
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-globe text-blue-400 mr-0.5"></i>Country</label>
                                            <select x-model="ht.country" @change="onHotelCountryChange(hi)"
                                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                                <option value="">-- Country --</option>
                                                <template x-for="c in countries" :key="c"><option :value="c" x-text="c"></option></template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-city text-purple-400 mr-0.5"></i>City</label>
                                            <select x-model="ht.city" @change="onHotelCityChange(hi)" :disabled="!ht.country"
                                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 disabled:opacity-50">
                                                <option value="">-- City --</option>
                                                <template x-for="ci in ht.cities" :key="ci"><option :value="ci" x-text="ci"></option></template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-hotel text-teal-500 mr-0.5"></i>Hotel</label>
                                            <select x-model="ht.hotelId" @change="onHotelSelect(hi)" :disabled="!ht.city"
                                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 disabled:opacity-50">
                                                <option value="">-- Hotel --</option>
                                                <template x-for="h in ht.filteredHotels" :key="h.id">
                                                    <option :value="h.id" x-text="h.name + (h.stars ? ' ' + '★'.repeat(h.stars) : '')"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Check-in / Check-out / Nights — per hotel -->
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-calendar-check text-teal-400 mr-0.5"></i>Check-in *</label>
                                            <input type="date" x-model="ht.checkIn" @change="onHotelCheckInChange(hi)" required
                                                   class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-calendar-times text-red-400 mr-0.5"></i>Check-out *</label>
                                            <input type="date" x-model="ht.checkOut" @change="onHotelCheckOutChange(hi)" required
                                                   class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-gray-500 mb-1"><i class="fas fa-moon text-indigo-400 mr-0.5"></i>Nights</label>
                                            <input type="number" x-model.number="ht.nights" min="1" readonly
                                                   class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-center font-bold text-teal-700 dark:text-teal-400">
                                        </div>
                                    </div>

                                    <!-- Rooms for this hotel -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Rooms</span>
                                            <button type="button" @click="addRoom(hi)"
                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-semibold hover:bg-indigo-100 transition">
                                                <i class="fas fa-plus text-[9px]"></i> Add Room
                                            </button>
                                        </div>
                                        <p x-show="!ht.hotelId" class="text-[10px] text-amber-500 mb-1.5 flex items-center gap-1">
                                            <i class="fas fa-info-circle"></i> Select a hotel above to auto-load room types.
                                        </p>
                                        <div class="space-y-2">
                                            <template x-for="(room, ri) in ht.rooms" :key="ri">
                                                <div class="bg-white dark:bg-gray-700/50 rounded-lg p-2.5 border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-center justify-between mb-1.5">
                                                        <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="'Room ' + (ri+1)"></span>
                                                        <button type="button" @click="ht.rooms.splice(ri,1)" x-show="ht.rooms.length > 1"
                                                                class="text-red-400 hover:text-red-600 text-[10px] transition"><i class="fas fa-times"></i></button>
                                                    </div>
                                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                        <div>
                                                            <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Room Type</label>
                                                            <select x-model="room.type" @change="onRoomTypeChange(hi, ri)"
                                                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs">
                                                                <option value="">-- Select --</option>
                                                                <template x-for="rt in ht.availableRoomTypes" :key="rt"><option :value="rt" x-text="rt"></option></template>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Board</label>
                                                            <select x-model="room.board"
                                                                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs">
                                                                <option value="">-- Select --</option>
                                                                <template x-for="bt in boardsForType(hi, room.type)" :key="bt">
                                                                    <option :value="bt" x-text="boardLabel(bt)"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Adults</label>
                                                            <input type="number" x-model.number="room.adults" min="0" :max="maxAdults(hi, room.type)"
                                                                   class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Children</label>
                                                            <input type="number" x-model.number="room.children" min="0" :max="maxChildren(hi, room.type)"
                                                                   class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        </div>

                        <!-- Totals row -->
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                            <span><strong x-text="totalPax()"></strong> total pax</span>
                            <span>·</span>
                            <span><strong x-text="totalRooms()"></strong> room(s) across <strong x-text="hotels.length"></strong> hotel(s)</span>
                            <span class="flex items-center gap-1 ml-2">
                                <label class="font-medium text-gray-500">Infants:</label>
                                <input type="number" name="infants" x-model.number="infants" min="0"
                                       class="w-14 px-1.5 py-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                            </span>
                        </div>
                    </div>

                    <!-- ── 4. GUESTS ── -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Guests</h3>
                            </div>
                            <button type="button" @click="addGuest()"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg text-xs font-semibold hover:bg-purple-100 transition">
                                <i class="fas fa-plus text-[10px]"></i> Add Guest
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(g, gi) in guests" :key="gi">
                                <div :class="gi===0
                                    ? 'flex gap-2 items-center flex-wrap bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 rounded-xl p-2.5'
                                    : 'flex gap-2 items-center flex-wrap bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-xl p-2.5'">
                                    <select x-model="g.title" class="w-20 px-1.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs shrink-0">
                                        <option>Mr</option><option>Mrs</option><option>Ms</option><option>Miss</option><option>Dr</option><option>Child</option><option>Infant</option>
                                    </select>
                                    <input type="text" x-model="g.name" placeholder="Full Name *" class="flex-1 min-w-[110px] px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                    <input type="number" x-model="g.age" placeholder="Age" min="0" max="120" class="w-14 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                                    <input type="text" x-model="g.passport" placeholder="Passport" class="w-28 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs font-mono tracking-wider">
                                    <button type="button" @click="guests.splice(gi,1)" x-show="guests.length>1"
                                            class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition shrink-0">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5">First guest is used as lead on the voucher PDF.</p>
                    </div>

                    <!-- ── 5. SPECIAL REQUESTS + LINKED SERVICES ── -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-5" x-data="hvLinkServices()">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">4</span>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Notes &amp; Services</h3>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Special Requests</label>
                                <textarea name="special_requests" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                                          placeholder="Room preferences, dietary requirements, accessibility…"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Link Tours / Transfers</label>
                                <p class="text-[10px] text-gray-400 mb-1.5">Linked items appear as Guest Program on the voucher PDF.</p>
                                <div class="relative" @click.outside="svcOpen=false">
                                    <input type="text" x-model="svcQuery" @input.debounce.200ms="searchSvc()" @focus="if(svcResults.length) svcOpen=true"
                                           placeholder="Search tours or transfers…" autocomplete="off"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                    <div x-show="svcOpen && svcResults.length>0" x-transition
                                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                                        <template x-for="r in svcResults" :key="r.type+'-'+r.id">
                                            <div @click="addSvc(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 text-sm transition" x-text="r.label"></div>
                                        </template>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-1" x-show="linked.length>0">
                                    <template x-for="(item,idx) in linked" :key="idx">
                                        <div class="flex items-center justify-between px-3 py-1.5 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-700 rounded-lg text-sm">
                                            <span class="flex-1 truncate text-teal-800 dark:text-teal-300" x-text="item.label"></span>
                                            <button type="button" @click="linked.splice(idx,1)" class="ml-2 text-red-400 hover:text-red-600 transition"><i class="fas fa-times text-xs"></i></button>
                                        </div>
                                    </template>
                                </div>
                                <input type="hidden" name="linked_services" :value="JSON.stringify(linked.map(x=>({type:x.type,id:x.id})))">
                                <textarea name="additional_services" class="hidden"></textarea>
                            </div>
                        </div>
                    </div>

                </div><!-- /form content -->

                <!-- Panel footer (sticky actions) -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="close()"
                            class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submitting" :class="{'opacity-50 cursor-not-allowed':submitting}"
                            class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fas fa-save"></i><span x-text="submitting ? 'Saving…' : 'Generate Voucher'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BOARD_LABELS_HV = {
    BB:'Bed & Breakfast', HB:'Half Board', FB:'Full Board',
    AI:'All Inclusive', RO:'Room Only', UAI:'Ultra All Inclusive',
    bed_breakfast:'Bed & Breakfast', half_board:'Half Board',
    full_board:'Full Board', all_inclusive:'All Inclusive',
    room_only:'Room Only', ultra_all_inclusive:'Ultra All Inclusive'
};

function hotelVoucherPanel() {
    return {
        isOpen: false,

        /* Company */
        companyId:'', companyName:'', companyAddress:'', companyPhone:'',
        partnerResults:[], partnerOpen:false,

        /* All hotels from DB */
        allHotels:[], countries:[],

        /* Hotels array — each entry = one hotel block with its own cascade, dates + rooms */
        hotels:[{
            country:'', city:'', hotelId:'', hotelName:'',
            cities:[], filteredHotels:[],
            hotelRooms:[], availableRoomTypes:[],
            checkIn:'', checkOut:'', nights:1,
            rooms:[{ type:'', board:'', adults:2, children:0 }]
        }],

        /* Infants (global) */
        infants:0,

        /* Guests */
        guests:[{ title:'Mr', name:'', age:'', passport:'' }],

        /* Panel control */
        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
            if (!this.allHotels.length) this.loadHotels();
        },
        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        /* Load all hotels once */
        async loadHotels() {
            try {
                this.allHotels = await fetch('<?= url('api/hotels/list') ?>').then(r=>r.json());
                this.countries = [...new Set(this.allHotels.map(h=>h.country).filter(Boolean))].sort();
            } catch(e) { this.allHotels = []; }
        },

        /* Partner search */
        async searchPartner() {
            if (this.companyName.length < 1) { this.partnerResults=[]; this.partnerOpen=false; return; }
            try {
                this.partnerResults = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.companyName)).then(r=>r.json());
                this.partnerOpen = this.partnerResults.length > 0;
            } catch(e) { this.partnerResults=[]; }
        },
        selectPartner(r) {
            this.companyName = r.company_name; this.companyId = r.id; this.partnerOpen = false;
            if (r.phone)              this.companyPhone   = r.phone;
            if (r.address || r.city)  this.companyAddress = [r.address,r.city,r.country].filter(Boolean).join(', ');
        },

        /* Per-hotel cascade */
        onHotelCountryChange(hi) {
            const ht = this.hotels[hi];
            ht.cities = [...new Set(this.allHotels.filter(h=>h.country===ht.country).map(h=>h.city).filter(Boolean))].sort();
            ht.city=''; ht.filteredHotels=[]; ht.hotelId=''; ht.hotelName='';
            ht.hotelRooms=[]; ht.availableRoomTypes=[];
        },
        onHotelCityChange(hi) {
            const ht = this.hotels[hi];
            ht.filteredHotels = this.allHotels.filter(h=>h.country===ht.country && h.city===ht.city);
            ht.hotelId=''; ht.hotelName=''; ht.hotelRooms=[]; ht.availableRoomTypes=[];
        },
        async onHotelSelect(hi) {
            const ht = this.hotels[hi];
            const h = this.allHotels.find(x=>x.id==ht.hotelId);
            ht.hotelName = h ? h.name : '';
            await this.fetchHotelRooms(hi);
        },
        async fetchHotelRooms(hi) {
            const ht = this.hotels[hi];
            if (!ht.hotelId) { ht.hotelRooms=[]; ht.availableRoomTypes=[]; return; }
            try {
                ht.hotelRooms = await fetch('<?= url('api/hotels/rooms') ?>?hotel_id='+ht.hotelId).then(r=>r.json());
            } catch(e) { ht.hotelRooms=[]; }
            ht.availableRoomTypes = [...new Set(ht.hotelRooms.map(r=>r.room_type))];
            if (ht.availableRoomTypes.length) {
                const ft  = ht.availableRoomTypes[0];
                const bds = this.boardsForType(hi, ft);
                const match = ht.hotelRooms.find(r=>r.room_type===ft);
                ht.rooms = [{ type:ft, board:bds.length?bds[0]:'', adults:parseInt(match?.max_adults)||2, children:0 }];
            }
        },

        /* Add / remove hotel blocks */
        addHotel() {
            this.hotels.push({
                country:'', city:'', hotelId:'', hotelName:'',
                cities:[], filteredHotels:[],
                hotelRooms:[], availableRoomTypes:[],
                checkIn:'', checkOut:'', nights:1,
                rooms:[{ type:'', board:'', adults:2, children:0 }]
            });
        },
        removeHotel(hi) { if (this.hotels.length > 1) this.hotels.splice(hi, 1); },

        /* Room helpers — scoped to hotel index hi */
        boardsForType(hi, roomType) {
            if (!roomType) return [];
            const ht = this.hotels[hi];
            if (!ht?.hotelRooms?.length) return [];
            return [...new Set(ht.hotelRooms.filter(r=>r.room_type===roomType).map(r=>r.board_type))];
        },
        boardLabel(code) { return BOARD_LABELS_HV[code] || code; },
        maxAdults(hi, rt) {
            const m = this.hotels[hi]?.hotelRooms?.find(r=>r.room_type===rt);
            return m ? parseInt(m.max_adults)||10 : 10;
        },
        maxChildren(hi, rt) {
            const m = this.hotels[hi]?.hotelRooms?.find(r=>r.room_type===rt);
            return m ? parseInt(m.max_children)||10 : 10;
        },
        onRoomTypeChange(hi, ri) {
            const ht   = this.hotels[hi];
            const room = ht.rooms[ri];
            const bds  = this.boardsForType(hi, room.type);
            if (bds.length && !bds.includes(room.board)) room.board = bds[0];
            const m = ht.hotelRooms?.find(r=>r.room_type===room.type);
            if (m) {
                if (room.adults   > parseInt(m.max_adults)||2)   room.adults   = parseInt(m.max_adults)||2;
                if (room.children > parseInt(m.max_children)||0) room.children = parseInt(m.max_children)||0;
            }
        },
        addRoom(hi) {
            const ht = this.hotels[hi];
            const ft = ht.availableRoomTypes.length ? ht.availableRoomTypes[0] : '';
            const bds = this.boardsForType(hi, ft);
            ht.rooms.push({ type:ft, board:bds.length?bds[0]:'', adults:2, children:0 });
        },

        /* Aggregate totals */
        totalAdults()   { return this.hotels.reduce((s,ht)=>s+ht.rooms.reduce((rs,r)=>rs+(r.adults||0),0), 0); },
        totalChildren() { return this.hotels.reduce((s,ht)=>s+ht.rooms.reduce((rs,r)=>rs+(r.children||0),0), 0); },
        totalRooms()    { return this.hotels.reduce((s,ht)=>s+ht.rooms.length, 0); },
        totalPax()      { return this.totalAdults()+this.totalChildren()+(this.infants||0); },

        /* Serialise hotels for hidden field */
        hotelsJson() {
            return JSON.stringify(this.hotels.map(ht=>({
                hotel_id:   ht.hotelId,
                hotel_name: ht.hotelName,
                country:    ht.country,
                city:       ht.city,
                checkIn:    ht.checkIn,
                checkOut:   ht.checkOut,
                nights:     ht.nights,
                rooms:      ht.rooms
            })));
        },

        /* Per-hotel date logic */
        onHotelCheckInChange(hi) {
            const ht = this.hotels[hi];
            if (ht.checkIn && ht.checkOut) {
                const d = Math.round((new Date(ht.checkOut)-new Date(ht.checkIn))/86400000);
                if (d>0) ht.nights=d; else { ht.nights=1; ht.checkOut=''; }
            } else if (ht.checkIn && ht.nights>0) {
                const dt = new Date(ht.checkIn); dt.setDate(dt.getDate()+ht.nights);
                ht.checkOut = dt.toISOString().split('T')[0];
            }
        },
        onHotelCheckOutChange(hi) {
            const ht = this.hotels[hi];
            if (ht.checkIn && ht.checkOut) {
                const d = Math.round((new Date(ht.checkOut)-new Date(ht.checkIn))/86400000);
                if (d>0) ht.nights=d; else { ht.nights=1; ht.checkOut=''; }
            }
        },

        /* Guests */
        addGuest() { this.guests.push({title:'Mr',name:'',age:'',passport:''}); },

        /* Submit */
        submitting: false,
        submitForm(form) { this.submitting = true; form.submit(); }
    };
}

function hvLinkServices() {
    return {
        svcQuery:'', svcResults:[], svcOpen:false, linked:[],
        async searchSvc() {
            if (this.svcQuery.length<2) { this.svcResults=[]; this.svcOpen=false; return; }
            try {
                this.svcResults = await fetch('<?= url('api/search-services') ?>?q='+encodeURIComponent(this.svcQuery)).then(r=>r.json());
                this.svcOpen = this.svcResults.length>0;
            } catch(e) { this.svcResults=[]; }
        },
        addSvc(r) {
            if (this.linked.some(x=>x.type===r.type&&x.id===r.id)) return;
            this.linked.push({type:r.type,id:r.id,label:r.label});
            this.svcOpen=false; this.svcQuery=''; this.svcResults=[];
        }
    };
}
</script>
