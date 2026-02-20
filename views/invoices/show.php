<?php
$inv = $invoice;
$sc = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700'][$inv['status']] ?? 'bg-gray-100 text-gray-600';
$sl = __($inv['status'] ?? 'draft') ?: ucfirst($inv['status'] ?? 'draft');
$balanceDue = round((float)($inv['total_amount'] ?? 0) - (float)($inv['paid_amount'] ?? 0), 2);

// Resolve partner ID: company_id is definitive (set at invoice creation)
$resolvedPartnerId = (int)($partnerId ?? 0)
    ?: ((int)($inv['company_id']  ?? 0)
    ?: (int)($inv['partner_id']   ?? 0));

// Show Pay with Credit button if: a partner is linked, balance still due, and has credit
$canPayByCredit = $resolvedPartnerId > 0
    && $balanceDue > 0
    && ($inv['status'] ?? '') !== 'paid'
    && (float)($partnerBalance ?? 0) > 0;
?>

<?php if (!empty($flash)): ?>
<div class="mb-5 flex items-center gap-3 p-4 rounded-xl border text-sm font-medium
    <?= $flash['type'] === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-400'
        : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-700 dark:text-red-400' ?>">
    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-lg flex-shrink-0"></i>
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<script>
function invoiceShowData() {
    return {
        showShare:      false,
        shareTab:       'email',
        sending:        false,
        sent:           false,
        error:          '',
        showCreditPay:  false,
        partnerBalance: <?= number_format(max(0, (float)($partnerBalance ?? 0)), 4, '.', '') ?>,
        balanceDue:     <?= number_format(max(0, $balanceDue), 4, '.', '') ?>,
        creditAmount:   '<?= number_format(min(max(0, $balanceDue), max(0, (float)($partnerBalance ?? 0))), 2, '.', '') ?>',
        get creditInsufficient() { return parseFloat(this.creditAmount) > this.partnerBalance + 0.0001; },
        get creditOverpay()      { return parseFloat(this.creditAmount) > this.balanceDue + 0.001; },

        invStatus:    '<?= e($inv['status'] ?? 'draft') ?>',
        statusSaving: false,
        statusSaved:  false,
        statusError:  '',
        statusClasses: {
            draft:     'bg-gray-100 text-gray-600',
            sent:      'bg-blue-100 text-blue-700',
            paid:      'bg-emerald-100 text-emerald-700',
            partial:   'bg-cyan-100 text-cyan-700',
            overdue:   'bg-red-100 text-red-700',
            cancelled: 'bg-gray-200 text-gray-500'
        },
        statusLabels: { draft: 'Draft', sent: 'Sent', paid: 'Paid', partial: 'Partial', overdue: 'Overdue', cancelled: 'Cancelled' },
        async changeStatus(val) {
            if (val === this.invStatus || this.statusSaving) return;
            this.statusSaving = true;
            this.statusError  = '';
            const fd = new FormData();
            fd.append('id', '<?= (int)$inv['id'] ?>');
            fd.append('status', val);
            try {
                const r = await fetch('<?= url('invoices/update-status') ?>', { method: 'POST', body: fd });
                const d = await r.json();
                if (d.success) {
                    this.invStatus   = val;
                    this.statusSaved = true;
                    setTimeout(() => { this.statusSaved = false; }, 2500);
                } else {
                    this.statusError = d.message || 'Update failed';
                }
            } catch(e) {
                this.statusError = 'Network error';
            }
            this.statusSaving = false;
        }
    };
}
</script>
<div x-data="invoiceShowData()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2 flex-wrap">
            <span class="font-mono"><?= e($inv['invoice_no']) ?></span>
            <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-full transition-all"
                  :class="statusClasses[invStatus] || 'bg-gray-100 text-gray-600'"
                  x-text="statusLabels[invStatus] || invStatus"></span>
        </h1>
    </div>
    <!-- Action bar: scrollable on mobile -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 sm:flex-wrap">
        <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>" target="_blank"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-file-pdf"></i><span class="hidden sm:inline">PDF</span>
        </a>
        <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>&print=1" target="_blank"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-print"></i><span class="hidden sm:inline"><?= __('print') ?></span>
        </a>
        <button @click="showShare = true"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-share-alt"></i><span class="hidden sm:inline"><?= __('share') ?: 'Share' ?></span>
        </button>
        <?php if (!$resolvedPartnerId || empty($inv['partner_id'])): ?>
        <button onclick="sendToPortal(<?= $inv['id'] ?>)"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 transition">
            <i class="fas fa-share-square"></i><span class="hidden sm:inline">Portal</span>
        </button>
        <?php else: ?>
        <span class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-purple-100 text-purple-700 rounded-xl text-sm font-semibold">
            <i class="fas fa-check-circle"></i><span class="hidden sm:inline">On Portal</span>
        </span>
        <?php endif; ?>
        <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>&download=1"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition">
            <i class="fas fa-download"></i><span class="hidden sm:inline"><?= __('download') ?></span>
        </a>
        <?php if ($canPayByCredit): ?>
        <button @click="showCreditPay = true"
                class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-coins"></i><span class="hidden sm:inline">Pay with Credit</span>
        </button>
        <?php endif; ?>
        <?php
        $invType = $inv['type'] ?? '';
        $editUrl = $invType === 'transfer'
            ? url('transfer-invoice/edit') . '?id=' . $inv['id']
            : ($invType === 'hotel'
                ? url('hotel-invoice/edit') . '?id=' . $inv['id']
                : url('invoices/edit') . '?id=' . $inv['id']);
        ?>
        <a href="<?= $editUrl ?>"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition">
            <i class="fas fa-edit"></i><span class="hidden sm:inline"><?= __('edit') ?></span>
        </a>
        <?php
        $backUrl = $invType === 'hotel'    ? url('hotel-invoice')
                 : ($invType === 'transfer' ? url('transfer-invoice')
                 : url('invoices'));
        ?>
        <a href="<?= $backUrl ?>"
           class="shrink-0 inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left"></i><span class="hidden sm:inline"><?= __('back') ?></span>
        </a>
    </div>
</div>



<script>
function sendToPortal(id) {
    if (confirm('Send this invoice to the partner portal?')) {
        fetch('<?= url('invoices/send-to-portal') ?>?id=' + id)
            .then(r => r.json())
            .then(d => {
                if (d.success) { alert('Invoice sent to portal!'); location.reload(); }
                else alert(d.message || 'Error sending to portal');
            });
    }
}
</script>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-xs text-gray-400 mb-1"><?= __('company_name') ?></p><p class="font-semibold"><?= e($inv['company_name']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('date') ?></p><p class="font-semibold"><?= isset($inv['invoice_date']) ? date('d/m/Y', strtotime($inv['invoice_date'])) : '—' ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('due_date') ?></p><p class="font-semibold"><?= isset($inv['due_date']) ? date('d/m/Y', strtotime($inv['due_date'])) : '—' ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('payment_method') ?></p><p class="font-semibold"><?= e($inv['payment_method'] ?: '—') ?></p></div>
            </div>
            <?php
            $spPhone   = trim($inv['partner_phone']   ?? '');
            $spEmail   = trim($inv['partner_email']   ?? '');
            $spContact = trim($inv['partner_contact'] ?? '');
            $spCity    = trim($inv['partner_city']    ?? '');
            $spCountry = trim($inv['partner_country'] ?? '');
            $spLocation = trim(implode(', ', array_filter([$spCity, $spCountry])));
            if ($spPhone || $spEmail || $spContact || $spLocation):
            ?>
            <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-3">
                <?php if ($spContact): ?>
                <span><i class="fas fa-user mr-1 text-gray-400"></i><?= e($spContact) ?></span>
                <?php endif; ?>
                <?php if ($spPhone): ?>
                <span><i class="fas fa-phone mr-1 text-gray-400"></i><?= e($spPhone) ?></span>
                <?php endif; ?>
                <?php if ($spEmail): ?>
                <span><i class="fas fa-envelope mr-1 text-gray-400"></i><?= e($spEmail) ?></span>
                <?php endif; ?>
                <?php if ($spLocation): ?>
                <span><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i><?= e($spLocation) ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($inv['notes'])): ?><div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl"><p class="text-xs text-gray-400 mb-1"><?= __('notes') ?></p><p class="text-gray-600 dark:text-gray-300"><?= nl2br(e($inv['notes'])) ?></p></div><?php endif; ?>
        </div>

        <!-- Line Items / Route Breakdown -->
        <?php
        $invType = $inv['type'] ?? '';
        if (!empty($invType) && $invType === 'hotel'):
            // Hotel: rich card view from hotels_json
            $hotelsData = json_decode($inv['hotels_json'] ?? '[]', true) ?: [];
            $guestsData = json_decode($inv['guests_json'] ?? '[]', true) ?: [];
            $boardLabels = ['RO'=>'Room Only','BB'=>'Bed & Breakfast','HB'=>'Half Board','FB'=>'Full Board','AI'=>'All Inclusive','UAI'=>'Ultra All Incl.'];
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">
                <i class="fas fa-hotel text-teal-500 mr-2"></i>Hotel Booking Details
                <span class="ml-2 text-xs font-normal text-gray-400"><?= count($hotelsData) ?> hotel<?= count($hotelsData) > 1 ? 's' : '' ?></span>
            </h3>
            <?php if (!empty($hotelsData)): ?>
            <div class="space-y-4">
                <?php foreach ($hotelsData as $hi => $hotel): ?>
                <?php
                    $nights   = max(1, (int)($hotel['nights'] ?? 1));
                    $checkIn  = !empty($hotel['checkIn'])  ? date('d M Y', strtotime($hotel['checkIn']))  : '—';
                    $checkOut = !empty($hotel['checkOut']) ? date('d M Y', strtotime($hotel['checkOut'])) : '—';
                    $stars    = (int)($hotel['stars'] ?? 0);
                    $hotelSubtotal = 0;
                    foreach ($hotel['rooms'] ?? [] as $r) {
                        $rCount      = max(1, (int)($r['count']      ?? 1));
                        $rAdults     = (int)($r['adults']     ?? 1);
                        $rBase       = (float)($r['price']      ?? 0);
                        $rChildren   = max(0, (int)($r['children']   ?? 0));
                        $rChildPrice = (float)($r['childPrice'] ?? 0);
                        if ($rAdults === 0) {
                            $hotelSubtotal += $rBase * $rCount * $nights;
                        } else {
                            $hotelSubtotal += ($rBase * $rCount + $rChildPrice * $rChildren * $rCount) * $nights;
                        }
                    }
                ?>
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <!-- Hotel header -->
                    <div class="px-5 py-3 bg-teal-50 dark:bg-teal-900/20 border-b border-teal-100 dark:border-teal-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center"><?= $hi + 1 ?></span>
                            <span class="font-semibold text-sm text-teal-800 dark:text-teal-300"><?= e($hotel['name'] ?? 'Hotel') ?></span>
                            <?php if ($hotel['city'] ?? ''): ?><span class="text-xs text-teal-500">· <?= e($hotel['city']) ?></span><?php endif; ?>
                            <?php if ($stars > 0): ?><span class="text-amber-400 text-[11px]"><?= str_repeat('★', $stars) ?></span><?php endif; ?>
                        </div>
                        <span class="text-xs bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 px-2 py-0.5 rounded-full font-medium"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="p-5 space-y-3">
                        <!-- Dates -->
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Check-in</p>
                                <p class="font-semibold text-gray-700 dark:text-gray-200"><?= $checkIn ?></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Nights</p>
                                <p class="font-bold text-teal-600 dark:text-teal-400 text-lg"><?= $nights ?></p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Check-out</p>
                                <p class="font-semibold text-gray-700 dark:text-gray-200"><?= $checkOut ?></p>
                            </div>
                        </div>
                        <!-- Rooms -->
                        <?php if (!empty($hotel['rooms'])): ?>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-2">Rooms</p>
                            <div class="space-y-1.5">
                                 <?php foreach ($hotel['rooms'] as $ri => $room):
                                     $board      = $boardLabels[strtoupper($room['board'] ?? '')] ?? ($room['board'] ?? '');
                                     $count      = max(1, (int)($room['count']      ?? 1));
                                     $adults     = (int)($room['adults']     ?? 1);
                                     $children   = max(0, (int)($room['children']   ?? 0));
                                     $infants    = max(0, (int)($room['infants']    ?? 0));
                                     $price      = (float)($room['price']      ?? 0);
                                     $childPrice = (float)($room['childPrice'] ?? 0);
                                     // Branch: child-only vs adult room
                                     if ($adults === 0) {
                                         $lineTotal = $price * $count * $nights;
                                     } else {
                                         $lineTotal = ($price * $count + $childPrice * $children * $count) * $nights;
                                     }
                                 ?>
                                 <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg space-y-2">
                                     <!-- Room header row -->
                                     <div class="flex items-center justify-between">
                                         <div class="flex items-center gap-2 flex-wrap">
                                             <span class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-[9px] font-bold flex items-center justify-center"><?= $ri + 1 ?></span>
                                             <span class="text-sm font-semibold text-gray-700 dark:text-gray-200"><?= e($room['roomType'] ?? 'Standard') ?></span>
                                             <?php if ($board): ?><span class="text-[10px] bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded-md font-medium"><?= e($board) ?></span><?php endif; ?>
                                             <?php if ($adults === 0): ?><span class="text-[10px] bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded-md font-medium">CHD Room</span><?php endif; ?>
                                         </div>
                                         <div class="text-right text-xs text-gray-400">
                                             <?php if ($count > 1): ?><?= $count ?> rooms ×<?php endif; ?> <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>
                                         </div>
                                     </div>
                                     <!-- Pax + total row -->
                                     <div class="flex items-center justify-between flex-wrap gap-2">
                                         <div class="flex items-center flex-wrap gap-1">
                                             <?php if ($adults > 0): ?>
                                             <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold">
                                                 <i class="fas fa-user text-[8px]"></i> <?= $adults ?> ADL
                                             </span>
                                             <?php endif; ?>
                                             <?php if ($children > 0): ?>
                                             <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-semibold">
                                                 <i class="fas fa-child text-[8px]"></i> <?= $children ?> CHD
                                                 <?php if ($adults > 0 && $childPrice > 0): ?><span class="opacity-70">(+<?= e($inv['currency']) ?> <?= number_format($childPrice, 2) ?>/night)</span><?php endif; ?>
                                             </span>
                                             <?php endif; ?>
                                             <?php if ($infants > 0): ?>
                                             <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-semibold">
                                                 <i class="fas fa-baby text-[8px]"></i> <?= $infants ?> INF
                                             </span>
                                             <?php endif; ?>
                                         </div>
                                         <span class="font-bold text-gray-700 dark:text-gray-200"><?= e($inv['currency'] ?? 'USD') ?> <?= number_format($lineTotal, 2) ?></span>
                                     </div>
                                 </div>
                                 <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Hotel subtotal -->
                        <div class="flex justify-end items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-400 uppercase tracking-wider">Hotel subtotal</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100"><?= e($inv['currency'] ?? 'USD') ?> <?= number_format($hotelSubtotal, 2) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php elseif (!empty($invoiceItems)): ?>
            <!-- Fallback: items table if no hotels_json -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 dark:bg-gray-700/50 border-b">
                        <th class="text-left px-4 py-2 font-semibold text-gray-600">Description</th>
                        <th class="text-center px-4 py-2 font-semibold text-gray-600">Qty</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-600">Unit</th>
                        <th class="text-right px-4 py-2 font-semibold text-gray-600">Total</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php foreach ($invoiceItems as $item): ?>
                    <tr>
                        <td class="px-4 py-3"><?= e($item['description'] ?? '') ?></td>
                        <td class="px-4 py-3 text-center"><?= (int)($item['quantity'] ?? 1) ?></td>
                        <td class="px-4 py-3 text-right"><?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                        <td class="px-4 py-3 text-right font-bold"><?= number_format((float)($item['total_price'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($guestsData)): ?>
            <!-- Guests -->
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-2">Guests</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($guestsData as $gi => $guest): ?>
                    <?php if (empty($guest['name'])) continue; ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium <?= $gi === 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-700' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' ?>">
                        <?php if ($gi === 0): ?><i class="fas fa-star text-[9px]"></i><?php else: ?><i class="fas fa-user text-[9px]"></i><?php endif; ?>
                        <?= e(($guest['title'] ?? '') . ' ' . ($guest['name'] ?? '')) ?>
                        <?php if (!empty($guest['passport'])): ?><span class="opacity-50">#<?= e($guest['passport']) ?></span><?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif (!empty($invoiceItems)): ?>
        <?php if ($invType === 'transfer'): ?>
        <!-- Transfer: styled route breakdown with type badges -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">
                <i class="fas fa-route text-teal-500 mr-2"></i>Transfer Route &amp; Pricing
                <span class="ml-2 text-xs font-normal text-gray-400"><?= count($invoiceItems) ?> leg<?= count($invoiceItems) > 1 ? 's' : '' ?></span>
            </h3>
            <div class="space-y-3">
            <?php foreach ($invoiceItems as $idx => $item):
                $desc   = $item['description'] ?? '';
                $parts  = array_map('trim', explode(' · ', $desc));
                $routePart = $parts[0] ?? $desc;
                $datePart  = '';
                $extraParts = [];
                foreach (array_slice($parts, 1) as $p) {
                    if (preg_match('/^\d{1,2} [A-Za-z]+ \d{4}/', $p)) { $datePart = $p; }
                    else { $extraParts[] = $p; }
                }
                $routeSides = explode(' → ', $routePart, 2);
                $fromLoc = trim($routeSides[0] ?? $routePart);
                $toLoc   = trim($routeSides[1] ?? '');
                $typeBadge = '';
                if (preg_match('/\((.+?)\)$/', $toLoc, $m)) {
                    $typeBadge = $m[1]; $toLoc = trim(str_replace('('.$m[1].')', '', $toLoc));
                } elseif (preg_match('/\((.+?)\)$/', $routePart, $m)) {
                    $typeBadge = $m[1];
                } elseif ($datePart && preg_match('/\((.+?)\)\s*$/', $datePart, $m)) {
                    $typeBadge = $m[1];
                    $datePart  = trim(preg_replace('/\s*\(' . preg_quote($m[1], '/') . '\)\s*$/', '', $datePart));
                }
                $isRT = strtolower($typeBadge) === 'round trip';
            ?>
            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-200 dark:border-gray-600">
                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center"><?= $idx + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm"><?= e($fromLoc) ?></span>
                        <i class="fas fa-long-arrow-alt-right text-teal-500 text-xs"></i>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm"><?= e($toLoc) ?></span>
                        <?php if ($typeBadge): ?>
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full <?= $isRT ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' ?>">
                            <i class="fas <?= $isRT ? 'fa-exchange-alt' : 'fa-long-arrow-alt-right' ?> mr-1 text-[9px]"></i><?= e($typeBadge) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($datePart || $extraParts): ?>
                    <p class="text-xs text-gray-400 mt-1">
                        <?php if ($datePart): ?><i class="fas fa-calendar mr-1"></i><?= e($datePart) ?><?php endif; ?>
                        <?php if ($extraParts): ?><span class="ml-2"><?= e(implode(' · ', $extraParts)) ?></span><?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="flex-shrink-0 text-right">
                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200"><?= e($inv['currency'] ?? 'USD') ?> <?= number_format((float)($item['total_price'] ?? 0), 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Generic line items table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-list text-blue-500 mr-2"></i><?= __('line_items') ?: 'Line Items' ?></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left px-4 py-2 font-semibold text-gray-600 dark:text-gray-300"><?= __('description') ?></th>
                            <th class="text-center px-4 py-2 font-semibold text-gray-600 dark:text-gray-300"><?= __('quantity') ?: 'Qty' ?></th>
                            <th class="text-right px-4 py-2 font-semibold text-gray-600 dark:text-gray-300"><?= __('unit_price') ?: 'Unit Price' ?></th>
                            <th class="text-right px-4 py-2 font-semibold text-gray-600 dark:text-gray-300"><?= __('line_total') ?: 'Total' ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach ($invoiceItems as $item): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                <?= e($item['description'] ?? '') ?>
                                <?php if (!empty($item['service_id'])): ?>
                                <span class="text-[10px] text-emerald-500 ml-1"><i class="fas fa-link"></i> <?= e($item['item_type'] ?? '') ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300"><?= (int)($item['quantity'] ?? 1) ?></td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300"><?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-200"><?= number_format((float)($item['total_price'] ?? 0), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($invType === 'hotel'): ?>
        <!-- ── Assign Mission (Hotel Invoice) ─────────────────────── -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                <i class="fas fa-tasks text-indigo-500 mr-1"></i><?= __('missions') ?: 'Missions' ?>
            </h3>
            <?php
            // Pre-fill from first guest in guests_json
            $guestsForMission = json_decode($inv['guests_json'] ?? '[]', true) ?: [];
            $firstGuest       = $guestsForMission[0] ?? [];
            $missionGuestName = trim(($firstGuest['title'] ?? '') . ' ' . ($firstGuest['name'] ?? ''))
                             ?: ($inv['company_name'] ?? '');
            $missionPassport  = $firstGuest['passport'] ?? '';
            // Pre-fill date from first hotel check-in
            $hotelsForMission = json_decode($inv['hotels_json'] ?? '[]', true) ?: [];
            $missionDate      = $hotelsForMission[0]['checkIn'] ?? '';
            ?>
            <form method="POST" action="<?= url('missions/quick-create') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="mission_type" value="hotel">
                <input type="hidden" name="reference_id" value="<?= $inv['id'] ?>">
                <input type="hidden" name="guest_name" value="<?= e($missionGuestName) ?>">
                <input type="hidden" name="guest_passport" value="<?= e($missionPassport) ?>">
                <input type="hidden" name="mission_date" value="<?= e($missionDate) ?>">
                <input type="hidden" name="pickup_location" value="<?= e($hotelsForMission[0]['name'] ?? '') ?>">
                <input type="hidden" name="dropoff_location" value="">
                <input type="hidden" name="pax_count" value="<?= count($guestsForMission) ?: 1 ?>">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle"></i> <?= __('assign_mission') ?: 'Assign Mission' ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4"><?= __('total_amount') ?: 'Financial Summary' ?></h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('subtotal') ?></span><span class="font-medium"><?= number_format($inv['subtotal'] ?? $inv['total_amount'] ?? 0, 2) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('tax') ?> (<?= $inv['tax_rate'] ?? 0 ?>%)</span><span class="font-medium"><?= number_format($inv['tax_amount'] ?? 0, 2) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('discount') ?></span><span class="font-medium text-red-500">-<?= number_format($inv['discount'] ?? 0, 2) ?></span></div>
            <div class="border-t pt-3 flex justify-between"><span class="font-semibold"><?= __('total_amount') ?></span><span class="text-xl font-bold text-emerald-600"><?= number_format($inv['total_amount'] ?? 0, 2) ?> <?= $inv['currency'] ?? 'USD' ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('paid') ?></span><span class="font-medium text-blue-600"><?= number_format($inv['paid_amount'] ?? 0, 2) ?></span></div>
        </div>
    </div>

    <!-- ── Inline Status Updater ─────────────────────────── -->
    <?php
    $statusConfig = [
        'draft'     => ['label' => 'Draft',     'class' => 'bg-gray-100 text-gray-600',     'icon' => 'fa-pencil-alt'],
        'sent'      => ['label' => 'Sent',      'class' => 'bg-blue-100 text-blue-700',     'icon' => 'fa-paper-plane'],
        'paid'      => ['label' => 'Paid',      'class' => 'bg-emerald-100 text-emerald-700','icon' => 'fa-check-circle'],
        'partial'   => ['label' => 'Partial',   'class' => 'bg-cyan-100 text-cyan-700',     'icon' => 'fa-adjust'],
        'overdue'   => ['label' => 'Overdue',   'class' => 'bg-red-100 text-red-700',       'icon' => 'fa-exclamation-circle'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-gray-200 text-gray-500',     'icon' => 'fa-ban'],
    ];
    $currentStatus = $inv['status'] ?? 'draft';
    ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Invoice Status</h3>
            <span x-show="statusSaving" class="text-xs text-blue-500 flex items-center gap-1">
                <i class="fas fa-spinner fa-spin text-[10px]"></i> Saving…
            </span>
            <span x-show="statusSaved && !statusSaving" x-cloak class="text-xs text-emerald-600 flex items-center gap-1">
                <i class="fas fa-check text-[10px]"></i> Saved
            </span>
        </div>

        <!-- Current live badge -->
        <div class="mb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold transition-all"
                  :class="statusClasses[invStatus] || 'bg-gray-100 text-gray-600'">
                <i class="fas text-xs" :class="{
                    'fa-pencil-alt':        invStatus==='draft',
                    'fa-paper-plane':       invStatus==='sent',
                    'fa-check-circle':      invStatus==='paid',
                    'fa-adjust':            invStatus==='partial',
                    'fa-exclamation-circle':invStatus==='overdue',
                    'fa-ban':               invStatus==='cancelled'
                }"></i>
                <span x-text="statusLabels[invStatus] || invStatus"></span>
            </span>
        </div>

        <!-- Change to buttons -->
        <p class="text-[10px] text-gray-400 uppercase font-semibold tracking-wider mb-2">Change to</p>
        <div class="grid grid-cols-2 gap-1.5">
            <?php foreach ($statusConfig as $sv => $sc2): ?>
            <button type="button"
                    @click="changeStatus('<?= $sv ?>')"
                    :disabled="invStatus === '<?= $sv ?>' || statusSaving"
                    class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all
                           <?= $sc2['class'] ?>
                           disabled:opacity-40 disabled:cursor-default hover:opacity-90 hover:shadow-sm">
                <i class="fas <?= $sc2['icon'] ?> text-[10px]"></i>
                <?= $sc2['label'] ?>
                <i x-show="invStatus === '<?= $sv ?>'" x-cloak class="fas fa-check ml-auto text-[9px] opacity-70"></i>
            </button>
            <?php endforeach; ?>
        </div>

        <p x-show="statusError" x-cloak class="mt-2 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i> <span x-text="statusError"></span>
        </p>
    </div>

    <!-- ── Danger Zone ─────────────────────────── -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-200 dark:border-red-700/30 p-5">
        <h3 class="text-xs font-bold text-red-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
            <i class="fas fa-exclamation-triangle"></i> Danger Zone
        </h3>
        <?php
        $deleteUrl = $invType === 'hotel'    ? url('hotel-invoice/delete') . '?id=' . $inv['id']
                   : ($invType === 'transfer' ? url('invoices/delete')      . '?id=' . $inv['id']
                   : url('invoices/delete')  . '?id=' . $inv['id']);
        ?>
        <a href="<?= $deleteUrl ?>"
           onclick="return confirm('<?= __('confirm_delete') ?: 'Are you sure you want to delete this invoice? This cannot be undone.' ?>')"
           class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 dark:hover:bg-red-900/40 transition">
            <i class="fas fa-trash"></i> <?= __('delete') ?: 'Delete Invoice' ?>
        </a>
    </div>
</div>

<!-- Pay with Credit Modal -->
<?php if ($canPayByCredit): ?>
<div x-show="showCreditPay" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showCreditPay = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6" @click.outside="showCreditPay = false">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-coins text-amber-500"></i> Pay with Credit
            </h2>
            <button @click="showCreditPay = false" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Balance info -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-amber-700 dark:text-amber-400 font-medium">
                    <i class="fas fa-wallet mr-1"></i> Available Credit
                </span>
                <span class="font-bold text-amber-800 dark:text-amber-300 text-base"
                      x-text="partnerBalance.toFixed(2) + ' <?= e($inv['currency'] ?? 'EUR') ?>'"></span>
            </div>
            <div class="flex items-center justify-between text-sm mt-2">
                <span class="text-amber-700 dark:text-amber-400 font-medium">
                    <i class="fas fa-file-invoice mr-1"></i> Balance Due
                </span>
                <span class="font-bold text-amber-800 dark:text-amber-300"
                      x-text="balanceDue.toFixed(2) + ' <?= e($inv['currency'] ?? 'EUR') ?>'"></span>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="<?= url('partners/credits/pay-invoice') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="partner_id" value="<?= $resolvedPartnerId ?>">
            <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">
            <input type="hidden" name="currency"   value="<?= e($inv['currency'] ?? 'EUR') ?>">

            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                    Amount to Apply <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="amount"
                           x-model="creditAmount"
                           min="0.01" :max="balanceDue" step="0.01" required
                           class="w-full pl-4 pr-16 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-lg font-bold">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">
                        <?= e($inv['currency'] ?? 'EUR') ?>
                    </span>
                </div>
                <!-- Warnings -->
                <p x-show="creditInsufficient" x-cloak
                   class="mt-2 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i>
                    Amount exceeds available credit balance.
                </p>
                <p x-show="creditOverpay && !creditInsufficient" x-cloak
                   class="mt-2 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
                    <i class="fas fa-exclamation-triangle"></i>
                    Amount exceeds the invoice balance due.
                </p>
                <!-- Quick fill buttons -->
                <div class="flex gap-2 mt-2">
                    <button type="button"
                            @click="creditAmount = Math.min(parseFloat(balanceDue), parseFloat(partnerBalance)).toFixed(2)"
                            class="text-xs px-3 py-1 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition font-medium">
                        Full amount
                    </button>
                    <button type="button"
                            @click="creditAmount = (parseFloat(balanceDue) / 2).toFixed(2)"
                            class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 transition font-medium">
                        50%
                    </button>
                </div>
            </div>

            <button type="submit"
                    :disabled="creditInsufficient || creditOverpay || parseFloat(creditAmount) <= 0"
                    class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i>
                Confirm Payment
            </button>
        </form>

        <?php if ($resolvedPartnerId > 0): ?>
        <p class="text-center text-xs text-gray-400 mt-3">
            <a href="<?= url('partners/show') ?>?id=<?= $resolvedPartnerId ?>#credits" class="hover:text-amber-500 transition">
                <i class="fas fa-external-link-alt mr-1"></i> View full credit history
            </a>
        </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Share Modal -->
<div x-show="showShare" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showShare = false">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6" @click.outside="showShare = false">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2"><i class="fas fa-share-alt text-blue-500"></i> <?= __('share') ?: 'Share Document' ?></h2>
            <button @click="showShare = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex gap-2 mb-5">
            <button @click="shareTab = 'email'; error = ''; sent = false" :class="shareTab === 'email' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'" class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2"><i class="fas fa-envelope"></i> <?= __('email') ?: 'Email' ?></button>
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
                body: new URLSearchParams({ type: 'invoice', id: '<?= $inv['id'] ?>', email: $refs.emailTo.value, subject: $refs.emailSubject.value, message: $refs.emailMessage.value })
            }).then(r => r.json()).then(d => { sending = false; if(d.success) { sent = true; } else { error = d.message || 'Failed to send.'; } }).catch(() => { sending = false; error = 'Network error.'; });
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recipient Email</label><input x-ref="emailTo" type="email" required placeholder="recipient@example.com" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Subject</label><input x-ref="emailSubject" type="text" value="<?= htmlspecialchars($inv['invoice_no']) ?> — <?= htmlspecialchars(COMPANY_NAME) ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message (optional)</label><textarea x-ref="emailMessage" rows="3" placeholder="Additional message..." class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea></div>
            <button type="submit" :disabled="sending" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2"><i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i><span x-text="sending ? 'Sending...' : 'Send Email with PDF'"></span></button>
        </form>
        <!-- WhatsApp Form -->
        <form x-show="shareTab === 'whatsapp'" @submit.prevent="
            const phone = $refs.waPhone.value.replace(/[^0-9]/g, '');
            window.open('<?= url('export/whatsapp') ?>?type=invoice&id=<?= $inv['id'] ?>&phone=' + phone, '_blank');
            sent = true;
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number (with country code)</label><input x-ref="waPhone" type="tel" placeholder="+905551234567" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></div>
            <p class="text-xs text-gray-400">Leave empty to open WhatsApp without a specific recipient.</p>
            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2"><i class="fab fa-whatsapp text-lg"></i> Share via WhatsApp</button>
        </form>
    </div>
</div>
</div>
