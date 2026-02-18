<?php
$customersRaw = json_decode($v['customers'] ?? '[]', true) ?: [['title'=>'Mr','name'=>'']];
// Ensure guests have age+passport fields for backward compat
$guestsInit = array_map(function($g) {
    return [
        'title' => $g['title'] ?? 'Mr',
        'name' => $g['name'] ?? '',
        'age' => $g['age'] ?? '',
        'passport' => $g['passport'] ?? '',
    ];
}, $customersRaw);
// Build initial rooms array from existing data
$roomsInit = [['type' => $v['room_type'] ?? 'DBL', 'board' => $v['board_type'] ?? 'bed_breakfast', 'adults' => (int)($v['adults'] ?? 2), 'children' => (int)($v['children'] ?? 0)]];
// If rooms_json was stored, use that instead
if (!empty($v['rooms_json'])) {
    $decoded = json_decode($v['rooms_json'], true);
    if (is_array($decoded) && count($decoded)) $roomsInit = $decoded;
}
$statusOptions = ['pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'];
?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'capacity' && !empty($_GET['message'])): ?>
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl flex items-center gap-2">
    <i class="fas fa-exclamation-triangle"></i> <?= e(urldecode((string)$_GET['message'])) ?>
</div>
<?php endif; ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-edit text-amber-500 mr-2"></i>Edit: <?= e($v['voucher_no']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($v['hotel_name']) ?></p>
    </div>
</div>

<form method="POST" action="<?= url('hotel-voucher/update') ?>" class="space-y-6" x-data="hotelEditForm()" @submit.prevent="prepareSubmit($el)">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $v['id'] ?>">
    <input type="hidden" name="company_id" :value="companyId">

    <!-- Company Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i>Company Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative" @click.outside="partnerOpen = false">
                <label class="block text-xs font-medium text-gray-500 mb-1">Company Name *</label>
                <input type="text" name="company_name" x-model="companyName" @input.debounce.300ms="searchPartner()" @focus="if(partnerResults.length) partnerOpen=true"
                       required autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                <div x-show="partnerOpen && partnerResults.length > 0" x-transition class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="r in partnerResults" :key="r.id">
                        <div @click="selectPartner(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
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

    <!-- Hotel & Stay -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-bed text-teal-500 mr-1"></i>Hotel & Stay</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hotel Name *</label>
                <input type="text" name="hotel_name" value="<?= e($v['hotel_name']) ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Transfer Type</label>
                <select name="transfer_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($transferTypes as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= ($v['transfer_type'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('check_in_date') ?: 'Check-in' ?> *</label>
                <input type="date" name="check_in" x-model="checkIn" @change="onCheckInChange()" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('check_out_date') ?: 'Check-out' ?> *</label>
                <input type="date" name="check_out" x-model="checkOut" @change="onCheckOutChange()" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('nights') ?: 'Nights' ?></label>
                <input type="number" name="nights" x-model.number="nights" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm bg-gray-50 dark:bg-gray-600" readonly>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($statusOptions as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= $v['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Rooms -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
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
        <div class="space-y-3">
            <template x-for="(room, ri) in rooms" :key="ri">
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-500 uppercase" x-text="'Room ' + (ri+1)"></span>
                        <button type="button" @click="rooms.splice(ri, 1)" x-show="rooms.length > 1" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Room Type</label>
                            <select x-model="room.type" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <option value="">-- Select --</option>
                                <?php foreach ($roomTypes as $k => $lbl): ?>
                                <option value="<?= $k ?>"><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Board</label>
                            <select x-model="room.board" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                                <?php foreach ($boardTypes as $k => $lbl): ?>
                                <option value="<?= $k ?>"><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Adults</label>
                            <input type="number" x-model.number="room.adults" min="0" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 mb-0.5">Children</label>
                            <input type="number" x-model.number="room.children" min="0" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
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

    <!-- Guests -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
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
                    <select x-model="g.title" class="w-20 px-1.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-xs">
                        <option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option>
                        <option value="Miss">Miss</option><option value="Dr">Dr</option>
                        <option value="Child">Child</option><option value="Infant">Infant</option>
                    </select>
                    <input type="text" x-model="g.name" placeholder="Full Name *" class="flex-1 min-w-[120px] px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                    <input type="number" x-model="g.age" placeholder="Age" min="0" max="120" class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-center">
                    <input type="text" x-model="g.passport" placeholder="Passport No." class="w-32 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <button type="button" @click="guests.splice(gi, 1)" x-show="guests.length > 1" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </template>
        </div>
        <p class="text-[10px] text-gray-400 mt-1.5">Age and passport are optional. First guest is used as the main guest name on the voucher.</p>
    </div>

    <!-- Special Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <label class="block text-xs font-medium text-gray-500 mb-1">Special Requests</label>
        <textarea name="special_requests" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"><?= e($v['special_requests'] ?? '') ?></textarea>
    </div>

    <!-- Link Additional Services -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="linkServicesFormEdit(<?= htmlspecialchars(json_encode($linkedServicesForEdit ?? []), ENT_QUOTES, 'UTF-8') ?>)">
        <label class="block text-xs font-medium text-gray-500 mb-2">Link Additional Services</label>
        <p class="text-[10px] text-gray-400 mb-2">Search and link existing tours or transfers. They appear as Guest Program on the voucher.</p>
        <div class="relative mb-2" @click.outside="serviceResultsOpen = false">
            <input type="text" x-model="serviceQuery" @input.debounce.200ms="searchServices()" @focus="if(serviceResults.length) serviceResultsOpen = true"
                   placeholder="Search tours or transfers..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
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
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-xl font-semibold shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i>Update Voucher
        </button>
        <a href="<?= url('hotel-voucher/show') ?>?id=<?= $v['id'] ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
    </div>
</form>

<script>
function hotelEditForm() {
    return {
        // Company
        companyId: '<?= e($v['company_id'] ?? '') ?>',
        companyName: '<?= e($v['company_name'] ?? '') ?>',
        companyAddress: '<?= e($v['address'] ?? '') ?>',
        companyPhone: '<?= e($v['telephone'] ?? '') ?>',
        partnerResults: [], partnerOpen: false,
        // Dates
        checkIn: '<?= $v['check_in'] ?>',
        checkOut: '<?= $v['check_out'] ?? '' ?>',
        nights: <?= (int)$v['nights'] ?>,
        // Rooms
        rooms: <?= json_encode($roomsInit) ?>,
        // Guests
        guests: <?= json_encode($guestsInit) ?>,

        init() {
            if (this.checkIn && !this.checkOut && this.nights > 0) {
                const d = new Date(this.checkIn); d.setDate(d.getDate() + this.nights);
                this.checkOut = d.toISOString().split('T')[0];
            }
        },

        // Partner search
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

        // Date / nights
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

        // Rooms
        addRoom() { this.rooms.push({ type: 'DBL', board: 'bed_breakfast', adults: 2, children: 0 }); },
        totalAdults() { return this.rooms.reduce((s, r) => s + (r.adults || 0), 0); },
        totalChildren() { return this.rooms.reduce((s, r) => s + (r.children || 0), 0); },
        totalPax() { return this.totalAdults() + this.totalChildren(); },

        // Guests
        addGuest() { this.guests.push({ title: 'Mr', name: '', age: '', passport: '' }); },

        // Submit
        prepareSubmit(form) { form.submit(); }
    };
}
function linkServicesFormEdit(initialList) {
    return {
        serviceQuery: '', serviceResults: [], serviceResultsOpen: false,
        linkedList: Array.isArray(initialList) ? initialList.map(function(x) { return { type: x.type, id: x.id, label: x.label || (x.type + ' #' + x.id) }; }) : [],
        async searchServices() {
            if (this.serviceQuery.length < 2) { this.serviceResults = []; this.serviceResultsOpen = false; return; }
            try {
                const res = await fetch('<?= url('api/search-services') ?>?q=' + encodeURIComponent(this.serviceQuery));
                this.serviceResults = await res.json();
                this.serviceResultsOpen = this.serviceResults.length > 0;
            } catch(e) { this.serviceResults = []; }
        },
        addService(r) {
            if (this.linkedList.some(function(x) { return x.type === r.type && x.id === r.id; })) return;
            this.linkedList.push({ type: r.type, id: r.id, label: r.label });
            this.serviceResultsOpen = false; this.serviceQuery = ''; this.serviceResults = [];
        },
        removeService(idx) { this.linkedList.splice(idx, 1); }
    };
}
</script>
