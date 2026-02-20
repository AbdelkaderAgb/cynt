<?php
/**
 * Edit Transfer Form — with Multi-Stop Builder
 */
$stopsInit = json_decode($v['stops_json'] ?? '[]', true);
if (empty($stopsInit) || !is_array($stopsInit)) {
    $stopsInit = [
        ['from' => $v['pickup_location'] ?? '', 'to' => $v['dropoff_location'] ?? '', 'date' => $v['pickup_date'] ?? '', 'time' => $v['pickup_time'] ?? ''],
        ['from' => '', 'to' => '', 'date' => '', 'time' => ''],
    ];
}
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('edit') ?> Transfer — <?= e($v['voucher_no']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($v['pickup_location']) ?> → <?= e($v['dropoff_location']) ?></p>
    </div>
    <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">
        <i class="fas fa-arrow-left"></i> <?= __('back') ?>
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
     x-data="transferEditForm()" x-init="init()">
    <form method="POST" action="<?= url('transfers/update') ?>" @submit.prevent="handleSubmit($el)" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $v['id'] ?>">
        <input type="hidden" name="company_id" id="company_id" value="<?= e($v['company_id'] ?? '') ?>">
        <input type="hidden" name="stops_json" :value="JSON.stringify(stops)">

        <!-- Company -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-400 mr-1"></i> Company</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative" x-data="editPartnerSearch()" @click.outside="open = false">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company *</label>
                    <input type="text" name="company_name" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open=true"
                           required autocomplete="off" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Search or type company name">
                    <div x-show="open && results.length > 0" x-transition
                         class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="r in results" :key="r.id">
                            <div @click="selectPartner(r)" class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-gray-100 dark:border-gray-600 last:border-0 transition">
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200" x-text="r.company_name"></div>
                                <div class="text-xs text-gray-400" x-text="(r.contact_person || '') + (r.phone ? ' - ' + r.phone : '')"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-phone text-green-500 mr-1"></i>Phone</label>
                    <input type="text" name="company_phone" id="company_phone" readonly class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-gray-500" placeholder="Auto-filled from partner">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Address</label>
                <input type="text" name="company_address" id="company_address" readonly class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm text-gray-500" placeholder="Auto-filled from partner">
            </div>
        </div>

        <!-- Transfer Type & Pax -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-route text-blue-400 mr-1"></i> Transfer Info</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transfer Type</label>
                    <select name="transfer_type" x-model="transferType" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="one_way">One Way</option>
                        <option value="round_trip">Round Trip</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Pax</label>
                    <input type="number" name="total_pax" value="<?= $v['total_pax'] ?? 1 ?>" min="1" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                    <select name="currency" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <?php foreach (['USD','EUR','TRY','GBP'] as $cur): ?>
                        <option value="<?= $cur ?>" <?= ($v['currency'] ?? 'USD') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ONE WAY / ROUND TRIP route -->
        <div x-show="transferType !== 'multi_stop'" x-transition class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-map-signs text-teal-400 mr-1"></i> Route & Schedule</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-circle text-teal-400 text-xs mr-1"></i>Starting Point *</label>
                    <input type="text" name="pickup_location" value="<?= e($v['pickup_location'] ?? '') ?>" :required="transferType !== 'multi_stop'" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-map-marker-alt text-rose-400 text-xs mr-1"></i>Destination *</label>
                    <input type="text" name="dropoff_location" value="<?= e($v['dropoff_location'] ?? '') ?>" :required="transferType !== 'multi_stop'" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Date *</label>
                    <input type="date" name="pickup_date" value="<?= e($v['pickup_date'] ?? '') ?>" :required="transferType !== 'multi_stop'" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup Time *</label>
                    <input type="time" name="pickup_time" value="<?= e($v['pickup_time'] ?? '') ?>" :required="transferType !== 'multi_stop'" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div x-show="transferType === 'round_trip'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Return Date</label>
                    <input type="date" name="return_date" value="<?= e($v['return_date'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div x-show="transferType === 'round_trip'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Return Time</label>
                    <input type="time" name="return_time" value="<?= e($v['return_time'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- MULTI STOP: dynamic stops builder -->
        <div x-show="transferType === 'multi_stop'" x-transition class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider"><i class="fas fa-map-signs text-blue-400 mr-1"></i> Stops</h3>
                <button type="button" @click="addStop()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-semibold hover:bg-blue-100 transition">
                    <i class="fas fa-plus text-[10px]"></i> Add Stop
                </button>
            </div>

            <div class="relative">
                <div class="absolute left-5 top-6 bottom-6 w-0.5 bg-gray-200 dark:bg-gray-600 z-0" x-show="stops.length > 1"></div>
                <div class="space-y-3">
                    <template x-for="(stop, idx) in stops" :key="idx">
                        <div class="relative flex gap-4 items-start">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center z-10 text-white text-xs font-bold shadow-md"
                                 :class="idx === 0 ? 'bg-teal-500' : (idx === stops.length - 1 ? 'bg-rose-500' : 'bg-blue-500')"
                                 x-text="idx + 1"></div>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                          x-text="idx === 0 ? 'Starting Point' : (idx === stops.length - 1 ? 'Final Destination' : 'Stop ' + (idx + 1))"></span>
                                    <button type="button" @click="removeStop(idx)"
                                            x-show="stops.length > 2"
                                            class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-400 mb-1"><i class="fas fa-circle text-teal-400 mr-1" style="font-size:8px"></i>From</label>
                                        <input type="text" x-model="stop.from" placeholder="Pickup point"
                                               class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-400 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i>To</label>
                                        <input type="text" x-model="stop.to" placeholder="Drop-off point"
                                               class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-400 mb-1"><i class="fas fa-calendar mr-1" style="font-size:8px"></i>Date</label>
                                        <input type="date" x-model="stop.date"
                                               class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-400 mb-1"><i class="fas fa-clock mr-1" style="font-size:8px"></i>Time</label>
                                        <input type="time" x-model="stop.time"
                                               class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-4 flex justify-center" x-show="stops.length >= 2">
                    <button type="button" @click="addStop()"
                            class="inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-blue-300 dark:border-blue-600 text-blue-500 dark:text-blue-400 rounded-xl text-sm font-medium hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                        <i class="fas fa-plus-circle"></i> Add Another Stop
                    </button>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-gray-400 bg-blue-50 dark:bg-blue-900/20 px-4 py-2.5 rounded-xl border border-blue-100 dark:border-blue-800">
                <i class="fas fa-info-circle text-blue-400"></i>
                <span x-text="stops.length + ' stops total · ' + (stops[0]?.from || '—') + ' → ' + (stops[stops.length-1]?.to || '—')"></span>
            </div>
        </div>

        <!-- Guest & Passport -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-user text-blue-400 mr-1"></i> Passenger Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('guest_name') ?: 'Guest Name' ?></label>
                    <input type="text" name="guest_name" value="<?= e($v['guest_name'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Main guest name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passport No.' ?></label>
                    <input type="text" name="passenger_passport" value="<?= e($v['passenger_passport'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Passport number">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">All Passenger Names</label>
                    <textarea name="passengers" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"><?= e($v['passengers'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-plane text-indigo-500 mr-1"></i>Flight Number</label>
                    <input type="text" name="flight_number" value="<?= e($v['flight_number'] ?? '') ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g. TK123">
                </div>
            </div>
        </div>

        <!-- Vehicle / Driver / Guide -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-car text-blue-400 mr-1"></i> Assignment <span class="font-normal text-gray-400 normal-case">(Optional)</span></h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vehicle</label>
                    <select name="vehicle_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($vehicles ?? [] as $vh): ?>
                        <option value="<?= $vh['id'] ?>" <?= ($v['vehicle_id'] ?? '') == $vh['id'] ? 'selected' : '' ?>><?= e($vh['plate_number']) ?> — <?= e($vh['make'] . ' ' . $vh['model']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver</label>
                    <select name="driver_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($drivers ?? [] as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($v['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name'] ?? trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tour Guide</label>
                    <select name="guide_id" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($guides ?? [] as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ($v['guide_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= e($g['name'] ?? trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="border-b border-gray-100 dark:border-gray-700 pb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4"><i class="fas fa-flag text-blue-400 mr-1"></i> Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('status') ?></label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                        <?php foreach (['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'] as $sk => $sl): ?>
                        <option value="<?= $sk ?>" <?= ($v['status'] ?? '') === $sk ? 'selected' : '' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><i class="fas fa-sticky-note text-amber-400 mr-1"></i>Notes</label>
            <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500"><?= e($v['notes'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= url('transfers/show') ?>?id=<?= $v['id'] ?>" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 transition">Cancel</a>
            <button type="submit" :disabled="submitting" :class="{'opacity-50 cursor-not-allowed':submitting}" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i><span x-text="submitting ? 'Saving…' : 'Update Transfer'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function transferEditForm() {
    return {
        transferType: '<?= e($v['transfer_type'] ?? 'one_way') ?>',
        stops: <?= json_encode($stopsInit) ?>,
        submitting: false,

        init() {
            this.$watch('transferType', (val) => {
                if (val === 'multi_stop' && this.stops.length < 2) {
                    this.stops = [
                        { from: '', to: '', date: '', time: '' },
                        { from: '', to: '', date: '', time: '' }
                    ];
                }
            });
            // Auto-fill partner phone/address on load
            const companyName = '<?= addslashes(e($v['company_name'] ?? '')) ?>';
            if (companyName) {
                fetch('<?= url('api/partners/search') ?>?q=' + encodeURIComponent(companyName))
                    .then(r => r.json())
                    .then(partners => {
                        const match = partners.find(p => p.company_name === companyName);
                        if (match) {
                            const phoneEl = document.getElementById('company_phone');
                            const addrEl  = document.getElementById('company_address');
                            if (phoneEl) phoneEl.value = match.phone || '';
                            if (addrEl)  addrEl.value  = [match.address, match.city, match.country].filter(Boolean).join(', ');
                        }
                    }).catch(() => {});
            }
        },

        addStop() {
            this.stops.push({ from: '', to: '', date: '', time: '' });
        },

        removeStop(idx) {
            if (this.stops.length > 2) this.stops.splice(idx, 1);
        },

        handleSubmit(form) {
            if (this.transferType === 'multi_stop') {
                const pickupEl  = form.querySelector('[name="pickup_location"]');
                const dropoffEl = form.querySelector('[name="dropoff_location"]');
                const dateEl    = form.querySelector('[name="pickup_date"]');
                const timeEl    = form.querySelector('[name="pickup_time"]');
                if (pickupEl)  pickupEl.value  = this.stops[0]?.from || '';
                if (dropoffEl) dropoffEl.value = this.stops[this.stops.length - 1]?.to || '';
                if (dateEl)    dateEl.value    = this.stops[0]?.date || '';
                if (timeEl)    timeEl.value    = this.stops[0]?.time || '';
            }
            this.submitting = true;
            form.submit();
        }
    };
}

function editPartnerSearch() {
    return {
        query: '<?= addslashes(e($v['company_name'] ?? '')) ?>',
        results: [], open: false,
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
            document.getElementById('company_id').value = r.id;
            const phoneEl = document.getElementById('company_phone');
            const addrEl  = document.getElementById('company_address');
            if (phoneEl) phoneEl.value = r.phone || '';
            if (addrEl)  addrEl.value  = [r.address, r.city, r.country].filter(Boolean).join(', ');
        }
    };
}
</script>
