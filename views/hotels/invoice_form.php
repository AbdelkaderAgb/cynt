<?php
/**
 * Hotel Invoice — Create / Edit Form
 * Multi-hotel, multi-room. Prices fetched from DB via /api/hotels/rooms.
 * Inspired by transfer invoice + hotel voucher.
 */
$currencies  = ['USD','EUR','TRY','GBP','DZD','SAR','AED','RUB'];
$payMethods  = [
    ''              => '— Select Method —',
    'cash'          => 'Cash',
    'bank_transfer' => 'Bank Transfer',
    'wire_transfer' => 'Wire Transfer (SWIFT)',
    'credit_card'   => 'Credit Card',
    'check'         => 'Cheque',
    'credit'        => 'Partner Credit',
    'online'        => 'Online Payment',
    'other'         => 'Other',
];

$inv    = $invoice  ?? [];
$isEdit = !empty($isEdit) && !empty($inv['id']);
// Merge prefill data when creating from a voucher
if (!$isEdit && !empty($prefill)) {
    $inv = array_merge($inv, $prefill);
}
$formAction  = ($isEdit && !empty($inv['id'])) ? url('hotel-invoice/update') : url('hotel-invoice/store');
$pageHeading = $isEdit ? 'Edit Hotel Invoice' : 'New Hotel Invoice';
$submitLabel = $isEdit ? 'Save Changes'       : 'Generate Invoice';

// Existing hotels_json for edit mode
$existingHotels = '[]';
if ($isEdit && !empty($inv['hotels_json'])) {
    $existingHotels = $inv['hotels_json'];
}
?>

<!-- ── PAGE HEADER ── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-file-invoice text-teal-500 mr-2"></i><?= $pageHeading ?>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            <?= $isEdit ? 'Update hotel and room details below' : 'Add multiple hotels and room types — prices auto-loaded from catalog' ?>
        </p>
    </div>
    <a href="<?= url('hotel-invoice') ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<form method="POST" action="<?= $formAction ?>"
      x-data="hotelInvoiceForm()"
      @submit.prevent="submitForm">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
    <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
    <?php endif; ?>
    <!-- hidden fields filled before submit -->
    <input type="hidden" name="hotels_json"      id="fi_hotels_json">
    <input type="hidden" name="guests_json"      id="fi_guests_json">
    <input type="hidden" name="company_id"       id="fi_company_id"       value="<?= htmlspecialchars($inv['company_id'] ?? '') ?>">
    <input type="hidden" name="partner_contact"  id="fi_partner_contact"  value="<?= htmlspecialchars($inv['partner_contact'] ?? '') ?>">
    <input type="hidden" name="partner_phone"    id="fi_partner_phone"    value="<?= htmlspecialchars($inv['partner_phone']   ?? '') ?>">
    <input type="hidden" name="partner_email"    id="fi_partner_email"    value="<?= htmlspecialchars($inv['partner_email']   ?? '') ?>">
    <input type="hidden" name="partner_city"     id="fi_partner_city"     value="<?= htmlspecialchars($inv['partner_city']    ?? '') ?>">
    <input type="hidden" name="partner_country"  id="fi_partner_country"  value="<?= htmlspecialchars($inv['partner_country'] ?? '') ?>">

    <div class="space-y-5">

        <!-- ═══════════════════════════════════════
             1 · BILLING PARTY
        ════════════════════════════════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Billing Party</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Company autocomplete -->
                <div class="md:col-span-2"
                     x-data="partnerSearch()" @click.outside="open=false">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        Company / Agency <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs"><i class="fas fa-building"></i></span>
                        <input type="text" name="company_name" x-model="query" required autocomplete="off"
                               @input.debounce.280ms="search()"
                               @focus="if(results.length) open=true"
                               value="<?= htmlspecialchars($inv['company_name'] ?? '') ?>"
                               placeholder="Type to search company or agency…"
                               class="w-full pl-8 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <span x-show="loading" class="absolute inset-y-0 right-3 flex items-center text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i></span>
                    </div>
                    <!-- Dropdown -->
                    <div x-show="open && results.length > 0" x-transition
                         class="relative z-50 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl max-h-56 overflow-y-auto">
                        <template x-for="r in results" :key="r.id">
                            <div @click="pick(r)"
                                 class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center shrink-0">
                                    <i class="fas fa-building text-teal-500 text-[10px]"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                    <div class="text-xs text-gray-400" x-text="[r.contact_person, r.phone, r.city].filter(Boolean).join(' · ')"></div>
                                </div>
                                <span class="ml-auto text-[10px] text-teal-500 font-semibold uppercase" x-text="r.partner_type||''"></span>
                            </div>
                        </template>
                    </div>
                    <!-- Selected info bar -->
                    <div x-show="picked" class="mt-2 flex flex-wrap gap-3 px-3 py-2 bg-teal-50 dark:bg-teal-900/15 border border-teal-200 dark:border-teal-800 rounded-lg text-xs text-teal-700 dark:text-teal-400">
                        <span x-show="pickedPhone"><i class="fas fa-phone mr-1"></i><span x-text="pickedPhone"></span></span>
                        <span x-show="pickedEmail"><i class="fas fa-envelope mr-1"></i><span x-text="pickedEmail"></span></span>
                        <span x-show="pickedCity"><i class="fas fa-map-marker-alt mr-1"></i><span x-text="[pickedCity, pickedCountry].filter(Boolean).join(', ')"></span></span>
                    </div>
                </div>

                <!-- Invoice Date -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Invoice Date</label>
                    <input type="date" name="invoice_date"
                           value="<?= htmlspecialchars($inv['invoice_date'] ?? date('Y-m-d')) ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Due Date</label>
                    <input type="date" name="due_date"
                           value="<?= htmlspecialchars($inv['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Currency</label>
                    <select name="currency" x-model="currency"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        <?php foreach ($currencies as $c): ?>
                        <option value="<?= $c ?>"<?= ($inv['currency'] ?? 'USD') === $c ? ' selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Payment Method</label>
                    <select name="payment_method"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        <?php foreach ($payMethods as $v => $l): ?>
                        <option value="<?= $v ?>"<?= ($inv['payment_method'] ?? '') === $v ? ' selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Internal Notes -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 resize-none"
                              placeholder="Special requests, VIP arrangements…"><?= htmlspecialchars($inv['notes'] ?? '') ?></textarea>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════
             2 · HOTELS & ROOMS  (multi-hotel)
        ════════════════════════════════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Hotels &amp; Rooms</h3>
                </div>
                <button type="button" @click="addHotel()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                    <i class="fas fa-plus"></i> Add Hotel
                </button>
            </div>

            <div class="p-5 space-y-5">
                <template x-for="(hotel, hi) in hotels" :key="hi">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">

                        <!-- Hotel header bar -->
                        <div class="px-5 py-3 bg-teal-50 dark:bg-teal-900/20 border-b border-teal-100 dark:border-teal-800 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center" x-text="hi+1"></span>
                                <span class="font-semibold text-sm text-teal-800 dark:text-teal-300 truncate"
                                      x-text="hotel.name || 'Hotel ' + (hi+1)"></span>
                                <span x-show="hotel.city" class="text-xs text-teal-500 hidden sm:inline" x-text="'· ' + hotel.city"></span>
                                <span x-show="hotel.stars" class="text-amber-400 text-[10px]" x-text="'★'.repeat(hotel.stars||0)"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <!-- nights badge -->
                                <span x-show="hotel.nights > 0"
                                      class="text-xs bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 px-2 py-0.5 rounded-full font-medium"
                                      x-text="hotel.nights + ' night(s)'"></span>
                                <!-- remove hotel -->
                                <button type="button" @click="hotels.splice(hi,1)" x-show="hotels.length > 1"
                                        class="p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition text-xs">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">

                            <!-- Hotel search row -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                                <!-- Catalog picker -->
                                <div class="relative" :id="'hs-'+hi" @click.outside="hotel.searchOpen=false">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hotel Catalog</label>
                                    <div class="flex gap-1.5">
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 pointer-events-none text-[10px]"><i class="fas fa-search"></i></span>
                                            <input type="text" x-model="hotel.searchQuery"
                                                   @input.debounce.250ms="searchHotels(hi)"
                                                   @focus="searchHotels(hi)"
                                                   placeholder="Type to search…"
                                                   class="w-full pl-7 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-xs focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                                        </div>
                                    </div>
                                    <!-- Dropdown results -->
                                    <div x-show="hotel.searchOpen && hotel.searchResults.length > 0" x-transition
                                         class="absolute left-0 right-0 z-50 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-2xl max-h-56 overflow-y-auto">
                                        <template x-for="h in hotel.searchResults" :key="h.id">
                                            <button type="button" @click="pickHotel(hi, h)"
                                                    class="w-full text-left px-3 py-2.5 hover:bg-teal-50 dark:hover:bg-teal-900/30 border-b border-gray-50 dark:border-gray-700 last:border-0 transition">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="h.name"></div>
                                                        <div class="text-[10px] text-gray-400" x-text="[h.city, h.country].filter(Boolean).join(', ')"></div>
                                                    </div>
                                                    <span x-show="h.stars" class="text-amber-400 text-[10px] shrink-0" x-text="'★'.repeat(h.stars||0)"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Hotel name (editable / auto-filled) -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hotel Name <span class="text-red-400">*</span></label>
                                    <input type="text" x-model="hotel.name" required
                                           placeholder="Hotel name"
                                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm font-semibold focus:ring-2 focus:ring-teal-500">
                                </div>

                                <!-- Dates -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in</label>
                                        <input type="date" x-model="hotel.checkIn" @change="calcNights(hi)"
                                               class="w-full px-2 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-xs focus:ring-2 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Check-out</label>
                                        <input type="date" x-model="hotel.checkOut" @change="calcNights(hi)"
                                               class="w-full px-2 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-xs focus:ring-2 focus:ring-teal-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Loading indicator for rooms -->
                            <div x-show="hotel.loadingRooms" class="flex items-center gap-2 text-xs text-teal-500 py-2">
                                <i class="fas fa-spinner fa-spin"></i> Loading rooms from catalog…
                            </div>

                            <!-- Room lines header -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Room Lines</span>
                                <button type="button" @click="addRoom(hi)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg text-[11px] font-semibold hover:bg-teal-100 transition">
                                    <i class="fas fa-plus text-[9px]"></i> Add Room
                                </button>
                            </div>

                            <!-- Room lines table -->
                            <div class="space-y-2">
                                <template x-for="(room, ri) in hotel.rooms" :key="ri">
                                    <div class="bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                                        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-2.5 items-end">

                                            <!-- Room type (unique types from catalog) -->
                                            <div class="col-span-2 sm:col-span-1 xl:col-span-2">
                                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Room Type</label>
                                                <template x-if="hotel.catalogRooms.length > 0">
                                                    <select x-model="room.roomType"
                                                            @change="onRoomTypeSelect(hi, ri)"
                                                            class="w-full px-2 py-2 border border-teal-300 dark:border-teal-700 rounded-lg bg-white dark:bg-gray-700 text-xs font-semibold focus:ring-2 focus:ring-teal-500">
                                                        <option value="">— Select Room Type —</option>
                                                        <template x-for="rt in uniqueRoomTypes(hotel)" :key="rt">
                                                            <option :value="rt" x-text="rt"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                                <template x-if="hotel.catalogRooms.length === 0">
                                                    <input type="text" x-model="room.roomType" placeholder="e.g. DBL, SNG…"
                                                           class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs focus:ring-2 focus:ring-teal-500">
                                                </template>
                                            </div>

                                            <!-- Board — dynamic from catalog for selected room type -->
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Board</label>
                                                <template x-if="hotel.catalogRooms.length > 0">
                                                    <select x-model="room.board"
                                                            @change="onBoardSelect(hi, ri)"
                                                            class="w-full px-2 py-2 border border-blue-300 dark:border-blue-700 rounded-lg bg-white dark:bg-gray-700 text-[11px] font-semibold text-blue-700 dark:text-blue-300 focus:ring-2 focus:ring-blue-500">
                                                        <option value="">— Board —</option>
                                                        <template x-for="b in boardsForRoomType(hotel, room.roomType)" :key="b.id">
                                                            <option :value="b.board_type" x-text="b.board_type"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                                <template x-if="hotel.catalogRooms.length === 0">
                                                    <select x-model="room.board" class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-[11px] focus:ring-2 focus:ring-teal-500">
                                                        <option value="RO">Room Only</option>
                                                        <option value="BB">Bed &amp; Breakfast</option>
                                                        <option value="HB">Half Board</option>
                                                        <option value="FB">Full Board</option>
                                                        <option value="AI">All Inclusive</option>
                                                        <option value="UAI">Ultra All Incl.</option>
                                                    </select>
                                                </template>
                                            </div>

                                            <!-- Adults per room (0 = child-only room) -->
                                            <div>
                                                <label class="block text-[10px] font-semibold mb-1"
                                                       :class="room.adults===0 ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400'">
                                                    <i class="fas fa-user mr-0.5"></i>
                                                    <span x-text="room.adults===0 ? 'Adults (CHD room)' : 'Adults'"></span>
                                                </label>
                                                <input type="number" x-model.number="room.adults" min="0"
                                                       class="w-full px-2 py-2 border rounded-lg bg-white dark:bg-gray-700 text-sm text-center font-bold focus:ring-2"
                                                       :class="room.adults===0 ? 'border-amber-300 dark:border-amber-700 focus:ring-amber-500' : 'border-blue-300 dark:border-blue-700 focus:ring-blue-500'">
                                            </div>

                                            <!-- Children per room -->
                                            <div>
                                                <label class="block text-[10px] font-semibold text-amber-600 dark:text-amber-400 mb-1"><i class="fas fa-child mr-0.5"></i> Children</label>
                                                <input type="number" x-model.number="room.children" min="0"
                                                       @change="updateChildRate(hi, ri)"
                                                       class="w-full px-2 py-2 border border-amber-300 dark:border-amber-700 rounded-lg bg-white dark:bg-gray-700 text-sm text-center font-bold focus:ring-2 focus:ring-amber-500">
                                            </div>

                                            <!-- Infants per room -->
                                            <div>
                                                <label class="block text-[10px] font-semibold text-purple-600 dark:text-purple-400 mb-1"><i class="fas fa-baby mr-0.5"></i> Infants</label>
                                                <input type="number" x-model.number="room.infants" min="0"
                                                       class="w-full px-2 py-2 border border-purple-300 dark:border-purple-700 rounded-lg bg-white dark:bg-gray-700 text-sm text-center font-bold focus:ring-2 focus:ring-purple-500">
                                            </div>

                                            <!-- Count (# rooms) -->
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Rooms</label>
                                                <input type="number" x-model.number="room.count" min="1"
                                                       class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center focus:ring-2 focus:ring-teal-500">
                                            </div>

                                            <!-- Base price / room / night -->
                                            <div>
                                                <label class="block text-[10px] font-bold mb-1"
                                                       :class="room.adults===0 ? 'text-amber-600 dark:text-amber-400' : 'text-teal-600 dark:text-teal-400'"
                                                       x-text="room.adults===0 ? 'Child Room Rate/Night' : 'Room Price/Night'"></label>
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-[10px] text-gray-400" x-text="currency"></span>
                                                    <input type="number" x-model.number="room.price" step="0.01" min="0"
                                                           class="w-full pl-8 pr-2 py-2 border rounded-lg bg-white dark:bg-gray-700 text-sm font-bold focus:ring-2"
                                                           :class="room.adults===0 ? 'border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 focus:ring-amber-500' : 'border-teal-300 dark:border-teal-700 text-teal-800 dark:text-teal-200 focus:ring-teal-500'">
                                                </div>
                                            </div>

                                            <!-- Child extra bed price (only when adults > 0 AND children > 0) -->
                                            <div x-show="room.adults > 0 && room.children > 0" x-cloak>
                                                <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-1">Child Bed/Night</label>
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none text-[10px] text-gray-400" x-text="currency"></span>
                                                    <input type="number" x-model.number="room.childPrice" step="0.01" min="0"
                                                           placeholder="0"
                                                           class="w-full pl-8 pr-2 py-2 border border-amber-300 dark:border-amber-700 rounded-lg bg-white dark:bg-gray-700 text-sm font-bold text-amber-800 dark:text-amber-300 focus:ring-2 focus:ring-amber-500">
                                                </div>
                                            </div>

                                            <!-- Remove button -->
                                            <div class="flex items-end">
                                                <button type="button"
                                                        x-show="hotel.rooms.length > 1"
                                                        @click="hotel.rooms.splice(ri,1)"
                                                        class="w-full py-2 text-[11px] text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition font-semibold border border-transparent hover:border-red-200">
                                                    <i class="fas fa-trash-alt mr-1"></i>Remove
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Catalog price reference strip (auto-fills based on adults) -->
                                        <template x-if="room.prices">
                                            <div class="mt-2 flex flex-wrap gap-1 items-center">
                                                <span class="text-[9px] text-gray-400 uppercase tracking-wider mr-1">DB prices:</span>
                                                <template x-if="room.prices.single">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-semibold transition"
                                                          :class="(room.adults<=1) ? 'bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 ring-1 ring-teal-400' : 'bg-gray-100 dark:bg-gray-600/60 text-gray-400'"
                                                          x-text="'1ADL: ' + room.prices.single"></span>
                                                </template>
                                                <template x-if="room.prices.double">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-semibold transition"
                                                          :class="(room.adults===2) ? 'bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 ring-1 ring-teal-400' : 'bg-gray-100 dark:bg-gray-600/60 text-gray-400'"
                                                          x-text="'2ADL: ' + room.prices.double"></span>
                                                </template>
                                                <template x-if="room.prices.triple">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-semibold transition"
                                                          :class="(room.adults===3) ? 'bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 ring-1 ring-teal-400' : 'bg-gray-100 dark:bg-gray-600/60 text-gray-400'"
                                                          x-text="'3ADL: ' + room.prices.triple"></span>
                                                </template>
                                                <template x-if="room.prices.quad">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-semibold transition"
                                                          :class="(room.adults>=4) ? 'bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 ring-1 ring-teal-400' : 'bg-gray-100 dark:bg-gray-600/60 text-gray-400'"
                                                          x-text="'4ADL: ' + room.prices.quad"></span>
                                                </template>
                                                <template x-if="room.prices.child">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400"
                                                          x-text="'CHD bed: ' + room.prices.child"></span>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Pax summary + line total -->
                                        <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2">
                                            <!-- Pax badges -->
                                            <div class="flex items-center flex-wrap gap-1.5">
                                                <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold">
                                                    <i class="fas fa-user text-[8px]"></i>
                                                    <span x-text="(room.adults||1) + ' ADL'"></span>
                                                </span>
                                                <template x-if="room.children > 0">
                                                    <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-semibold">
                                                        <i class="fas fa-child text-[8px]"></i>
                                                        <span x-text="room.children + ' CHD'"></span>
                                                        <span x-show="room.childPrice > 0" class="text-[9px] opacity-70" x-text="'(+' + room.childPrice + '/night)'"></span>
                                                    </span>
                                                </template>
                                                <template x-if="room.infants > 0">
                                                    <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-semibold">
                                                        <i class="fas fa-baby text-[8px]"></i>
                                                        <span x-text="room.infants + ' INF'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                            <!-- Line total -->
                                            <span class="ml-auto text-xs font-bold text-gray-700 dark:text-gray-200"
                                                  x-text="currency + ' ' + roomLineTotal(room, hotel.nights).toFixed(2)
                                                           + ' (' + (hotel.nights||1) + ' night' + ((hotel.nights>1)?'s':'') + ')'"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Add room dashed -->
                                <button type="button" @click="addRoom(hi)"
                                        class="w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-400 rounded-xl text-xs font-medium hover:border-teal-400 hover:text-teal-500 hover:bg-teal-50 dark:hover:bg-teal-900/10 transition">
                                    <i class="fas fa-plus-circle text-sm"></i> Add Room Line
                                </button>
                            </div>

                            <!-- Hotel subtotal -->
                            <div class="flex justify-end items-center gap-3 pt-1 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-xs text-gray-400 uppercase tracking-wider">Hotel subtotal</span>
                                <span class="font-bold text-base text-gray-800 dark:text-gray-100"
                                      x-text="currency + ' ' + hotelSubtotal(hi).toFixed(2)"></span>
                            </div>

                        </div>
                    </div>
                </template>

                <!-- Add hotel dashed CTA -->
                <button type="button" @click="addHotel()"
                        class="w-full flex items-center justify-center gap-2 py-4 border-2 border-dashed border-teal-300 dark:border-teal-700 text-teal-500 dark:text-teal-400 rounded-2xl font-medium hover:border-teal-500 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition">
                    <i class="fas fa-hotel text-lg"></i> Add Another Hotel
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             3 · INVOICE TOTALS
        ════════════════════════════════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Invoice Totals</h3>
            </div>
            <div class="p-6">

                <!-- Hotel breakdown -->
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-200 dark:border-gray-600 divide-y divide-gray-200 dark:divide-gray-600 mb-5">
                    <template x-for="(hotel, hi) in hotels" :key="hi">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-teal-500 text-white text-[9px] font-bold flex items-center justify-center" x-text="hi+1"></span>
                                <span x-text="hotel.name||'Hotel '+(hi+1)"></span>
                                <span class="text-gray-400 text-xs" x-text="hotel.rooms.length + ' room line(s)'"></span>
                            </span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200"
                                  x-text="currency + ' ' + hotelSubtotal(hi).toFixed(2)"></span>
                        </div>
                    </template>
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-100 dark:bg-gray-700">
                        <span class="font-bold text-sm text-gray-700 dark:text-gray-200">Combined Subtotal</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="currency + ' ' + allSubtotal.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Tax / Discount / Paid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" x-model.number="taxRate" min="0" max="100" step="0.1"
                               value="<?= htmlspecialchars($inv['tax_rate'] ?? 0) ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Discount</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs" x-text="currency"></span>
                            <input type="number" name="discount" x-model.number="discount" min="0" step="0.01"
                                   value="<?= htmlspecialchars($inv['discount'] ?? 0) ?>"
                                   class="w-full pl-9 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Paid Amount</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs" x-text="currency"></span>
                            <input type="number" name="paid_amount" min="0" step="0.01"
                                   value="<?= htmlspecialchars($inv['paid_amount'] ?? 0) ?>"
                                   class="w-full pl-9 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <!-- Grand total banner -->
                <div class="flex items-center justify-between bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-2xl px-6 py-4 shadow-lg shadow-teal-500/20">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest opacity-80">Grand Total</p>
                        <p class="text-xs opacity-60 mt-0.5"
                           x-text="'Tax: '+currency+' '+(allSubtotal * taxRate/100).toFixed(2) + ' · Discount: ' + currency + ' ' + discount.toFixed(2)"></p>
                    </div>
                    <span class="text-3xl font-bold" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════
             4 · GUESTS
        ════════════════════════════════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="guestList()">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">4</span>
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Guests / Passengers</h3>
                </div>
                <button type="button" @click="addGuest()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg text-xs font-semibold hover:bg-teal-100 dark:hover:bg-teal-900/50 transition">
                    <i class="fas fa-plus"></i> Add Guest
                </button>
            </div>
            <div class="p-5 space-y-3">
                <input type="hidden" name="guests_json" :value="JSON.stringify(guests)">
                <template x-for="(g, gi) in guests" :key="gi">
                    <div :class="gi===0
                            ? 'rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-900/10 p-4'
                            : 'rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40 p-4'">
                        <div class="flex items-center justify-between mb-3">
                            <span :class="gi===0 ? 'text-xs font-bold text-amber-700 dark:text-amber-400' : 'text-xs font-semibold text-gray-500'"
                                  x-text="gi===0 ? '★ Lead Guest' : 'Guest ' + (gi+1)"></span>
                            <button x-show="gi>0" type="button" @click="guests.splice(gi,1)"
                                    class="text-xs text-red-400 hover:text-red-600 transition">
                                <i class="fas fa-times-circle mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1">Title</label>
                                <select x-model="g.title"
                                        class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                    <option>Mr</option><option>Mrs</option><option>Ms</option><option>Miss</option><option>Dr</option><option>Child</option><option>Infant</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] text-gray-500 mb-1">Full Name *</label>
                                <input type="text" x-model="g.name" :required="gi===0"
                                       placeholder="Full legal name"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1">Passport / ID</label>
                                <input type="text" x-model="g.passport" placeholder="Passport no."
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm font-mono uppercase tracking-wider focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>
                </template>
                <p class="text-[10px] text-gray-400">The first guest appears as lead name on the invoice PDF.</p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             5 · TERMS
        ════════════════════════════════════════ -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center">5</span>
                <h3 class="font-semibold text-gray-700 dark:text-gray-200 text-sm uppercase tracking-wider">Terms &amp; Conditions</h3>
            </div>
            <div class="p-5">
                <textarea name="terms" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500 resize-none"
                          placeholder="Payment terms, cancellation policy, special conditions…"><?= htmlspecialchars($inv['terms'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- ── ACTION BUTTONS ── -->
        <div class="flex items-center justify-end gap-3 pt-2 pb-6">
            <a href="<?= url('hotel-invoice') ?>"
               class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm">
                Cancel
            </a>
            <?php if ($isEdit): ?>
            <a href="<?= url('hotel-invoice/show') ?>?id=<?= (int)$inv['id'] ?>"
               class="px-5 py-2.5 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition flex items-center gap-2">
                <i class="fas fa-eye"></i> View Invoice
            </a>
            <?php endif; ?>
            <button type="submit"
                    class="px-8 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5 flex items-center gap-2 text-sm">
                <i class="fas fa-file-invoice-dollar"></i> <?= $submitLabel ?>
            </button>
        </div>

    </div>
</form>

<script>
/* ─────────────────────────────────────────
   Partner / Company autocomplete
───────────────────────────────────────── */
function partnerSearch() {
    return {
        query:         '<?= addslashes($inv['company_name'] ?? '') ?>',
        results:       [],
        open:          false,
        loading:       false,
        picked:        false,
        pickedPhone:   '',
        pickedEmail:   '',
        pickedCity:    '',
        pickedCountry: '',

        async search() {
            if (this.query.length < 1) { this.results = []; this.open = false; return; }
            this.loading = true;
            try {
                const res  = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.open = this.results.length > 0;
            } catch(e) { this.results = []; }
            this.loading = false;
        },

        pick(r) {
            this.query         = r.company_name;
            this.open          = false;
            this.picked        = true;
            this.pickedPhone   = r.phone   || '';
            this.pickedEmail   = r.email   || '';
            this.pickedCity    = r.city    || '';
            this.pickedCountry = r.country || '';

            document.getElementById('fi_company_id').value      = r.id;
            document.getElementById('fi_partner_contact').value = r.contact_person || '';
            document.getElementById('fi_partner_phone').value   = r.phone          || '';
            document.getElementById('fi_partner_email').value   = r.email          || '';
            document.getElementById('fi_partner_city').value    = r.city           || '';
            document.getElementById('fi_partner_country').value = r.country        || '';
        }
    };
}

/* ─────────────────────────────────────────
   Guest list
───────────────────────────────────────── */
function guestList() {
    return {
        guests: [{ title:'Mr', name:'', passport:'' }],
        addGuest()       { this.guests.push({ title:'Mr', name:'', passport:'' }); },
    };
}

/* ─────────────────────────────────────────
   Main invoice form (multi-hotel)
───────────────────────────────────────── */
function hotelInvoiceForm() {
    const API_HOTELS = '<?= url('api/hotels/list') ?>';
    const API_ROOMS  = '<?= url('api/hotels/rooms') ?>';

    /* Restore hotels from edit-mode JSON */
    let initHotels;
    try { initHotels = JSON.parse('<?= addslashes($existingHotels) ?>'); } catch(e) { initHotels = []; }
    if (!initHotels || !initHotels.length) {
        initHotels = [{
            id: null, name: '', city: '', country: '', stars: 0,
            checkIn: '', checkOut: '', nights: 1,
            searchQuery: '', searchResults: [], searchOpen: false, loadingRooms: false,
            catalogRooms: [],
            rooms: [{ roomId:'', roomType:'', board:'BB', count:1, adults:2, children:0, infants:0, price:0, childPrice:0, prices:null }]
        }];
    } else {
        initHotels = initHotels.map(h => ({
            catalogRooms: [], searchQuery:'', searchResults:[], searchOpen:false, loadingRooms:false,
            ...h,
            rooms: (h.rooms||[]).map(r => ({
                roomId:'', roomType: r.roomType||r.type||'', board: r.board||'BB',
                count: r.count||1, adults: r.adults||2, children: r.children||0,
                infants: r.infants||0, price: r.price||0, childPrice: r.childPrice||0, prices:null,
                ...r
            }))
        }));
    }

    return {
        currency: '<?= htmlspecialchars($inv['currency'] ?? 'USD') ?>',
        taxRate:  <?= (float)($inv['tax_rate'] ?? 0) ?>,
        discount: <?= (float)($inv['discount'] ?? 0) ?>,
        hotels:   initHotels,

        /* ── add / remove hotel ── */
        addHotel() {
            this.hotels.push({
                id: null, name: '', city: '', country: '', stars: 0,
                checkIn: '', checkOut: '', nights: 1,
                searchQuery: '', searchResults: [], searchOpen: false, loadingRooms: false,
                catalogRooms: [],
                rooms: [{ roomId:'', roomType:'', board:'BB', count:1, adults:2, children:0, infants:0, price:0, childPrice:0, prices:null }]
            });
        },

        /* ── hotel catalog search ── */
        async searchHotels(hi) {
            const hotel = this.hotels[hi];
            const q     = hotel.searchQuery.trim();
            hotel.searchOpen = true;
            try {
                const res  = await fetch(API_HOTELS + '?q=' + encodeURIComponent(q));
                hotel.searchResults = await res.json();
            } catch(e) { hotel.searchResults = []; }
        },

        /* ── pick a hotel from catalog ── */
        async pickHotel(hi, h) {
            const hotel = this.hotels[hi];
            hotel.id           = h.id;
            hotel.name         = h.name;
            hotel.city         = h.city;
            hotel.country      = h.country;
            hotel.stars        = h.stars;
            hotel.searchOpen   = false;
            hotel.searchQuery  = h.name;
            hotel.searchResults = [];

            /* Load rooms for this hotel */
            hotel.loadingRooms = true;
            try {
                const res   = await fetch(API_ROOMS + '?hotel_id=' + h.id);
                const rooms = await res.json();
                hotel.catalogRooms = rooms;

                /* Pre-fill first room line: pick first unique room type + first board */
                if (rooms.length > 0) {
                    const firstType = rooms[0].room_type;
                    const firstRow  = rooms.find(r => r.room_type === firstType) || rooms[0];
                    hotel.rooms[0].roomType   = firstRow.room_type;
                    hotel.rooms[0].board      = firstRow.board_type || '';
                    hotel.rooms[0].roomId     = firstRow.id;
                    hotel.rooms[0].prices     = this.buildPriceTiers(firstRow);
                    hotel.rooms[0].childPrice = (firstRow.price_child > 0) ? firstRow.price_child : 0;
                    this.applyRoomPrice(hi, 0);
                }
            } catch(e) { hotel.catalogRooms = []; }
            hotel.loadingRooms = false;
        },

        /* ── helpers for the two-level room type → board dropdowns ── */
        uniqueRoomTypes(hotel) {
            const seen = new Set();
            return (hotel.catalogRooms || []).reduce((acc, cr) => {
                if (!seen.has(cr.room_type)) { seen.add(cr.room_type); acc.push(cr.room_type); }
                return acc;
            }, []);
        },

        boardsForRoomType(hotel, roomType) {
            if (!roomType) return hotel.catalogRooms || [];
            return (hotel.catalogRooms || []).filter(cr => cr.room_type === roomType);
        },

        /* ── Step 1: user changes Room Type → auto-select first available board ── */
        onRoomTypeSelect(hi, ri) {
            const hotel  = this.hotels[hi];
            const room   = hotel.rooms[ri];
            const boards = this.boardsForRoomType(hotel, room.roomType);
            if (boards.length > 0) {
                room.board = boards[0].board_type || '';
            } else {
                room.board = '';
            }
            this.onBoardSelect(hi, ri);
        },

        /* ── Step 2: user changes Board → find exact catalog row → update prices ── */
        onBoardSelect(hi, ri) {
            const hotel = this.hotels[hi];
            const room  = hotel.rooms[ri];
            const cr    = (hotel.catalogRooms || []).find(
                r => r.room_type === room.roomType && r.board_type === room.board
            );
            if (!cr) return;
            room.roomId     = cr.id;
            room.prices     = this.buildPriceTiers(cr);
            room.childPrice = (cr.price_child > 0) ? cr.price_child : 0;
            this.applyRoomPrice(hi, ri);
        },

        /* ── legacy alias (kept for safety) ── */
        onRoomSelect(hi, ri) { this.onBoardSelect(hi, ri); },

        /* ── auto-set room base price based on adults count & catalog tiers ── */
        applyRoomPrice(hi, ri) {
            const room   = this.hotels[hi].rooms[ri];
            if (!room.prices) return;
            const adults = parseInt(room.adults);

            if (adults === 0) {
                // Child-only room: price = the catalog child room rate
                room.price      = room.prices.child || 0;
                room.childPrice = 0;   // no extra supplement needed
            } else {
                // Adult (possibly + extra-bed children)
                let price = 0;
                if      (adults <= 1) price = room.prices.single || room.prices.double || 0;
                else if (adults === 2) price = room.prices.double || room.prices.single || 0;
                else if (adults === 3) price = room.prices.triple || room.prices.double || 0;
                else                  price = room.prices.quad   || room.prices.triple || room.prices.double || 0;
                if (price > 0) room.price = price;
                // Extra-bed supplement from catalog
                if (room.prices.child > 0) room.childPrice = room.prices.child;
            }
        },

        /* ── update only childPrice when children count changes (does NOT touch base price) ── */
        updateChildRate(hi, ri) {
            const room     = this.hotels[hi].rooms[ri];
            if (!room.prices) return;
            const children = parseInt(room.children) || 0;
            if (children > 0 && room.prices.child > 0) {
                room.childPrice = room.prices.child;
            } else if (children === 0) {
                room.childPrice = 0;
            }
            // room.price is intentionally NOT touched — base price stays fixed
        },

        /* ── legacy stub ── */
        applyOccupancyPrice() {},

        /* ── build a price tiers object from a catalog room row ── */
        buildPriceTiers(cr) {
            const tiers = {};
            if (cr.price_single != null && cr.price_single > 0) tiers.single = cr.price_single;
            if (cr.price_double != null && cr.price_double > 0) tiers.double = cr.price_double;
            if (cr.price_triple != null && cr.price_triple > 0) tiers.triple = cr.price_triple;
            if (cr.price_quad   != null && cr.price_quad   > 0) tiers.quad   = cr.price_quad;
            if (cr.price_child  != null && cr.price_child  > 0) tiers.child  = cr.price_child;
            return Object.keys(tiers).length ? tiers : null;
        },

        /* ── add room line to a hotel ── */
        addRoom(hi) {
            this.hotels[hi].rooms.push({
                roomId:'', roomType:'', board:'BB', count:1, adults:2, children:0, infants:0, price:0, childPrice:0, prices:null
            });
        },

        /* ── calculate nights from check-in/out ── */
        calcNights(hi) {
            const hotel = this.hotels[hi];
            if (hotel.checkIn && hotel.checkOut) {
                const d = Math.round(
                    (new Date(hotel.checkOut) - new Date(hotel.checkIn)) / 86400000
                );
                hotel.nights = d > 0 ? d : 1;
            }
        },

        /* ── line total for one room line ── */
        roomLineTotal(room, nights) {
            const n     = Math.max(1, parseInt(nights) || 1);
            const rooms = Math.max(1, parseInt(room.count) || 1);
            const base  = parseFloat(room.price) || 0;
            const chd   = parseInt(room.children) || 0;
            const adults= parseInt(room.adults);

            if (adults === 0) {
                // Child-only room: base IS the child room rate, no extra supplement
                return base * rooms * n;
            } else {
                // Adult room ± extra-bed children supplement
                const chdP = parseFloat(room.childPrice) || 0;
                return (base * rooms + chdP * chd * rooms) * n;
            }
        },

        /* ── per-hotel subtotal ── */
        hotelSubtotal(hi) {
            const hotel = this.hotels[hi];
            const n     = hotel.nights || 1;
            return hotel.rooms.reduce((s, r) => s + this.roomLineTotal(r, n), 0);
        },

        /* ── combined subtotal across all hotels ── */
        get allSubtotal() {
            return this.hotels.reduce((s, _, hi) => s + this.hotelSubtotal(hi), 0);
        },

        /* ── grand total ── */
        get grandTotal() {
            const tax = this.allSubtotal * (parseFloat(this.taxRate)||0) / 100;
            return Math.max(0, this.allSubtotal + tax - (parseFloat(this.discount)||0));
        },

        /* ── prepare & submit form ── */
        submitForm(e) {
            /* Build hotels payload (strip reactivity) */
            const payload = this.hotels.map(h => ({
                id:       h.id,
                name:     h.name,
                city:     h.city,
                country:  h.country,
                checkIn:  h.checkIn,
                checkOut: h.checkOut,
                nights:   h.nights,
                rooms: h.rooms.map(r => ({
                    roomId:     r.roomId,
                    roomType:   r.roomType,
                    board:      r.board,
                    count:      r.count,
                    adults:     r.adults     || 2,
                    children:   r.children   || 0,
                    infants:    r.infants    || 0,
                    price:      r.price,
                    childPrice: r.childPrice || 0,
                    nights:     h.nights,
                }))
            }));

            document.getElementById('fi_hotels_json').value = JSON.stringify(payload);
            e.target.submit();
        }
    };
}
</script>
