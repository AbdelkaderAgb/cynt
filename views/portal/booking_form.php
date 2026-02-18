<?php
/**
 * Partner Portal — New Booking Request Form
 * Features: City-based hotel search, live room pricing, pax-based calculation
 */
$services = $services ?? [];
$hotels = $hotels ?? [];
$hotelRooms = $hotelRooms ?? [];
$cities = $cities ?? [];
$servicesByType = ['transfer' => [], 'hotel' => [], 'tour' => []];
foreach ($services as $svc) {
    $t = $svc['service_type'] ?? 'other';
    if (isset($servicesByType[$t])) $servicesByType[$t][] = $svc;
}
// Group rooms by hotel_id
$roomsByHotel = [];
foreach ($hotelRooms as $r) {
    $roomsByHotel[$r['hotel_id']][] = $r;
}
?>
<div class="mb-6 flex items-center gap-3">
    <a href="<?= url('portal/bookings') ?>" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:bg-gray-200">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-plus-circle text-blue-500 mr-2"></i>New Booking Request</h1>
        <p class="text-sm text-gray-500 mt-1">Select a service, see live pricing, and submit your request</p>
    </div>
</div>
<?php if (isset($_GET['error']) && $_GET['error'] === 'capacity' && !empty($_GET['message'])): ?>
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i> <?= e(urldecode((string)$_GET['message'])) ?>
</div>
<?php endif; ?>
<div class="max-w-3xl" x-data="bookingForm()">
    <form method="POST" action="<?= url('portal/booking/store') ?>" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 space-y-5">
        <?= csrf_field() ?>

        <!-- Request Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Booking Type</label>
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="request_type" value="transfer" x-model="requestType" @change="resetAll()" class="sr-only peer">
                    <div class="p-2.5 sm:p-3 border-2 rounded-xl text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition">
                        <i class="fas fa-car-side text-blue-500 text-lg sm:text-xl mb-1"></i>
                        <p class="text-xs sm:text-sm font-semibold">Transfer</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="request_type" value="hotel" x-model="requestType" @change="resetAll()" class="sr-only peer">
                    <div class="p-2.5 sm:p-3 border-2 rounded-xl text-center peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 transition">
                        <i class="fas fa-hotel text-purple-500 text-lg sm:text-xl mb-1"></i>
                        <p class="text-xs sm:text-sm font-semibold">Hotel</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="request_type" value="tour" x-model="requestType" @change="resetAll()" class="sr-only peer">
                    <div class="p-2.5 sm:p-3 border-2 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/20 transition">
                        <i class="fas fa-map-marked-alt text-emerald-500 text-lg sm:text-xl mb-1"></i>
                        <p class="text-xs sm:text-sm font-semibold">Tour</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Service Selection (for transfer & tour) -->
        <div x-show="requestType !== 'hotel'" x-transition class="bg-gradient-to-br from-gray-50 to-blue-50/30 dark:from-gray-700/50 dark:to-blue-900/10 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                <i class="fas fa-list-check mr-1"></i> Select Service
            </label>
            <select name="service_id" x-model="selectedServiceId" @change="onServiceSelect()"
                    class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">-- Choose from available services --</option>
                <template x-if="requestType === 'transfer'">
                    <template x-for="s in serviceData.transfer" :key="s.id">
                        <option :value="s.id" x-text="s.name + ' — ' + Number(s.price).toFixed(2) + ' ' + s.currency"></option>
                    </template>
                </template>
                <template x-if="requestType === 'tour'">
                    <template x-for="s in serviceData.tour" :key="s.id">
                        <option :value="s.id" x-text="s.name + ' — ' + Number(s.price).toFixed(2) + ' ' + s.currency"></option>
                    </template>
                </template>
            </select>
            <!-- Price Badge -->
            <div x-show="selectedService" x-transition class="mt-3 flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-emerald-200 dark:border-emerald-700">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="selectedService?.name || ''"></div>
                    <div class="text-xs text-gray-400 truncate" x-text="selectedService?.description || ''"></div>
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="text-lg font-bold text-emerald-600" x-text="servicePrice"></div>
                    <div class="text-[10px] text-gray-400 uppercase" x-text="selectedService?.unit?.replace('_', ' ') || ''"></div>
                </div>
            </div>
            <input type="hidden" name="service_price" :value="selectedService?.price || ''">
            <input type="hidden" name="service_name" :value="selectedService?.name || ''">
        </div>

        <!-- Common Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guest Name</label>
                <input type="text" name="guest_name" required
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
            <div x-show="requestType !== 'hotel'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                <input type="date" name="date" :required="requestType !== 'hotel'"
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
            <div x-show="requestType !== 'hotel'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Number of Passengers</label>
                <input type="number" name="pax" min="1" value="1"
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
        </div>

        <!-- Transfer-specific -->
        <div x-show="requestType === 'transfer'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Location</label>
                <input type="text" name="pickup_location" placeholder="e.g. Istanbul Airport"
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destination</label>
                <input type="text" name="destination" placeholder="e.g. Taksim Hotel"
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════ -->
        <!-- HOTEL BOOKING — Enhanced with city search + pricing -->
        <!-- ═══════════════════════════════════════════════════ -->
        <div x-show="requestType === 'hotel'" x-transition class="space-y-4">
            <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-800/30 rounded-xl p-4 space-y-4">
                <h3 class="text-sm font-bold text-purple-700 dark:text-purple-300 flex items-center gap-2">
                    <i class="fas fa-hotel"></i> Hotel Booking Details
                </h3>

                <!-- City Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <i class="fas fa-city text-purple-400 mr-1"></i>Filter by City
                    </label>
                    <select x-model="selectedCity" @change="filterHotels()"
                            class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">All Cities</option>
                        <template x-for="c in cities" :key="c">
                            <option :value="c" x-text="c"></option>
                        </template>
                    </select>
                </div>

                <!-- Hotel Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <i class="fas fa-hotel text-purple-400 mr-1"></i>Select Hotel
                        <span class="text-xs text-gray-400 ml-1" x-text="'(' + filteredHotels.length + ' hotels)'"></span>
                    </label>
                    <select name="hotel_name" x-model="selectedHotelId" @change="onHotelSelect()"
                            class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Select a hotel --</option>
                        <template x-for="h in filteredHotels" :key="h.id">
                            <option :value="h.id" x-text="h.name + ' (' + h.city + ') ' + '★'.repeat(h.stars || 0)"></option>
                        </template>
                    </select>
                </div>

                <!-- Room Type Selection (dynamic from hotel_rooms) -->
                <div x-show="availableRooms.length > 0" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <i class="fas fa-bed text-purple-400 mr-1"></i>Room Type & Pricing
                    </label>
                    <select name="room_type" x-model="selectedRoomIdx" @change="calcPrice()"
                            class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Select room type --</option>
                        <template x-for="(rm, idx) in availableRooms" :key="idx">
                            <option :value="idx" x-text="rm.room_type + ' — from ' + Number(rm.price_single).toFixed(2) + ' ' + rm.currency + '/night'"></option>
                        </template>
                    </select>

                    <!-- Room Price Detail Card -->
                    <div x-show="selectedRoom" x-transition class="mt-3 bg-white dark:bg-gray-800 rounded-xl border border-purple-200 dark:border-purple-700 overflow-hidden">
                        <div class="bg-purple-600 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-tags"></i> Price per Night — <span x-text="selectedRoom?.room_type || ''"></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-px bg-gray-200 dark:bg-gray-600">
                            <div class="bg-white dark:bg-gray-800 p-3 text-center">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Single</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="fmtPrice(selectedRoom?.price_single)"></div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-3 text-center">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Double</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="fmtPrice(selectedRoom?.price_double)"></div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-3 text-center">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Triple</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="fmtPrice(selectedRoom?.price_triple)"></div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-3 text-center">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Quad</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="fmtPrice(selectedRoom?.price_quad)"></div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-3 text-center">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Child</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="fmtPrice(selectedRoom?.price_child)"></div>
                            </div>
                        </div>
                        <div class="px-4 py-2 text-xs text-gray-400 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            <span>Board: <strong x-text="selectedRoom?.board_type || 'BB'"></strong> · Season: <strong x-text="selectedRoom?.season || 'all'"></strong> · Currency: <strong x-text="selectedRoom?.currency || 'USD'"></strong></span>
                        </div>
                    </div>
                </div>

                <!-- No rooms message -->
                <div x-show="selectedHotelId && availableRooms.length === 0" class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i> No room pricing available for this hotel. You can still submit the request.
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <i class="fas fa-calendar-check text-emerald-500 mr-1"></i>Check-in
                        </label>
                        <input type="date" name="check_in" x-model="checkIn" @change="calcPrice()"
                               class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <i class="fas fa-calendar-times text-red-400 mr-1"></i>Check-out
                        </label>
                        <input type="date" name="check_out" x-model="checkOut" @change="calcPrice()"
                               class="w-full px-3 sm:px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                </div>

                <!-- Nights -->
                <div x-show="nights > 0" x-transition class="flex items-center gap-2 text-sm text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/30 px-3 py-1.5 rounded-lg w-fit">
                    <i class="fas fa-moon"></i>
                    <span x-text="nights + ' night' + (nights > 1 ? 's' : '')"></span>
                </div>

                <!-- Board Type + Room Count -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Board Type</label>
                        <select name="board_type" class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                            <option value="room_only">Room Only</option>
                            <option value="bed_breakfast" selected>Bed & Breakfast</option>
                            <option value="half_board">Half Board</option>
                            <option value="full_board">Full Board</option>
                            <option value="all_inclusive">All Inclusive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Number of Rooms</label>
                        <input type="number" name="room_count" min="1" x-model.number="roomCount" @input="calcPrice()"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                </div>

                <!-- Guests -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-user mr-1"></i>Adults</label>
                        <input type="number" name="adults" min="1" x-model.number="adults" @input="calcPrice()"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-child mr-1"></i>Children</label>
                        <input type="number" name="children" min="0" x-model.number="children" @input="calcPrice()"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-baby mr-1"></i>Infants</label>
                        <input type="number" name="infants" min="0" x-model.number="infants" @input="calcPrice()"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
                    </div>
                </div>

                <!-- ════════════════════════════════ -->
                <!-- LIVE PRICE ESTIMATE             -->
                <!-- ════════════════════════════════ -->
                <div x-show="estimatedTotal > 0" x-transition
                     class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-4 text-white shadow-lg">
                    <div class="flex items-center gap-2 mb-3 text-emerald-100 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-calculator"></i> Estimated Price
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div>
                            <div class="text-emerald-200 text-[10px] uppercase">Per Night</div>
                            <div class="text-lg font-bold" x-text="fmtPrice(pricePerNight)"></div>
                        </div>
                        <div>
                            <div class="text-emerald-200 text-[10px] uppercase">Nights</div>
                            <div class="text-lg font-bold" x-text="nights"></div>
                        </div>
                        <div>
                            <div class="text-emerald-200 text-[10px] uppercase">Rooms</div>
                            <div class="text-lg font-bold" x-text="roomCount"></div>
                        </div>
                        <div>
                            <div class="text-emerald-200 text-[10px] uppercase">Children</div>
                            <div class="text-lg font-bold" x-text="childrenCost > 0 ? '+' + fmtPrice(childrenCost) : '—'"></div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-white/20 flex items-center justify-between">
                        <span class="text-sm font-medium">Estimated Total</span>
                        <span class="text-2xl font-extrabold" x-text="fmtPrice(estimatedTotal) + ' ' + (selectedRoom?.currency || 'USD')"></span>
                    </div>
                    <p class="text-[10px] text-emerald-200 mt-1">* Estimate only. Final price confirmed by admin.</p>
                </div>
            </div>
        </div>

        <!-- Tour-specific -->
        <div x-show="requestType === 'tour'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tour Name / Destination</label>
                <input type="text" name="tour_name" :placeholder="selectedService?.name || 'e.g. Cappadocia Daily Tour'"
                       :value="selectedService && requestType === 'tour' ? selectedService.name : ''"
                       class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
            <textarea name="notes" rows="3" placeholder="Flight info, special requests, VIP service, etc."
                      class="w-full px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm"></textarea>
        </div>

        <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane"></i> Submit Booking Request
        </button>
    </form>
</div>

<script>
function bookingForm() {
    const serviceData = <?= json_encode($servicesByType, JSON_UNESCAPED_UNICODE) ?>;
    const allHotels = <?= json_encode($hotels, JSON_UNESCAPED_UNICODE) ?>;
    const roomsByHotel = <?= json_encode($roomsByHotel, JSON_UNESCAPED_UNICODE) ?>;
    const cities = <?= json_encode($cities, JSON_UNESCAPED_UNICODE) ?>;

    return {
        requestType: 'transfer',
        selectedServiceId: '',
        selectedService: null,
        servicePrice: '',
        serviceData,
        cities,

        // Hotel booking state
        selectedCity: '',
        selectedHotelId: '',
        selectedRoomIdx: '',
        selectedRoom: null,
        availableRooms: [],
        filteredHotels: allHotels,

        checkIn: '',
        checkOut: '',
        nights: 0,
        roomCount: 1,
        adults: 2,
        children: 0,
        infants: 0,

        // Pricing
        pricePerNight: 0,
        childrenCost: 0,
        estimatedTotal: 0,

        resetAll() {
            this.selectedServiceId = '';
            this.selectedService = null;
            this.servicePrice = '';
            this.selectedCity = '';
            this.selectedHotelId = '';
            this.selectedRoomIdx = '';
            this.selectedRoom = null;
            this.availableRooms = [];
            this.filteredHotels = allHotels;
            this.estimatedTotal = 0;
            this.pricePerNight = 0;
            this.childrenCost = 0;
        },

        filterHotels() {
            if (this.selectedCity) {
                this.filteredHotels = allHotels.filter(h => h.city === this.selectedCity);
            } else {
                this.filteredHotels = allHotels;
            }
            // Reset hotel selection if current hotel is not in filtered list
            if (this.selectedHotelId) {
                const still = this.filteredHotels.find(h => h.id == this.selectedHotelId);
                if (!still) {
                    this.selectedHotelId = '';
                    this.availableRooms = [];
                    this.selectedRoom = null;
                    this.selectedRoomIdx = '';
                    this.estimatedTotal = 0;
                }
            }
        },

        onHotelSelect() {
            const hid = parseInt(this.selectedHotelId);
            if (!hid) {
                this.availableRooms = [];
                this.selectedRoom = null;
                this.selectedRoomIdx = '';
                this.estimatedTotal = 0;
                return;
            }
            this.availableRooms = roomsByHotel[hid] || [];
            this.selectedRoom = null;
            this.selectedRoomIdx = '';
            this.estimatedTotal = 0;

            // Set hotel_name hidden input to hotel name string
            const hotel = allHotels.find(h => h.id == hid);
            if (hotel) {
                // Update the select's value to hotel name for form submission
                const sel = this.$el.querySelector('select[name="hotel_name"]');
                if (sel) {
                    // Add a custom option with the hotel name
                    for (let o of sel.options) {
                        if (o.value == hid) { o.value = hotel.name; break; }
                    }
                }
            }
        },

        onServiceSelect() {
            const id = parseInt(this.selectedServiceId);
            if (!id) { this.selectedService = null; this.servicePrice = ''; return; }
            const all = [...(this.serviceData.transfer || []), ...(this.serviceData.tour || [])];
            const svc = all.find(s => s.id == id);
            if (svc) {
                this.selectedService = svc;
                this.servicePrice = Number(svc.price).toFixed(2) + ' ' + svc.currency;
            }
        },

        calcPrice() {
            // Calculate nights
            if (this.checkIn && this.checkOut) {
                const d1 = new Date(this.checkIn);
                const d2 = new Date(this.checkOut);
                const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                this.nights = diff > 0 ? diff : 0;
            } else {
                this.nights = 0;
            }

            // Calculate room price
            if (this.selectedRoomIdx !== '' && this.availableRooms[this.selectedRoomIdx]) {
                this.selectedRoom = this.availableRooms[this.selectedRoomIdx];
            }

            if (!this.selectedRoom || this.nights === 0) {
                this.estimatedTotal = 0;
                this.pricePerNight = 0;
                this.childrenCost = 0;
                return;
            }

            const rm = this.selectedRoom;
            const ad = Math.max(1, this.adults || 1);

            // Determine per-night rate based on adult count
            let perNight = 0;
            if (ad === 1) perNight = Number(rm.price_single) || 0;
            else if (ad === 2) perNight = Number(rm.price_double) || 0;
            else if (ad === 3) perNight = Number(rm.price_triple) || 0;
            else perNight = Number(rm.price_quad) || 0;

            // Children extra cost
            const childPrice = Number(rm.price_child) || 0;
            const childCount = Math.max(0, this.children || 0);
            this.childrenCost = childPrice * childCount * this.nights;

            this.pricePerNight = perNight;
            this.estimatedTotal = (perNight * this.nights * Math.max(1, this.roomCount)) + this.childrenCost;
        },

        fmtPrice(val) {
            return Number(val || 0).toFixed(2);
        }
    };
}
</script>
