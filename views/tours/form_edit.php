<?php
$customers = json_decode($t['customers'] ?? '[]', true) ?: [['name'=>'']];
$tourItems = json_decode($t['tour_items'] ?? '[]', true) ?: [['name'=>'','date'=>'','duration'=>'']];
$statusOptions = ['pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'];
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-edit text-amber-500 mr-2"></i>Edit: <?= e($t['tour_name']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($t['tour_code'] ?? '') ?></p>
    </div>
</div>

<form method="POST" action="<?= url('tour-voucher/update') ?>" class="space-y-6" x-data="tourEditForm()">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $t['id'] ?>">
    <input type="hidden" name="company_id" id="tour_company_id" value="<?= e($t['company_id'] ?? '') ?>">
    <input type="hidden" name="tour_items" :value="JSON.stringify(tours)">
    <input type="hidden" name="customers" :value="JSON.stringify(guests)">

    <!-- Company Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i>Company Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative" x-data="tourPartnerSearch()" @click.outside="open = false">
                <label class="block text-xs font-medium text-gray-500 mb-1">Company Name</label>
                <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                       autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500">
                <div x-show="open && results.length > 0" x-transition class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="r in results" :key="r.id">
                        <div @click="selectPartner(r)" class="px-3 py-2 cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                            <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                            <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' · ' + r.phone : '')"></div>
                        </div>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer Phone</label>
                <input type="text" name="customer_phone" value="<?= e($t['customer_phone'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hotel Name</label>
                <input type="text" name="hotel_name" value="<?= e($t['hotel_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
            </div>
        </div>
    </div>

    <!-- Guest & Passport -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-passport text-amber-500 mr-1"></i>Guest Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                <input type="text" name="guest_name" value="<?= e($t['guest_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Main guest name">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passenger Passport' ?></label>
                <input type="text" name="passenger_passport" value="<?= e($t['passenger_passport'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Passport number">
            </div>
        </div>
    </div>

    <!-- Location & Details -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Location & Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('city') ?: 'City' ?></label>
                <input type="text" name="city" value="<?= e($t['city'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. Istanbul">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('country') ?: 'Country' ?></label>
                <input type="text" name="country" value="<?= e($t['country'] ?? 'Turkey') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. Turkey">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('address') ?: 'Address' ?></label>
                <input type="text" name="address" value="<?= e($t['address'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Full address">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-map-pin text-purple-500 mr-1"></i><?= __('meeting_point') ?: 'Meeting Point' ?></label>
                <input type="text" name="meeting_point" value="<?= e($t['meeting_point'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. Hotel Lobby">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('meeting_point_address') ?: 'Meeting Point Address' ?></label>
                <input type="text" name="meeting_point_address" value="<?= e($t['meeting_point_address'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Full address of meeting point">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-clock text-blue-500 mr-1"></i><?= __('duration') ?: 'Duration (hours)' ?></label>
                <input type="number" name="duration_hours" step="0.5" min="0" value="<?= (float)($t['duration_hours'] ?? 0) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. 6">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-check-circle text-green-500 mr-1"></i><?= __('includes') ?: 'Includes' ?></label>
                <textarea name="includes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Transport, lunch, guide, entrance fees..."><?= e($t['includes'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><i class="fas fa-times-circle text-red-500 mr-1"></i><?= __('excludes') ?: 'Excludes' ?></label>
                <textarea name="excludes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Personal expenses, tips, drinks..."><?= e($t['excludes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-users text-purple-500 mr-1"></i>Passengers</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Adult</label><input type="number" name="adults" value="<?= $t['adults'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Child</label><input type="number" name="children" value="<?= $t['children'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-500 mb-1">Infant</label><input type="number" name="infants" value="<?= $t['infants'] ?>" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></div>
        </div>
        <!-- Pricing removed — prices are managed via invoices/receipts only -->
    </div>

    <!-- Tour Items -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-route text-purple-500 mr-1"></i>Tours</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr>
                    <th class="text-left pb-2 text-xs font-semibold text-gray-500 uppercase">Tour Name</th>
                    <th class="text-left pb-2 text-xs font-semibold text-gray-500 uppercase">Tour Date</th>
                    <th class="text-left pb-2 text-xs font-semibold text-gray-500 uppercase">Duration</th>
                    <th class="w-10"></th>
                </tr></thead>
                <tbody>
                    <template x-for="(tour, idx) in tours" :key="idx">
                        <tr>
                            <td class="pr-2 pb-2"><input type="text" x-model="tour.name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></td>
                            <td class="pr-2 pb-2"><input type="date" x-model="tour.date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></td>
                            <td class="pr-2 pb-2"><input type="text" x-model="tour.duration" placeholder="e.g. Full Day" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"></td>
                            <td class="pb-2"><button type="button" @click="tours.splice(idx,1)" x-show="tours.length>1" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <button type="button" @click="tours.push({name:'',date:'',duration:''})" class="text-sm text-purple-600 hover:text-purple-700 font-medium mt-2"><i class="fas fa-plus mr-1"></i>Add Tour</button>
    </div>

    <!-- Customers -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4"><i class="fas fa-user-friends text-teal-500 mr-1"></i>Customers</h3>
        <template x-for="(g, idx) in guests" :key="idx">
            <div class="flex items-center gap-2 mb-2">
                <input type="text" x-model="g.name" placeholder="Customer name" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <button type="button" @click="guests.splice(idx, 1)" x-show="guests.length > 1" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-trash"></i></button>
            </div>
        </template>
        <button type="button" @click="guests.push({name:''})" class="text-sm text-teal-600 hover:text-teal-700 font-medium"><i class="fas fa-plus mr-1"></i>Add Customer</button>
    </div>

    <!-- Status -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                    <?php foreach ($statusOptions as $k => $lbl): ?>
                    <option value="<?= $k ?>" <?= $t['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-save mr-2"></i>Update Tour Voucher
        </button>
        <a href="<?= url('tour-voucher/show') ?>?id=<?= $t['id'] ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
    </div>
</form>

<script>
function tourEditForm() {
    return {
        tours: <?= json_encode($tourItems) ?>,
        guests: <?= json_encode($customers) ?>
    };
}
function tourPartnerSearch() {
    return {
        query: '<?= e($t['company_name'] ?? '') ?>',
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
            document.getElementById('tour_company_id').value = r.id;
            const phone = document.querySelector('[name="customer_phone"]');
            if (phone && r.phone) phone.value = r.phone;
        }
    };
}
</script>
