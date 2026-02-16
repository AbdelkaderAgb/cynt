<?php
$customers = json_decode($v['customers'] ?? '[]', true) ?: [['title'=>'Mr','name'=>'']];
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

<form method="POST" action="<?= url('hotel-voucher/update') ?>" class="space-y-6" x-data="hotelEditForm()">
    <input type="hidden" name="id" value="<?= $v['id'] ?>">
    <input type="hidden" name="company_id" id="hotel_company_id" value="<?= e($v['company_id'] ?? '') ?>">

    <!-- Company Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i>Company Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative" x-data="hotelPartnerSearch()" @click.outside="open = false">
                <label class="block text-xs font-medium text-gray-500 mb-1">Company Name *</label>
                <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                       required autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
                <div x-show="open && results.length > 0" x-transition class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="selectPartner(r)" class="px-3 py-2 cursor-pointer hover:bg-teal-50 dark:hover:bg-teal-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
                        </div>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Address</label>
                <input type="text" name="address" value="<?= e($v['address'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Telephone</label>
                <input type="text" name="telephone" value="<?= e($v['telephone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
        </div>
    </div>

    <!-- Hotel & Room Details -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-bed text-teal-500 mr-1"></i>Hotel & Room</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hotel Name *</label>
                <input type="text" name="hotel_name" value="<?= e($v['hotel_name']) ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Room Count</label>
                <input type="number" name="room_count" value="<?= $v['room_count'] ?>" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Check-in *</label>
                <input type="date" name="check_in" value="<?= $v['check_in'] ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nights *</label>
                <input type="number" name="nights" value="<?= $v['nights'] ?>" min="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Room Type</label>
                <select name="room_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($roomTypes as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= $v['room_type'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Board Type</label>
                <select name="board_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($boardTypes as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= $v['board_type'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Transfer Type</label>
                <select name="transfer_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($transferTypes as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= $v['transfer_type'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
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

    <!-- PAX -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-users text-purple-500 mr-1"></i>Guests</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Adults</label><input type="number" name="adults" value="<?= $v['adults'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Children</label><input type="number" name="children" value="<?= $v['children'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Infants</label><input type="number" name="infants" value="<?= $v['infants'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        </div>

        <!-- Dynamic Customer Names -->
        <input type="hidden" name="customers" :value="JSON.stringify(guests)">
        <template x-for="(g, idx) in guests" :key="idx">
            <div class="flex items-center gap-2 mb-2">
                <select x-model="g.title" class="px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm w-20">
                    <option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option><option value="Child">Child</option>
                </select>
                <input type="text" x-model="g.name" placeholder="Guest name" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <button type="button" @click="guests.splice(idx, 1)" x-show="guests.length > 1" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-trash"></i></button>
            </div>
        </template>
        <button type="button" @click="guests.push({title:'Mr',name:''})" class="text-sm text-teal-600 hover:text-teal-700 font-medium"><i class="fas fa-plus mr-1"></i>Add Guest</button>
    </div>

    <!-- Pricing -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-money-bill-wave text-emerald-500 mr-1"></i>Pricing</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Price per Night</label><input type="number" name="price_per_night" value="<?= $v['price_per_night'] ?>" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Total Price</label><input type="number" name="total_price" value="<?= $v['total_price'] ?>" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Currency</label>
                <select name="currency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <option value="USD" <?= $v['currency']==='USD'?'selected':'' ?>>USD</option>
                    <option value="EUR" <?= $v['currency']==='EUR'?'selected':'' ?>>EUR</option>
                    <option value="TRY" <?= $v['currency']==='TRY'?'selected':'' ?>>TRY</option>
                    <option value="GBP" <?= $v['currency']==='GBP'?'selected':'' ?>>GBP</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Special Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <label class="block text-xs font-medium text-gray-500 mb-1">Special Requests</label>
        <textarea name="special_requests" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"><?= e($v['special_requests'] ?? '') ?></textarea>
    </div>

    <!-- Link Additional Services (existing tours & transfers → Guest Program) -->
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
        <div class="space-y-1 min-h-[60px] rounded-lg border border-dashed border-gray-200 dark:border-gray-600 p-2" x-show="linkedList.length > 0">
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
        guests: <?= json_encode($customers) ?>
    };
}
function linkServicesFormEdit(initialList) {
    return {
        serviceQuery: '',
        serviceResults: [],
        serviceResultsOpen: false,
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
            this.serviceResultsOpen = false;
            this.serviceQuery = '';
            this.serviceResults = [];
        },
        removeService(idx) {
            this.linkedList.splice(idx, 1);
        }
    };
}
function hotelPartnerSearch() {
    return {
        query: '<?= e($v['company_name'] ?? '') ?>',
        results: [],
        open: false,
        async search() {
            if (this.query.length < 1) { this.results = []; this.open = false; return; }
            try {
                const res = await fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.open = this.results.length > 0;
            } catch(e) { this.results = []; }
        },
        selectPartner(r) {
            this.query = r.company_name;
            this.open = false;
            document.getElementById('hotel_company_id').value = r.id;
            const tel = document.querySelector('[name="telephone"]');
            const addr = document.querySelector('[name="address"]');
            if (tel && r.phone) tel.value = r.phone;
            if (addr) addr.value = r.address || ((r.city || '') + (r.country ? ', ' + r.country : ''));
        }
    };
}
</script>
