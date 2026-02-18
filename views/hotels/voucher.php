<?php
/**
 * Hotel Vouchers View — Matching reference system fields
 */
$statusLabels = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'checked_in' => 'Checked In', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
$statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'checked_in' => 'bg-cyan-100 text-cyan-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700'];

$roomTypes = ['SNG' => 'Single (SNG)', 'DBL' => 'Double (DBL)', 'TRP' => 'Triple (TRP)', 'QUAD' => 'Quad (QUAD)', 'SUIT' => 'Suite (SUIT)', 'VILLA' => 'Villa (VILLA)', 'STUDIO' => 'Studio (STUDIO)', 'APART' => 'Apart (APART)'];
$boardTypes = ['room_only' => 'Room Only', 'bed_breakfast' => 'Bed & Breakfast', 'half_board' => 'Half Board', 'full_board' => 'Full Board', 'all_inclusive' => 'All Inclusive', 'ultra_all_inclusive' => 'Ultra All Inclusive'];
$transferTypes = ['without' => 'Without Transfer', 'one_way' => 'One Way', 'round_trip' => 'Round Trip'];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Hotel voucher saved successfully
</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'capacity' && !empty($_GET['message'])): ?>
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i> <?= e(urldecode((string)$_GET['message'])) ?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('hotel_voucher') ?: 'Hotel Vouchers' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> records found</p>
    </div>
    <button onclick="document.getElementById('newHotelModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> New Hotel Voucher
    </button>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('hotel-voucher') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Voucher no, guest, hotel..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value="">All</option>
                <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition"><i class="fas fa-search mr-1"></i>Filter</button>
            <a href="<?= url('hotel-voucher') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Voucher No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hotel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Check-in</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Nights</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Room</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pax</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($vouchers)): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-hotel text-4xl mb-3 block"></i>No hotel vouchers found</td></tr>
                <?php else: ?>
                <?php foreach ($vouchers as $v): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-teal-600">
                        <a href="<?= url('hotel-voucher/show') ?>?id=<?= $v['id'] ?>" class="hover:underline"><?= e($v['voucher_no']) ?></a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                        <div><?= e($v['company_name']) ?></div>
                        <div class="text-xs text-gray-400"><?= e($v['guest_name']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?= e($v['hotel_name']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($v['check_in'])) ?></td>
                    <td class="px-4 py-3 text-center"><?= $v['nights'] ?></td>
                    <td class="px-4 py-3 text-center"><?= $roomTypes[$v['room_type']] ?? $v['room_type'] ?></td>
                    <td class="px-4 py-3 text-center"><?= e(($v['adults'] ?? 0) + ($v['children'] ?? 0) + ($v['infants'] ?? 0)) ?></td>
                    <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$v['status']] ?? $v['status'] ?></span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= url('hotel-voucher/show') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="<?= __('view') ?>"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('hotel-voucher/pdf') ?>?id=<?= $v['id'] ?>&download=1" class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="<?= __('download') ?>"><i class="fas fa-download"></i></a>
                            <a href="<?= url('hotel-voucher/edit') ?>?id=<?= $v['id'] ?>" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="<?= __('edit') ?>"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('hotel-voucher/delete') ?>?id=<?= $v['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="<?= __('delete') ?>"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Hotel Voucher Modal -->
<div id="newHotelModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto p-6" x-data="hotelVoucherForm()">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white"><i class="fas fa-hotel text-teal-500 mr-2"></i>New Hotel Voucher</h2>
            <button onclick="document.getElementById('newHotelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
        </div>

        <form method="POST" action="<?= url('hotel-voucher/store') ?>" @submit.prevent="prepareSubmit($el)">
            <?= csrf_field() ?>
            <input type="hidden" name="hotel_id" :value="selectedHotelId">

            <!-- 1. Company Info -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3"><i class="fas fa-building text-blue-500 mr-1"></i> Company Information</h3>
                <input type="hidden" name="company_id" :value="companyId">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative" @click.outside="partnerOpen = false">
                        <label for="hv_company_name" class="block text-xs font-medium text-gray-500 mb-1">Company Name *</label>
                        <input type="text" id="hv_company_name" name="company_name" x-model="companyName" @input.debounce.300ms="searchPartner()" @focus="if(partnerResults.length) partnerOpen=true"
                               required autocomplete="organization" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        <p class="text-[10px] text-gray-400 mt-0.5">Type to search registered partners</p>
                        <div x-show="partnerOpen && partnerResults.length > 0" x-transition
                             class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="r in partnerResults" :key="r.id">
                                <div @click="selectPartner(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                    <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                    <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label for="hv_address" class="block text-xs font-medium text-gray-500 mb-1">Address</label>
                        <input type="text" id="hv_address" name="address" x-model="companyAddress" autocomplete="street-address" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="hv_telephone" class="block text-xs font-medium text-gray-500 mb-1">Telephone</label>
                        <input type="text" id="hv_telephone" name="telephone" x-model="companyPhone" autocomplete="tel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
            </div>

            <!-- 2. Hotel Selection + Dates -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3"><i class="fas fa-bed text-teal-500 mr-1"></i> Hotel & Stay</h3>
                <input type="hidden" name="hotel_name" :value="selectedHotelName">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                    <div>
                        <label for="hv_country" class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-globe text-blue-400 mr-1"></i>Country *</label>
                        <select id="hv_country" x-model="selectedCountry" @change="onCountryChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            <option value="">-- Select Country --</option>
                            <template x-for="c in countries" :key="c"><option :value="c" x-text="c"></option></template>
                        </select>
                    </div>
                    <div>
                        <label for="hv_city" class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-city text-purple-400 mr-1"></i>City *</label>
                        <select id="hv_city" x-model="selectedCity" @change="onCityChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            <option value="">-- Select City --</option>
                            <template x-for="ci in cities" :key="ci"><option :value="ci" x-text="ci"></option></template>
                        </select>
                    </div>
                    <div>
                        <label for="hv_hotel" class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-hotel text-teal-400 mr-1"></i>Hotel *</label>
                        <select id="hv_hotel" x-model="selectedHotelId" @change="onHotelChange()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            <option value="">-- Select Hotel --</option>
                            <template x-for="h in filteredHotels" :key="h.id"><option :value="h.id" x-text="h.name + ' ' + '★'.repeat(h.stars || 0)"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="hv_check_in" class="block text-xs font-medium text-gray-500 mb-1"><?= __('check_in_date') ?: 'Check-in' ?> *</label>
                        <input type="date" id="hv_check_in" name="check_in" x-model="checkIn" @change="onCheckInChange()" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label for="hv_check_out" class="block text-xs font-medium text-gray-500 mb-1"><?= __('check_out_date') ?: 'Check-out' ?> *</label>
                        <input type="date" id="hv_check_out" name="check_out" x-model="checkOut" @change="onCheckOutChange()" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label for="hv_nights" class="block text-xs font-medium text-gray-500 mb-1"><?= __('nights') ?: 'Nights' ?></label>
                        <input type="number" id="hv_nights" name="nights" x-model.number="nights" @change="onNightsChange()" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm bg-gray-50 dark:bg-gray-600" readonly>
                    </div>
                    <div>
                        <label for="hv_transfer_type" class="block text-xs font-medium text-gray-500 mb-1">Transfer Type</label>
                        <select id="hv_transfer_type" name="transfer_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                            <?php foreach ($transferTypes as $k => $lbl): ?>
                            <option value="<?= $k ?>"><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. Rooms (dynamic, multiple) -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"><i class="fas fa-door-open text-indigo-500 mr-1"></i> Rooms</h3>
                    <button type="button" @click="addRoom()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition">
                        <i class="fas fa-plus text-[10px]"></i> Add Room
                    </button>
                </div>
                <input type="hidden" name="rooms_json" :value="JSON.stringify(rooms)">
                <input type="hidden" name="room_count" :value="rooms.length">
                <input type="hidden" name="room_type" :value="rooms[0]?.type || ''">
                <input type="hidden" name="board_type" :value="rooms[0]?.board || 'bed_breakfast'">
                <p x-show="!selectedHotelId" class="text-xs text-amber-500 mb-2"><i class="fas fa-info-circle mr-1"></i>Select a hotel above to load available room types.</p>
                <div class="space-y-3">
                    <template x-for="(room, ri) in rooms" :key="ri">
                        <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-500 uppercase" x-text="'Room ' + (ri+1)"></span>
                                <button type="button" @click="rooms.splice(ri, 1)" x-show="rooms.length > 1" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label :for="'hv_room_type_' + ri" class="block text-[10px] font-medium text-gray-400 mb-0.5">Room Type</label>
                                    <select :id="'hv_room_type_' + ri" x-model="room.type" @change="onRoomTypeChange(ri)" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                        <option value="">-- Select --</option>
                                        <template x-for="rt in availableRoomTypes" :key="rt">
                                            <option :value="rt" x-text="rt"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label :for="'hv_room_board_' + ri" class="block text-[10px] font-medium text-gray-400 mb-0.5">Board</label>
                                    <select :id="'hv_room_board_' + ri" x-model="room.board" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                        <option value="">-- Select --</option>
                                        <template x-for="bt in boardsForType(room.type)" :key="bt">
                                            <option :value="bt" x-text="boardLabel(bt)"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label :for="'hv_room_adults_' + ri" class="block text-[10px] font-medium text-gray-400 mb-0.5">Adults</label>
                                    <input type="number" :id="'hv_room_adults_' + ri" x-model.number="room.adults" min="0" :max="maxAdults(room.type)" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </div>
                                <div>
                                    <label :for="'hv_room_children_' + ri" class="block text-[10px] font-medium text-gray-400 mb-0.5">Children</label>
                                    <input type="number" :id="'hv_room_children_' + ri" x-model.number="room.children" min="0" :max="maxChildren(room.type)" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                    <span><strong x-text="totalPax()"></strong> total pax</span>
                    <span><strong x-text="rooms.length"></strong> room(s)</span>
                </div>
                <input type="hidden" name="adults" :value="totalAdults()">
                <input type="hidden" name="children" :value="totalChildren()">
                <input type="hidden" name="infants" :value="0">
            </div>

            <!-- 4. Guests (unified: name + age + passport) -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider"><i class="fas fa-users text-purple-500 mr-1"></i> Guests</h3>
                    <button type="button" @click="addGuest()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg text-xs font-semibold hover:bg-purple-100 transition">
                        <i class="fas fa-plus text-[10px]"></i> Add Guest
                    </button>
                </div>
                <input type="hidden" name="customers" :value="JSON.stringify(guests)">
                <input type="hidden" name="guest_name" :value="guests.length ? ((guests[0].title || '') + ' ' + (guests[0].name || '')).trim() : ''">
                <input type="hidden" name="passenger_passport" :value="guests.length ? (guests[0].passport || '') : ''">
                <div class="space-y-2">
                    <template x-for="(g, gi) in guests" :key="gi">
                        <div class="flex gap-2 items-center flex-wrap sm:flex-nowrap bg-gray-50 dark:bg-gray-700/30 rounded-lg p-2">
                            <label :for="'hv_guest_title_' + gi" class="sr-only" x-text="'Guest ' + (gi+1) + ' Title'"></label>
                            <select :id="'hv_guest_title_' + gi" x-model="g.title" class="w-20 px-1.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs">
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                                <option value="Miss">Miss</option>
                                <option value="Dr">Dr</option>
                                <option value="Child">Child</option>
                                <option value="Infant">Infant</option>
                            </select>
                            <label :for="'hv_guest_name_' + gi" class="sr-only" x-text="'Guest ' + (gi+1) + ' Name'"></label>
                            <input type="text" :id="'hv_guest_name_' + gi" x-model="g.name" placeholder="Full Name *" autocomplete="name" class="flex-1 min-w-[120px] px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            <label :for="'hv_guest_age_' + gi" class="sr-only" x-text="'Guest ' + (gi+1) + ' Age'"></label>
                            <input type="number" :id="'hv_guest_age_' + gi" x-model="g.age" placeholder="Age" min="0" max="120" class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                            <label :for="'hv_guest_passport_' + gi" class="sr-only" x-text="'Guest ' + (gi+1) + ' Passport'"></label>
                            <input type="text" :id="'hv_guest_passport_' + gi" x-model="g.passport" placeholder="Passport No." class="w-32 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                            <button type="button" @click="guests.splice(gi, 1)" x-show="guests.length > 1" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="text-[10px] text-gray-400 mt-1.5">Age and passport are optional. First guest is used as the main guest name on the voucher.</p>
            </div>

            <!-- 5. Special Requests -->
            <div class="mb-5">
                <label for="hv_special_requests" class="block text-xs font-medium text-gray-500 mb-1">Special Requests</label>
                <textarea id="hv_special_requests" name="special_requests" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></textarea>
            </div>

            <!-- 6. Link Additional Services -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-5" x-data="linkServicesForm()">
                <label class="block text-xs font-medium text-gray-500 mb-2">Link Additional Services</label>
                <p class="text-[10px] text-gray-400 mb-2">Search and link existing tours or transfers. They appear as Guest Program on the voucher.</p>
                <div class="relative mb-2" @click.outside="serviceResultsOpen = false">
                    <input type="text" id="hv_service_search" x-model="serviceQuery" @input.debounce.200ms="searchServices()" @focus="if(serviceResults.length) serviceResultsOpen = true"
                           placeholder="Search tours or transfers..." autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <div x-show="serviceResultsOpen && serviceResults.length > 0" x-transition class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="r in serviceResults" :key="r.type + '-' + r.id">
                            <div @click="addService(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 text-sm" x-text="r.label"></div>
                        </template>
                    </div>
                </div>
                <div class="space-y-1 min-h-[40px] rounded-lg border border-dashed border-gray-200 dark:border-gray-600 p-2" x-show="linkedList.length > 0">
                    <template x-for="(item, idx) in linkedList" :key="idx">
                        <div class="flex items-center justify-between py-1.5 px-2 bg-gray-50 dark:bg-gray-700/50 rounded text-sm">
                            <span class="flex-1 truncate" x-text="item.label"></span>
                            <button type="button" @click="removeService(idx)" class="ml-2 text-red-500 hover:text-red-700"><i class="fas fa-times text-xs"></i></button>
                        </div>
                    </template>
                </div>
                <input type="hidden" name="linked_services" :value="JSON.stringify(linkedList.map(x => ({type: x.type, id: x.id})))">
                <textarea name="additional_services" rows="1" class="hidden"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="document.getElementById('newHotelModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-save mr-1"></i>Generate Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const BOARD_LABELS = {BB:'Bed & Breakfast',HB:'Half Board',FB:'Full Board',AI:'All Inclusive',RO:'Room Only',UAI:'Ultra All Inclusive',
    bed_breakfast:'Bed & Breakfast',half_board:'Half Board',full_board:'Full Board',all_inclusive:'All Inclusive',room_only:'Room Only',ultra_all_inclusive:'Ultra All Inclusive'};

function hotelVoucherForm() {
    return {
        // Company
        companyId: '', companyName: '', companyAddress: '', companyPhone: '',
        partnerResults: [], partnerOpen: false,
        // Hotel cascade
        allHotels: [], countries: [], cities: [], filteredHotels: [],
        selectedCountry: '', selectedCity: '', selectedHotelId: '', selectedHotelName: '',
        // Hotel rooms from DB
        hotelRooms: [],
        availableRoomTypes: [],
        // Dates
        checkIn: '', checkOut: '', nights: 1,
        // Rooms
        rooms: [{ type: '', board: '', adults: 2, children: 0 }],
        // Guests
        guests: [{ title: 'Mr', name: '', age: '', passport: '' }],

        // --- Partner search ---
        async searchPartner() {
            if (this.companyName.length < 1) { this.partnerResults = []; this.partnerOpen = false; return; }
            try {
                const res = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.companyName));
                this.partnerResults = await res.json();
                this.partnerOpen = this.partnerResults.length > 0;
            } catch(e) { this.partnerResults = []; }
        },
        selectPartner(r) {
            this.companyName = r.company_name;
            this.companyId = r.id;
            this.partnerOpen = false;
            if (r.phone) this.companyPhone = r.phone;
            this.companyAddress = r.address || ((r.city || '') + (r.country ? ', ' + r.country : ''));
        },

        // --- Hotel cascade ---
        async init() {
            try {
                const res = await fetch('<?= url('api/hotels/list') ?>');
                this.allHotels = await res.json();
                this.countries = [...new Set(this.allHotels.map(h => h.country).filter(Boolean))].sort();
            } catch(e) { this.allHotels = []; }
            const urlParams = new URLSearchParams(window.location.search);
            const prefillId = urlParams.get('hotel_id');
            if (prefillId) {
                const h = this.allHotels.find(x => x.id == prefillId);
                if (h) {
                    this.selectedCountry = h.country; this.onCountryChange();
                    this.selectedCity = h.city; this.onCityChange();
                    this.selectedHotelId = h.id; await this.onHotelChange();
                }
            }
        },
        onCountryChange() {
            this.cities = [...new Set(this.allHotels.filter(h => h.country === this.selectedCountry).map(h => h.city).filter(Boolean))].sort();
            this.selectedCity = ''; this.filteredHotels = []; this.selectedHotelId = ''; this.selectedHotelName = '';
            this.hotelRooms = []; this.availableRoomTypes = [];
        },
        onCityChange() {
            this.filteredHotels = this.allHotels.filter(h => h.country === this.selectedCountry && h.city === this.selectedCity);
            this.selectedHotelId = ''; this.selectedHotelName = '';
            this.hotelRooms = []; this.availableRoomTypes = [];
        },
        async onHotelChange() {
            const h = this.allHotels.find(x => x.id == this.selectedHotelId);
            this.selectedHotelName = h ? h.name : '';
            await this.fetchHotelRooms();
        },
        async fetchHotelRooms() {
            if (!this.selectedHotelId) { this.hotelRooms = []; this.availableRoomTypes = []; return; }
            try {
                const res = await fetch('<?= url('api/hotels/rooms') ?>?hotel_id=' + this.selectedHotelId);
                this.hotelRooms = await res.json();
            } catch(e) { this.hotelRooms = []; }
            this.availableRoomTypes = [...new Set(this.hotelRooms.map(r => r.room_type))];
            // Reset room selections to first available
            if (this.availableRoomTypes.length) {
                const firstType = this.availableRoomTypes[0];
                const boards = this.boardsForType(firstType);
                const firstBoard = boards.length ? boards[0] : '';
                const match = this.hotelRooms.find(r => r.room_type === firstType);
                this.rooms = [{ type: firstType, board: firstBoard, adults: parseInt(match?.max_adults) || 2, children: 0 }];
            }
        },

        // --- Room helpers (DB-driven) ---
        boardsForType(roomType) {
            if (!roomType || !this.hotelRooms.length) return [];
            return [...new Set(this.hotelRooms.filter(r => r.room_type === roomType).map(r => r.board_type))];
        },
        boardLabel(code) { return BOARD_LABELS[code] || code; },
        maxAdults(roomType) {
            const match = this.hotelRooms.find(r => r.room_type === roomType);
            return match ? parseInt(match.max_adults) || 10 : 10;
        },
        maxChildren(roomType) {
            const match = this.hotelRooms.find(r => r.room_type === roomType);
            return match ? parseInt(match.max_children) || 10 : 10;
        },
        onRoomTypeChange(ri) {
            const room = this.rooms[ri];
            const boards = this.boardsForType(room.type);
            if (boards.length && !boards.includes(room.board)) room.board = boards[0];
            const match = this.hotelRooms.find(r => r.room_type === room.type);
            if (match) {
                const ma = parseInt(match.max_adults) || 2;
                const mc = parseInt(match.max_children) || 0;
                if (room.adults > ma) room.adults = ma;
                if (room.children > mc) room.children = mc;
            }
        },

        // --- Date / nights ---
        onCheckInChange() {
            if (this.checkIn && this.checkOut) {
                const diff = Math.round((new Date(this.checkOut) - new Date(this.checkIn)) / 86400000);
                if (diff > 0) this.nights = diff; else { this.nights = 1; this.checkOut = ''; }
            } else if (this.checkIn && this.nights > 0) {
                const d = new Date(this.checkIn); d.setDate(d.getDate() + this.nights);
                this.checkOut = d.toISOString().split('T')[0];
            }
        },
        onCheckOutChange() {
            if (this.checkIn && this.checkOut) {
                const diff = Math.round((new Date(this.checkOut) - new Date(this.checkIn)) / 86400000);
                if (diff > 0) this.nights = diff; else { this.nights = 1; this.checkOut = ''; }
            }
        },
        onNightsChange() {
            if (this.checkIn && this.nights > 0) {
                const d = new Date(this.checkIn); d.setDate(d.getDate() + this.nights);
                this.checkOut = d.toISOString().split('T')[0];
            }
        },

        // --- Rooms ---
        addRoom() {
            const firstType = this.availableRoomTypes.length ? this.availableRoomTypes[0] : '';
            const boards = this.boardsForType(firstType);
            this.rooms.push({ type: firstType, board: boards.length ? boards[0] : '', adults: 2, children: 0 });
        },
        totalAdults() { return this.rooms.reduce((s, r) => s + (r.adults || 0), 0); },
        totalChildren() { return this.rooms.reduce((s, r) => s + (r.children || 0), 0); },
        totalPax() { return this.totalAdults() + this.totalChildren(); },

        // --- Guests ---
        addGuest() {
            this.guests.push({ title: 'Mr', name: '', age: '', passport: '' });
        },

        // --- Submit ---
        prepareSubmit(form) {
            form.submit();
        }
    };
}
function linkServicesForm() {
    return {
        serviceQuery: '', serviceResults: [], serviceResultsOpen: false, linkedList: [],
        async searchServices() {
            if (this.serviceQuery.length < 2) { this.serviceResults = []; this.serviceResultsOpen = false; return; }
            try {
                const res = await fetch('<?= url('api/search-services') ?>?q=' + encodeURIComponent(this.serviceQuery));
                this.serviceResults = await res.json();
                this.serviceResultsOpen = this.serviceResults.length > 0;
            } catch(e) { this.serviceResults = []; }
        },
        addService(r) {
            if (this.linkedList.some(x => x.type === r.type && x.id === r.id)) return;
            this.linkedList.push({ type: r.type, id: r.id, label: r.label });
            this.serviceResultsOpen = false; this.serviceQuery = ''; this.serviceResults = [];
        },
        removeService(idx) { this.linkedList.splice(idx, 1); }
    };
}
</script>
