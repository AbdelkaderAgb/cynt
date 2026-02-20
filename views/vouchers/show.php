<?php
/**
 * Voucher Detail View — with PDF/Email/WhatsApp export
 */
$v = $voucher;
$statuses = ['pending' => __('pending') ?: 'Pending', 'confirmed' => __('confirmed') ?: 'Confirmed', 'completed' => __('completed') ?: 'Completed', 'cancelled' => __('cancelled') ?: 'Cancelled', 'no_show' => 'No Show'];
?>
<script>
function voucherShowData() {
    return {
        showShare: false, shareTab: 'email', sending: false, sent: false, error: '',
        vStatus: '<?= e($v['status']) ?>',
        statusSaving: false, statusSaved: false, statusError: '',
        statusClasses: {
            pending:   'bg-amber-100 text-amber-700',
            confirmed: 'bg-blue-100 text-blue-700',
            completed: 'bg-emerald-100 text-emerald-700',
            cancelled: 'bg-red-100 text-red-700',
            no_show:   'bg-gray-100 text-gray-600'
        },
        statusLabels: <?= json_encode($statuses, JSON_HEX_TAG | JSON_HEX_APOS) ?>,
        async changeStatus(val) {
            this.statusSaving = true; this.statusSaved = false; this.statusError = '';
            try {
                const r = await fetch('<?= url('vouchers/update-status') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: <?= (int)$v['id'] ?>, status: val })
                });
                const d = await r.json();
                if (d.success) {
                    this.vStatus = val;
                    this.statusSaved = true;
                    setTimeout(() => { this.statusSaved = false; }, 2500);
                } else {
                    this.statusError = d.message || 'Failed';
                }
            } catch(e) { this.statusError = 'Network error'; }
            this.statusSaving = false;
        }
    };
}
</script>
<div x-data="voucherShowData()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2 flex-wrap">
            <span class="font-mono"><?= e($v['voucher_no']) ?></span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                  :class="statusClasses[vStatus] || 'bg-gray-100 text-gray-600'"
                  x-text="statusLabels[vStatus] || vStatus"></span>
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= __('created_at') ?>: <?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></p>
    </div>
    <!-- Action bar: scrollable on mobile -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 sm:flex-wrap">
        <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>" target="_blank"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-file-pdf"></i><span class="hidden sm:inline">PDF</span>
        </a>
        <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>&print=1" target="_blank"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-print"></i><span class="hidden sm:inline"><?= __('print') ?></span>
        </a>
        <button @click="showShare = true"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-share-alt"></i><span class="hidden sm:inline">Share</span>
        </button>
        <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>&download=1"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition">
            <i class="fas fa-download"></i><span class="hidden sm:inline"><?= __('download') ?></span>
        </a>
        <a href="<?= url('vouchers/edit') ?>?id=<?= $v['id'] ?>"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition">
            <i class="fas fa-edit"></i><span class="hidden sm:inline"><?= __('edit') ?></span>
        </a>
        <a href="<?= url('vouchers') ?>"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i><span class="hidden sm:inline"><?= __('back') ?></span>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        <?php
        $vStops = array_values(array_filter(json_decode($v['stops_json'] ?? '[]', true) ?: []));
        $isVMulti = ($v['transfer_type'] ?? 'one_way') === 'multi_stop' && !empty($vStops);
        $typeLabel = ['one_way' => 'One Way', 'round_trip' => 'Round Trip', 'multi_stop' => 'Multi Stop'][$v['transfer_type'] ?? 'one_way'] ?? $v['transfer_type'];
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2"><i class="fas fa-route text-blue-500"></i><?= __('transfer_details', [], 'Transfer Details') ?></h3>

            <!-- Company info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 pb-5 border-b border-gray-100 dark:border-gray-700">
                <div><p class="text-xs text-gray-400 mb-1"><?= __('company_name') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['company_name']) ?></p></div>
                <?php if (!empty($v['partner_phone'] ?? '')): ?>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-phone text-green-500 mr-1"></i>Phone</p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['partner_phone']) ?></p></div>
                <?php endif; ?>
                <?php if (!empty($v['partner_address'] ?? '')): ?>
                <div class="sm:col-span-2"><p class="text-xs text-gray-400 mb-1"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Address</p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['partner_address']) ?></p></div>
                <?php endif; ?>
                <?php if (!empty($v['guest_name'])): ?>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-user text-blue-400 mr-1"></i>Guest Name</p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['guest_name']) ?></p></div>
                <?php endif; ?>
                <?php if (!empty($v['passenger_passport'])): ?>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-passport text-amber-500 mr-1"></i>Passport No.</p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['passenger_passport']) ?></p></div>
                <?php endif; ?>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('transfer_type') ?></p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($typeLabel) ?>
                    <?php if ($isVMulti): ?><span class="ml-1 text-xs text-blue-500 font-normal">(<?= count($vStops) + 1 ?> legs)</span><?php endif; ?>
                    </p>
                </div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('flight_number') ?></p><p class="font-semibold"><?= e($v['flight_number'] ?: '—') ?></p></div>
            </div>

            <!-- Route: Multi-Stop timeline -->
            <?php if ($isVMulti):
                $allVLegs = array_merge(
                    [['from' => $v['pickup_location'] ?? '', 'to' => $v['dropoff_location'] ?? '', 'date' => $v['pickup_date'] ?? '', 'time' => $v['pickup_time'] ?? '']],
                    $vStops
                );
            ?>
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3"><i class="fas fa-map-signs text-teal-400 mr-1"></i> Route</h4>
            <div class="space-y-1">
                <?php foreach ($allVLegs as $si => $leg):
                    $isFirst = $si === 0; $isLast = $si === count($allVLegs) - 1;
                    $colors = $isFirst ? ['bg-teal-500','border-teal-200 dark:border-teal-800','bg-teal-50 dark:bg-teal-900/10','text-teal-700 dark:text-teal-400']
                                       : ($isLast ? ['bg-rose-500','border-rose-200 dark:border-rose-800','bg-rose-50 dark:bg-rose-900/10','text-rose-700 dark:text-rose-400']
                                                  : ['bg-blue-500','border-blue-200 dark:border-blue-800','bg-blue-50 dark:bg-blue-900/10','text-blue-700 dark:text-blue-400']);
                    $label = $isFirst ? 'Main Transfer' : ($isLast ? 'Final Transfer' : 'Transfer ' . ($si + 1));
                    $d = $leg['date'] ?? $leg['pickup_date'] ?? '';
                    $t = $leg['time'] ?? $leg['pickup_time'] ?? '';
                ?>
                <div class="flex gap-3 items-stretch">
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-7 h-7 rounded-full <?= $colors[0] ?> text-white text-xs font-bold flex items-center justify-center"><?= $si + 1 ?></div>
                        <?php if (!$isLast): ?><div class="w-0.5 bg-gray-200 dark:bg-gray-700 flex-1 my-1"></div><?php endif; ?>
                    </div>
                    <div class="border <?= $colors[1] ?> rounded-xl p-3 mb-1 flex-1 <?= $colors[2] ?>">
                        <p class="text-[10px] font-bold uppercase tracking-wider <?= $colors[3] ?> mb-2"><?= $label ?></p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= e($leg['from'] ?? $leg['pickup_location'] ?? '—') ?></span>
                            <i class="fas fa-arrow-right text-gray-400 text-xs flex-shrink-0"></i>
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200"><?= e($leg['to'] ?? $leg['dropoff_location'] ?? '—') ?></span>
                        </div>
                        <?php if ($d && $d !== '1970-01-01'): ?>
                        <p class="text-xs text-gray-400 mt-1"><i class="fas fa-calendar mr-1"></i><?= date('d/m/Y', strtotime($d)) ?><?= $t ? ' · ' . e($t) : '' ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <!-- Simple one-way / round-trip -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-circle text-teal-400 mr-1" style="font-size:8px"></i><?= __('pickup_location') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['pickup_location']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-map-marker-alt text-rose-400 mr-1" style="font-size:8px"></i><?= __('dropoff_location') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['dropoff_location']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-calendar text-blue-400 mr-1"></i><?= __('pickup_date') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= $v['pickup_date'] && $v['pickup_date'] !== '1970-01-01' ? date('d/m/Y', strtotime($v['pickup_date'])) : '—' ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-clock text-blue-400 mr-1"></i><?= __('pickup_time') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['pickup_time'] ?: '—') ?></p></div>
                <?php if (($v['transfer_type'] ?? 'one_way') === 'round_trip' && !empty($v['return_date']) && $v['return_date'] !== '1970-01-01'): ?>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-undo-alt text-indigo-400 mr-1"></i><?= __('return_date') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= date('d/m/Y', strtotime($v['return_date'])) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><i class="fas fa-undo-alt text-indigo-400 mr-1"></i><?= __('return_time') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['return_time'] ?: '—') ?></p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php
        // Parse guests (same logic as PDF)
        $rawPass = $v['passengers'] ?? '';
        $showGuests = [];
        if (!empty($rawPass) && substr(trim($rawPass), 0, 1) === '[') {
            $showGuests = json_decode($rawPass, true) ?: [];
        }
        if (empty($showGuests) && !empty($v['guest_name'])) {
            $showGuests[] = ['name' => $v['guest_name'], 'passport' => $v['passenger_passport'] ?? ''];
            if (!empty($rawPass) && substr(trim($rawPass), 0, 1) !== '[') {
                foreach (array_filter(array_map('trim', explode("\n", $rawPass))) as $line) {
                    if ($line !== $v['guest_name']) $showGuests[] = ['name' => $line, 'passport' => ''];
                }
            }
        }
        ?>
        <?php if (!empty($showGuests)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                <i class="fas fa-users text-purple-500"></i> Guests
                <span class="ml-auto text-xs font-normal bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-2 py-0.5 rounded-full"><?= count($showGuests) ?> guest<?= count($showGuests) !== 1 ? 's' : '' ?></span>
            </h3>
            <div class="space-y-2">
                <?php foreach ($showGuests as $gi => $g): ?>
                <div class="flex items-center gap-3 <?= $gi === 0 ? 'bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700' : 'bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600' ?> rounded-xl px-3 py-2.5">
                    <div class="w-7 h-7 rounded-full <?= $gi === 0 ? 'bg-amber-500' : 'bg-gray-400 dark:bg-gray-500' ?> text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        <?= $gi + 1 ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate"><?= e($g['name'] ?? '') ?>
                            <?php if ($gi === 0): ?><span class="ml-1 text-[10px] text-amber-500 font-bold">★ Main</span><?php endif; ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <i class="fas fa-passport text-xs <?= !empty($g['passport']) ? 'text-amber-500' : 'text-gray-300' ?>"></i>
                        <span class="text-xs <?= !empty($g['passport']) ? 'font-mono text-gray-700 dark:text-gray-300' : 'text-gray-400 italic' ?>"><?= e($g['passport'] ?: 'No passport') ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($v['notes'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2"><i class="fas fa-sticky-note text-amber-500"></i><?= __('notes') ?></h3>
            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line text-sm"><?= e($v['notes']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Details card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-info-circle text-blue-500 mr-2"></i><?= __('details') ?></h3>
            <div class="space-y-3">
                <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('total_pax') ?></span><span class="font-bold text-lg text-blue-600"><?= $v['total_pax'] ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('payment_status') ?></span><span class="text-sm font-medium"><?= $v['payment_status'] ?? 'unpaid' ?></span></div>
            </div>
        </div>

        <!-- Inline Status Changer -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fas fa-tag text-indigo-400"></i> Voucher Status
            </h3>
            <div class="mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-semibold rounded-full"
                      :class="statusClasses[vStatus] || 'bg-gray-100 text-gray-600'">
                    <i class="fas fa-circle text-[8px]"></i>
                    <span x-text="statusLabels[vStatus] || vStatus"></span>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-1.5">
                <?php foreach (['pending' => ['fa-clock','amber'], 'confirmed' => ['fa-check','blue'], 'completed' => ['fa-check-double','emerald'], 'cancelled' => ['fa-times','red'], 'no_show' => ['fa-user-slash','gray']] as $s => [$icon, $color]): ?>
                <button @click="changeStatus('<?= $s ?>')"
                        :disabled="statusSaving || vStatus === '<?= $s ?>'"
                        :class="vStatus === '<?= $s ?>' ? 'ring-2 ring-<?= $color ?>-500 bg-<?= $color ?>-50 dark:bg-<?= $color ?>-900/20' : 'bg-gray-50 dark:bg-gray-700/50 hover:bg-<?= $color ?>-50 dark:hover:bg-<?= $color ?>-900/20'"
                        class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition disabled:opacity-40">
                    <i class="fas <?= $icon ?> text-<?= $color ?>-500 w-3"></i>
                    <?= $statuses[$s] ?? ucfirst($s) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div x-show="statusSaving" class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                <i class="fas fa-spinner fa-spin"></i> Saving…
            </div>
            <div x-show="statusSaved" x-cloak class="mt-2 text-xs text-emerald-600 flex items-center gap-1">
                <i class="fas fa-check-circle"></i> Saved!
            </div>
            <div x-show="statusError" x-cloak class="mt-2 text-xs text-red-600" x-text="statusError"></div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div x-show="showShare" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showShare = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6" @click.outside="showShare = false">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2"><i class="fas fa-share-alt text-blue-500"></i> Share Document</h2>
            <button @click="showShare = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex gap-2 mb-5">
            <button @click="shareTab = 'email'; error = ''; sent = false" :class="shareTab === 'email' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2"><i class="fas fa-envelope"></i> Email</button>
            <button @click="shareTab = 'whatsapp'; error = ''; sent = false" :class="shareTab === 'whatsapp' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2"><i class="fab fa-whatsapp"></i> WhatsApp</button>
        </div>
        <div x-show="sent" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2"><i class="fas fa-check-circle"></i> <span x-text="shareTab === 'email' ? 'Email sent successfully!' : 'Redirecting to WhatsApp...'"></span></div>
        <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <span x-text="error"></span></div>
        <!-- Email Form -->
        <form x-show="shareTab === 'email'" @submit.prevent="
            sending = true; error = ''; sent = false;
            fetch('<?= url('export/email') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ type: 'voucher', id: '<?= $v['id'] ?>', email: $refs.emailTo.value, subject: $refs.emailSubject.value, message: $refs.emailMessage.value })
            }).then(r => r.json()).then(d => { sending = false; if(d.success) { sent = true; } else { error = d.message || 'Failed to send.'; } }).catch(() => { sending = false; error = 'Network error.'; });
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recipient Email</label><input x-ref="emailTo" type="email" required placeholder="recipient@example.com" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Subject</label><input x-ref="emailSubject" type="text" value="<?= htmlspecialchars($v['voucher_no']) ?> — <?= htmlspecialchars(COMPANY_NAME) ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message (optional)</label><textarea x-ref="emailMessage" rows="3" placeholder="Additional message..." class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea></div>
            <button type="submit" :disabled="sending" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2"><i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i><span x-text="sending ? 'Sending...' : 'Send Email with PDF'"></span></button>
        </form>
        <!-- WhatsApp Form -->
        <form x-show="shareTab === 'whatsapp'" @submit.prevent="
            const phone = $refs.waPhone.value.replace(/[^0-9]/g, '');
            window.open('<?= url('export/whatsapp') ?>?type=voucher&id=<?= $v['id'] ?>&phone=' + phone, '_blank');
            sent = true;
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number (with country code)</label><input x-ref="waPhone" type="tel" placeholder="+905551234567" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></div>
            <p class="text-xs text-gray-400">Leave empty to open WhatsApp without a specific recipient.</p>
            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2"><i class="fab fa-whatsapp text-lg"></i> Share via WhatsApp</button>
        </form>
    </div>
</div>
</div>
