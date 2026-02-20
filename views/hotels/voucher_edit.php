<?php
/**
 * Hotel Voucher Edit — Aligned with new numbered-section architecture.
 */
$customersRaw = json_decode($v['customers'] ?? '[]', true) ?: [['title'=>'Mr','name'=>'']];
$guestsInit = array_map(fn($g) => [
    'title'    => $g['title']    ?? 'Mr',
    'name'     => $g['name']     ?? '',
    'age'      => $g['age']      ?? '',
        'passport' => $g['passport'] ?? '',
], $customersRaw);

// Resolve hotels init — handle both new multi-hotel format and old flat rooms format
$hotelsInit = [];
if (!empty($v['rooms_json'])) {
    $decoded = json_decode($v['rooms_json'], true);
    if (is_array($decoded) && !empty($decoded)) {
        if (isset($decoded[0]['rooms'])) {
            // New multi-hotel format — ensure every block has checkIn/checkOut/nights
            $hotelsInit = array_map(function($ht, $i) use ($v) {
                if (empty($ht['checkIn']) && $i === 0)  $ht['checkIn']  = $v['check_in']  ?? '';
                if (empty($ht['checkOut']) && $i === 0) $ht['checkOut'] = $v['check_out'] ?? '';
                if (empty($ht['nights']) && $i === 0)   $ht['nights']   = $v['nights'] ?? '';
                return $ht;
            }, $decoded, array_keys($decoded));
        } else {
            // Old flat rooms array — wrap in single hotel block
            $hotelsInit = [[
                'hotel_id'   => $v['hotel_id']   ?? '',
                'hotel_name' => $v['hotel_name'] ?? '',
                'country'    => '',
                'city'       => '',
                'checkIn'    => $v['check_in']   ?? '',
                'checkOut'   => $v['check_out']  ?? '',
                'nights'     => $v['nights']     ?? '',
                'rooms'      => $decoded,
            ]];
        }
    }
}
if (empty($hotelsInit)) {
    $hotelsInit = [[
        'hotel_id'   => $v['hotel_id']   ?? '',
        'hotel_name' => $v['hotel_name'] ?? '',
        'country'    => '',
        'city'       => '',
        'checkIn'    => $v['check_in']   ?? '',
        'checkOut'   => $v['check_out']  ?? '',
        'nights'     => $v['nights']     ?? '',
        'rooms'      => [['type' => $v['room_type'] ?? 'DBL', 'board' => $v['board_type'] ?? 'bed_breakfast', 'adults' => (int)($v['adults'] ?? 2), 'children' => 0]],
    ]];
}

$statusOptions = ['pending'=>'Pending','confirmed'=>'Confirmed','checked_in'=>'Checked In','completed'=>'Completed','cancelled'=>'Cancelled'];
$transferTypes = ['without'=>'Without Transfer','one_way'=>'One Way','round_trip'=>'Round Trip'];
?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'capacity' && !empty($_GET['message'])): ?>
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i> <?= e(urldecode((string)$_GET['message'])) ?>
</div>
<?php endif; ?>

<!-- ── PAGE HEADER ── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-edit text-amber-500"></i> Edit Hotel Voucher
        </h1>
        <p class="text-sm text-gray-500 mt-1 font-mono"><?= e($v['voucher_no']) ?> &nbsp;·&nbsp; <?= e($v['hotel_name']) ?></p>
    </div>
    <a href="<?= url('hotel-voucher/show') ?>?id=<?= $v['id'] ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition shrink-0">
        <i class="fas fa-arrow-left text-xs"></i> Back to Voucher
    </a>
</div>

<form method="POST" action="<?= url('hotel-voucher/update') ?>" class="space-y-5"
      x-data="hotelEditForm()" @submit.prevent="prepareSubmit($el)">
    <?= csrf_field() ?>
    <input type="hidden" name="id"          value="<?= $v['id'] ?>">
    <input type="hidden" name="hotel_id"    :value="hotels[0]?.hotelId || '<?= e($v['hotel_id'] ?? '') ?>'">
    <input type="hidden" name="hotel_name"  :value="hotels[0]?.hotelName || ''">
    <input type="hidden" name="company_id"  :value="companyId">
    <input type="hidden" name="rooms_json"  :value="hotelsJson()">
    <input type="hidden" name="room_count"  :value="totalRooms()">
    <input type="hidden" name="room_type"   :value="hotels[0]?.rooms[0]?.type || ''">
    <input type="hidden" name="board_type"  :value="hotels[0]?.rooms[0]?.board || ''">
    <input type="hidden" name="check_in"    :value="hotels[0]?.checkIn || ''">
    <input type="hidden" name="check_out"   :value="hotels[0]?.checkOut || ''">
    <input type="hidden" name="nights"      :value="hotels[0]?.nights || 1">
    <input type="hidden" name="adults"      :value="totalAdults()">
    <input type="hidden" name="children"    :value="totalChildren()">
    <input type="hidden" name="customers"   :value="JSON.stringify(guests)">
    <input type="hidden" name="guest_name"  :value="guests.length?((guests[0].title||'')+' '+(guests[0].name||'')).trim():''">
    <input type="hidden" name="passenger_passport" :value="guests.length?(guests[0].passport||''):''">

    <!-- ══════════ 1 · COMPANY ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Company Information</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative" @click.outside="partnerOpen=false">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Company Name *</label>
                <input type="text" name="company_name" x-model="companyName"
                       @input.debounce.300ms="searchPartner()" @focus="if(partnerResults.length) partnerOpen=true"
                       required autocomplete="off"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500"
                       placeholder="Search or type…">
                <div x-show="partnerOpen && partnerResults.length>0" x-transition
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
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Address</label>
                <input type="text" name="address" x-model="companyAddress" autocomplete="street-address"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Telephone</label>
                <input type="text" name="telephone" x-model="companyPhone" autocomplete="tel"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
    </div>

    <!-- ══════════ 2 · HOTELS & ROOMS ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Hotels &amp; Rooms</h3>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transfer</label>
                    <select name="transfer_type" class="px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    <?php foreach ($transferTypes as $k => $lbl): ?>
                        <option value="<?= $k ?>" <?= ($v['transfer_type']??'without') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</label>
                    <select name="status" class="px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        <?php foreach ($statusOptions as $k => $lbl): ?>
                        <option value="<?= $k ?>" <?= $v['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
                <button type="button" @click="addHotel()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 rounded-lg text-xs font-semibold hover:bg-teal-100 transition">
                    <i class="fas fa-plus text-[10px]"></i> Add Hotel
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <template x-for="(ht, hi) in hotels" :key="hi">
                <div class="rounded-xl border-2 border-teal-200 dark:border-teal-700 bg-teal-50/40 dark:bg-teal-900/10 overflow-hidden">
                    <!-- Hotel block header -->
                    <div class="flex items-center justify-between px-4 py-2.5 bg-teal-600/10 dark:bg-teal-800/20 border-b border-teal-200 dark:border-teal-700">
                        <span class="text-xs font-bold text-teal-700 dark:text-teal-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-hotel text-[10px]"></i>
                            <span x-text="ht.hotelName || ('Hotel ' + (hi+1))"></span>
                            <span x-show="ht.hotelName && ht.city" class="font-normal text-teal-500 normal-case tracking-normal" x-text="'· ' + ht.city"></span>
                        </span>
                        <button type="button" @click="removeHotel(hi)" x-show="hotels.length > 1"
                                class="text-red-400 hover:text-red-600 text-xs transition p-1 hover:bg-red-50 dark:hover:bg-red-900/30 rounded">
                            <i class="fas fa-trash-alt"></i>
                        </button>
            </div>

                    <div class="p-4 space-y-3">
                        <!-- Cascade: Country → City → Hotel -->
                        <div class="grid grid-cols-3 gap-3">
            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-globe text-blue-400 mr-0.5"></i>Country
                                </label>
                                <select x-model="ht.country" @change="onHotelCountryChange(hi)"
                                        class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                                    <option value="">-- Country --</option>
                                    <template x-for="c in countries" :key="c"><option :value="c" x-text="c"></option></template>
                                </select>
            </div>
            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-city text-purple-400 mr-0.5"></i>City
                                </label>
                                <select x-model="ht.city" @change="onHotelCityChange(hi)" :disabled="!ht.country"
                                        class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 disabled:opacity-50">
                                    <option value="">-- City --</option>
                                    <template x-for="ci in ht.cities" :key="ci"><option :value="ci" x-text="ci"></option></template>
                                </select>
            </div>
            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-hotel text-teal-500 mr-0.5"></i>Hotel
                                </label>
                                <select x-model="ht.hotelId" @change="onHotelSelect(hi)" :disabled="!ht.city"
                                        class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 disabled:opacity-50">
                                    <option value="">-- Hotel --</option>
                                    <template x-for="h in ht.filteredHotels" :key="h.id">
                                        <option :value="h.id" x-text="h.name + (h.stars ? ' ' + '★'.repeat(h.stars) : '')"></option>
                                    </template>
                </select>
            </div>
                        </div>

                        <!-- Check-in / Check-out / Nights — per hotel -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-calendar-check text-teal-400 mr-0.5"></i>Check-in *
                                </label>
                                <input type="date" x-model="ht.checkIn" @change="onHotelCheckInChange(hi)" required
                                       class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-calendar-times text-red-400 mr-0.5"></i>Check-out *
                                </label>
                                <input type="date" x-model="ht.checkOut" @change="onHotelCheckOutChange(hi)" required
                                       class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    <i class="fas fa-moon text-indigo-400 mr-0.5"></i>Nights
                                </label>
                                <input type="number" x-model.number="ht.nights" min="1" readonly
                                       class="w-full px-2.5 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-center font-bold text-teal-700 dark:text-teal-400">
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
                                <i class="fas fa-info-circle"></i> Select a hotel above to auto-load room types from database.
                            </p>
                            <div class="space-y-2">
                                <template x-for="(room, ri) in ht.rooms" :key="ri">
                                    <div class="bg-white dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase" x-text="'Room ' + (ri+1)"></span>
                                            <button type="button" @click="ht.rooms.splice(ri,1)" x-show="ht.rooms.length > 1"
                                                    class="text-red-400 hover:text-red-600 text-[10px] transition p-1"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                                                <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Room Type</label>
                                                <select x-model="room.type" @change="onRoomTypeChange(hi, ri)"
                                                        class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <option value="">-- Select --</option>
                                                    <template x-for="rt in ht.availableRoomTypes" :key="rt">
                                    <option :value="rt" x-text="rt"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                                                <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Board</label>
                                                <select x-model="room.board"
                                                        class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <option value="">-- Select --</option>
                                                    <template x-for="bt in boardsForType(hi, room.type)" :key="bt">
                                    <option :value="bt" x-text="boardLabel(bt)"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                                                <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Adults</label>
                                                <input type="number" x-model.number="room.adults" min="0" :max="maxAdults(hi, room.type)"
                                                       class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                        </div>
                        <div>
                                                <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Children</label>
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

            <!-- Totals row -->
            <div class="flex items-center gap-4 text-xs text-gray-500 pt-1 border-t border-gray-100 dark:border-gray-700">
            <span><strong x-text="totalPax()"></strong> total pax</span>
                <span>·</span>
                <span><strong x-text="totalRooms()"></strong> room(s) across <strong x-text="hotels.length"></strong> hotel(s)</span>
                <span class="flex items-center gap-2 ml-2">
                    <label class="font-medium">Infants:</label>
                    <input type="number" name="infants" x-model.number="infants" min="0"
                           class="w-14 px-1.5 py-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                </span>
        </div>
        </div>
    </div>

    <!-- ══════════ 4 · GUESTS ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">4</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Guests</h3>
            </div>
            <button type="button" @click="addGuest()"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg text-xs font-semibold hover:bg-purple-100 transition">
                <i class="fas fa-plus text-[10px]"></i> Add Guest
            </button>
        </div>
        <div class="p-6 space-y-2">
            <template x-for="(g, gi) in guests" :key="gi">
                <div :class="gi===0
                    ? 'flex gap-2 items-center flex-wrap bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 rounded-xl p-3'
                    : 'flex gap-2 items-center flex-wrap bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-xl p-3'">
                    <select x-model="g.title" class="w-20 px-1.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs shrink-0">
                        <option>Mr</option><option>Mrs</option><option>Ms</option><option>Miss</option><option>Dr</option><option>Child</option><option>Infant</option>
                    </select>
                    <input type="text" x-model="g.name" placeholder="Full Name *" autocomplete="name"
                           class="flex-1 min-w-[110px] px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    <input type="number" x-model="g.age" placeholder="Age" min="0" max="120"
                           class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                    <input type="text" x-model="g.passport" placeholder="Passport No."
                           class="w-32 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs font-mono tracking-wider">
                    <button type="button" @click="guests.splice(gi,1)" x-show="guests.length>1"
                            class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition shrink-0">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </template>
            <p class="text-[10px] text-gray-400 mt-1">First guest is shown as Lead on the voucher PDF.</p>
        </div>
    </div>

    <!-- ══════════ 5 · NOTES + LINKED SERVICES ══════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="linkServicesFormEdit(<?= htmlspecialchars(json_encode($linkedServicesForEdit ?? []), ENT_QUOTES, 'UTF-8') ?>)">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">5</span>
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Notes &amp; Services</h3>
    </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Special Requests</label>
                <textarea name="special_requests" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 resize-none"
                          placeholder="Room preferences, dietary, accessibility…"><?= e($v['special_requests'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Link Tours / Transfers</label>
                <p class="text-[10px] text-gray-400 mb-2">Linked services appear as Guest Program on the PDF.</p>
                <div class="relative" @click.outside="serviceResultsOpen=false">
                    <input type="text" x-model="serviceQuery" @input.debounce.200ms="searchServices()" @focus="if(serviceResults.length) serviceResultsOpen=true"
                           placeholder="Search tours or transfers…" autocomplete="off"
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    <div x-show="serviceResultsOpen && serviceResults.length>0" x-transition
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                        <template x-for="r in serviceResults" :key="r.type+'-'+r.id">
                            <div @click="addService(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 text-sm transition" x-text="r.label"></div>
                        </template>
        </div>
                </div>
                <div class="mt-2 space-y-1" x-show="linkedList.length>0">
                    <template x-for="(item,idx) in linkedList" :key="idx">
                        <div class="flex items-center justify-between px-3 py-1.5 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-700 rounded-lg text-sm">
                            <span class="flex-1 truncate text-teal-800 dark:text-teal-300" x-text="item.label"></span>
                            <button type="button" @click="removeService(idx)" class="ml-2 text-red-400 hover:text-red-600 transition"><i class="fas fa-times text-xs"></i></button>
                </div>
            </template>
                </div>
                <input type="hidden" name="linked_services" :value="JSON.stringify(linkedList.map(x=>({type:x.type,id:x.id})))">
                <textarea name="additional_services" class="hidden"></textarea>
            </div>
        </div>
    </div>

    <!-- ── ACTIONS ── -->
    <div class="flex items-center justify-end gap-3 pt-1">
        <a href="<?= url('hotel-voucher/show') ?>?id=<?= $v['id'] ?>"
           class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
            Cancel
        </a>
        <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $v['id'] ?>" target="_blank"
           class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition flex items-center gap-2">
            <i class="fas fa-file-pdf text-red-500"></i> Preview PDF
        </a>
        <button type="submit"
                class="px-7 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-save"></i> Update Voucher
        </button>
    </div>
</form>

<script>
const BOARD_LABELS_HVE = {
    BB:'Bed & Breakfast',HB:'Half Board',FB:'Full Board',AI:'All Inclusive',RO:'Room Only',UAI:'Ultra All Inclusive',
    bed_breakfast:'Bed & Breakfast',half_board:'Half Board',full_board:'Full Board',
    all_inclusive:'All Inclusive',room_only:'Room Only',ultra_all_inclusive:'Ultra All Inclusive'
};

function hotelEditForm() {
    return {
        /* Company */
        companyId:      '<?= e($v['company_id'] ?? '') ?>',
        companyName:    '<?= e($v['company_name'] ?? '') ?>',
        companyAddress: '<?= e($v['address'] ?? '') ?>',
        companyPhone:   '<?= e($v['telephone'] ?? '') ?>',
        partnerResults: [], partnerOpen: false,

        /* All hotels for cascade */
        allHotels: [], countries: [],

        /* Hotels array — initialised from saved rooms_json */
        hotels: (function() {
            const init   = <?= json_encode($hotelsInit) ?>;
            const dbCi   = '<?= $v['check_in']  ?? '' ?>';
            const dbCo   = '<?= $v['check_out'] ?? '' ?>';
            const dbNts  = <?= (int)($v['nights'] ?? 1) ?>;
            return init.map((ht, idx) => ({
                hotelId:           String(ht.hotel_id || ''),
                hotelName:         ht.hotel_name || '',
                country:           ht.country || '',
                city:              ht.city || '',
                cities:            [],
                filteredHotels:    [],
                hotelRooms:        [],
                availableRoomTypes:[],
                checkIn:           ht.checkIn  || (idx === 0 ? dbCi  : ''),
                checkOut:          ht.checkOut || (idx === 0 ? dbCo  : ''),
                nights:            ht.nights   || (idx === 0 ? dbNts : 1),
                rooms:             Array.isArray(ht.rooms) ? ht.rooms : [{ type:'', board:'', adults:2, children:0 }]
            }));
        })(),

        infants: <?= (int)($v['infants'] ?? 0) ?>,
        guests:  <?= json_encode($guestsInit) ?>,

        /* ── Lifecycle ── */
        async init() {
            await this.loadHotels();
            for (let hi = 0; hi < this.hotels.length; hi++) {
                await this.restoreHotelCascade(hi);
            }
        },

        /* Load all hotels once */
        async loadHotels() {
            try {
                this.allHotels = await fetch('<?= url('api/hotels/list') ?>').then(r=>r.json());
                this.countries = [...new Set(this.allHotels.map(h=>h.country).filter(Boolean))].sort();
            } catch(e) { this.allHotels=[]; }
        },

        /* Restore cascade dropdowns + room types for a saved hotel block */
        async restoreHotelCascade(hi) {
            const ht = this.hotels[hi];
            let matched = null;
            if (ht.hotelId) matched = this.allHotels.find(h => String(h.id) === String(ht.hotelId));
            if (!matched && ht.hotelName) matched = this.allHotels.find(h => h.name === ht.hotelName);
            if (matched) {
                ht.hotelId   = String(matched.id);
                ht.hotelName = matched.name;
                if (!ht.country) ht.country = matched.country || '';
                if (!ht.city)    ht.city    = matched.city    || '';
            }
            if (ht.country) {
                ht.cities = [...new Set(this.allHotels.filter(h=>h.country===ht.country).map(h=>h.city).filter(Boolean))].sort();
            }
            if (ht.country && ht.city) {
                ht.filteredHotels = this.allHotels.filter(h=>h.country===ht.country && h.city===ht.city);
            }
            if (ht.hotelId) await this.fetchHotelRooms(hi);
        },

        /* Per-hotel cascade handlers */
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
            const h = this.allHotels.find(x => String(x.id) === String(ht.hotelId));
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
            // Preserve any existing room types not in DB (backward compat)
            for (const room of ht.rooms) {
                if (room.type && !ht.availableRoomTypes.includes(room.type)) {
                    ht.availableRoomTypes.push(room.type);
                }
            }
        },

        /* Hotel block management */
        addHotel() {
            this.hotels.push({
                hotelId:'', hotelName:'', country:'', city:'',
                cities:[], filteredHotels:[], hotelRooms:[], availableRoomTypes:[],
                checkIn:'', checkOut:'', nights:1,
                rooms:[{ type:'', board:'', adults:2, children:0 }]
            });
        },
        removeHotel(hi) { if (this.hotels.length > 1) this.hotels.splice(hi,1); },

        /* Room helpers — scoped to hotel index hi */
        boardsForType(hi, roomType) {
            if (!roomType) return [];
            const ht = this.hotels[hi];
            if (!ht?.hotelRooms?.length) return [];
            return [...new Set(ht.hotelRooms.filter(r=>r.room_type===roomType).map(r=>r.board_type))];
        },
        boardLabel(code) { return BOARD_LABELS_HVE[code] || code; },
        maxAdults(hi, rt)   { const m=this.hotels[hi]?.hotelRooms?.find(r=>r.room_type===rt); return m?parseInt(m.max_adults)||10:10; },
        maxChildren(hi, rt) { const m=this.hotels[hi]?.hotelRooms?.find(r=>r.room_type===rt); return m?parseInt(m.max_children)||10:10; },
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
            const ht  = this.hotels[hi];
            const ft  = ht.availableRoomTypes.length ? ht.availableRoomTypes[0] : '';
            const bds = this.boardsForType(hi, ft);
            ht.rooms.push({ type:ft, board:bds.length?bds[0]:'', adults:2, children:0 });
        },

        /* Aggregate totals */
        totalAdults()   { return this.hotels.reduce((s,ht)=>s+ht.rooms.reduce((rs,r)=>rs+(r.adults||0),0), 0); },
        totalChildren() { return this.hotels.reduce((s,ht)=>s+ht.rooms.reduce((rs,r)=>rs+(r.children||0),0), 0); },
        totalRooms()    { return this.hotels.reduce((s,ht)=>s+ht.rooms.length, 0); },
        totalPax()      { return this.totalAdults()+this.totalChildren()+(this.infants||0); },

        /* Serialise for hidden field */
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

        /* Partner search */
        async searchPartner() {
            if (this.companyName.length<1) { this.partnerResults=[]; this.partnerOpen=false; return; }
            try {
                this.partnerResults=await fetch('<?= url('api/partners/search') ?>?q='+encodeURIComponent(this.companyName)).then(r=>r.json());
                this.partnerOpen=this.partnerResults.length>0;
            } catch(e) { this.partnerResults=[]; }
        },
        selectPartner(r) {
            this.companyName=r.company_name; this.companyId=r.id; this.partnerOpen=false;
            if (r.phone) this.companyPhone=r.phone;
            if (r.address||r.city) this.companyAddress=[r.address,r.city,r.country].filter(Boolean).join(', ');
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

        addGuest() { this.guests.push({title:'Mr',name:'',age:'',passport:''}); },
        prepareSubmit(form) { form.submit(); }
    };
}

function linkServicesFormEdit(initialList) {
    return {
        serviceQuery:'', serviceResults:[], serviceResultsOpen:false,
        linkedList: Array.isArray(initialList) ? initialList.map(x=>({type:x.type,id:x.id,label:x.label||(x.type+' #'+x.id)})) : [],
        async searchServices() {
            if (this.serviceQuery.length<2) { this.serviceResults=[]; this.serviceResultsOpen=false; return; }
            try {
                this.serviceResults=await fetch('<?= url('api/search-services') ?>?q='+encodeURIComponent(this.serviceQuery)).then(r=>r.json());
                this.serviceResultsOpen=this.serviceResults.length>0;
            } catch(e) { this.serviceResults=[]; }
        },
        addService(r) {
            if (this.linkedList.some(x=>x.type===r.type&&x.id===r.id)) return;
            this.linkedList.push({type:r.type,id:r.id,label:r.label});
            this.serviceResultsOpen=false; this.serviceQuery=''; this.serviceResults=[];
        },
        removeService(idx) { this.linkedList.splice(idx,1); }
    };
}
</script>
