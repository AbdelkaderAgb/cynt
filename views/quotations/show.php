<?php
/**
 * Quotation Detail View
 */
$statusLabels = [
    'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
    'rejected' => 'Rejected', 'expired' => 'Expired', 'converted' => 'Converted',
];
$statusColors = [
    'draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700',
    'accepted' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700',
    'expired' => 'bg-amber-100 text-amber-700', 'converted' => 'bg-purple-100 text-purple-700',
];
$typeIcons = ['hotel' => 'fa-hotel text-teal-500', 'tour' => 'fa-route text-blue-500', 'transfer' => 'fa-shuttle-van text-green-500', 'other' => 'fa-concierge-bell text-gray-500'];

// Group items by day
$days = [];
foreach ($items as $item) {
    $d = (int)($item['day_number'] ?? 1);
    $days[$d][] = $item;
}
ksort($days);
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Quotation created successfully
</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> Quotation updated
</div>
<?php endif; ?>
<?php if (isset($_GET['converted'])): ?>
<div class="mb-4 p-4 bg-purple-50 border border-purple-200 text-purple-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-exchange-alt"></i> Quotation converted to bookings
</div>
<?php endif; ?>

<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <i class="fas fa-file-alt mr-2 text-orange-500"></i><?= e($q['quote_number']) ?>
            </h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$q['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $statusLabels[$q['status']] ?? ucfirst($q['status']) ?>
                </span>
                <?php if ($q['valid_until']): ?>
                <span class="text-xs text-gray-400">Valid until <?= date('d M Y', strtotime($q['valid_until'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= url('quotations/pdf') ?>?id=<?= (int)$q['id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <?php if (in_array($q['status'], ['draft', 'sent', 'accepted'])): ?>
            <form method="POST" action="<?= url('quotations/convert') ?>" class="inline" onsubmit="return confirm('Convert this quotation to bookings?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-xl text-sm font-semibold hover:bg-purple-100 transition">
                    <i class="fas fa-exchange-alt"></i> Convert
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= url('quotations/edit') ?>?id=<?= (int)$q['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?= url('quotations') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Client -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Client</h3>
            <p class="font-semibold text-gray-800 dark:text-white text-lg"><?= e($q['client_name']) ?></p>
            <?php if ($q['client_email']): ?><p class="text-sm text-gray-500 mt-1"><i class="fas fa-envelope mr-1"></i><?= e($q['client_email']) ?></p><?php endif; ?>
            <?php if ($q['client_phone']): ?><p class="text-sm text-gray-500"><i class="fas fa-phone mr-1"></i><?= e($q['client_phone']) ?></p><?php endif; ?>
            <?php if ($q['partner_name']): ?><p class="text-sm text-gray-400 mt-2"><i class="fas fa-handshake mr-1"></i><?= e($q['partner_name']) ?></p><?php endif; ?>
        </div>
        <!-- Travel -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Travel</h3>
            <p class="font-semibold text-gray-800 dark:text-white">
                <?= $q['travel_dates_from'] ? date('d M Y', strtotime($q['travel_dates_from'])) : '—' ?>
                → <?= $q['travel_dates_to'] ? date('d M Y', strtotime($q['travel_dates_to'])) : '—' ?>
            </p>
            <div class="flex gap-4 mt-2 text-sm text-gray-500">
                <span><i class="fas fa-user mr-1"></i><?= (int)$q['adults'] ?> adults</span>
                <?php if ((int)$q['children']): ?><span><i class="fas fa-child mr-1"></i><?= (int)$q['children'] ?> children</span><?php endif; ?>
                <?php if ((int)$q['infants']): ?><span><i class="fas fa-baby mr-1"></i><?= (int)$q['infants'] ?> infants</span><?php endif; ?>
            </div>
        </div>
        <!-- Financials -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Financials</h3>
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium"><?= format_currency($q['subtotal'], $q['currency']) ?></dd></div>
                <?php if ((float)$q['discount_amount'] > 0): ?>
                <div class="flex justify-between text-red-500"><dt>Discount (<?= $q['discount_percent'] ?>%)</dt><dd>-<?= format_currency($q['discount_amount'], $q['currency']) ?></dd></div>
                <?php endif; ?>
                <?php if ((float)$q['tax_amount'] > 0): ?>
                <div class="flex justify-between text-gray-500"><dt>Tax (<?= $q['tax_percent'] ?>%)</dt><dd>+<?= format_currency($q['tax_amount'], $q['currency']) ?></dd></div>
                <?php endif; ?>
                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700 font-bold text-lg text-gray-800 dark:text-white">
                    <dt>Total</dt><dd><?= format_currency($q['total'], $q['currency']) ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Day-by-Day Itinerary -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-list-ol mr-2 text-green-400"></i>Itinerary</h3>
        <?php if (empty($days)): ?>
        <p class="text-center text-gray-400 py-8">No items in this quotation.</p>
        <?php else: ?>
        <?php foreach ($days as $dayNum => $dayItems): ?>
        <div class="mb-4">
            <h4 class="text-sm font-bold text-orange-600 uppercase mb-2"><i class="fas fa-calendar-day mr-1"></i>Day <?= $dayNum ?></h4>
            <div class="space-y-2">
                <?php foreach ($dayItems as $item): ?>
                <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas <?= $typeIcons[$item['item_type']] ?? 'fa-circle text-gray-400' ?>"></i>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-gray-200 text-sm"><?= e($item['item_name']) ?></span>
                            <?php if ($item['description']): ?>
                            <p class="text-xs text-gray-400"><?= e($item['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-right text-sm">
                        <span class="text-gray-400"><?= (int)$item['quantity'] ?> × <?= format_currency($item['unit_price'], $item['currency'] ?? $q['currency']) ?></span>
                        <span class="ml-3 font-semibold text-gray-800 dark:text-gray-200"><?= format_currency($item['total_price'], $item['currency'] ?? $q['currency']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($q['notes'] || $q['payment_terms'] || $q['cancellation_policy']): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
        <?php if ($q['payment_terms']): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Payment Terms</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap"><?= e($q['payment_terms']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($q['notes']): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap"><?= e($q['notes']) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
