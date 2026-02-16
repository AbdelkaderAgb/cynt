<?php
/**
 * Voucher Detail View — with PDF/Email/WhatsApp export
 */
$v = $voucher;
$statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-red-100 text-red-700'];
$sc = $statusColors[$v['status']] ?? 'bg-gray-100 text-gray-600';
$statuses = ['pending' => __('pending'), 'confirmed' => __('confirmed'), 'completed' => __('completed'), 'cancelled' => __('cancelled')];
?>
<div x-data="{ showShare: false, shareTab: 'email', sending: false, sent: false, error: '' }">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
            <?= e($v['voucher_no']) ?>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?= $sc ?>"><?= $statuses[$v['status']] ?? $v['status'] ?></span>
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= __('created_at') ?>: <?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>" target="_blank" class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-file-pdf"></i>PDF</a>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-print"></i><?= __('print') ?></button>
        <button @click="showShare = true" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-share-alt"></i>Share</button>
        <a href="<?= url('vouchers/pdf') ?>?id=<?= $v['id'] ?>&download=1" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5"><i class="fas fa-download"></i><?= __('download') ?></a>
        <a href="<?= url('vouchers/edit') ?>?id=<?= $v['id'] ?>" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition flex items-center gap-1.5"><i class="fas fa-edit"></i><?= __('edit') ?></a>
        <a href="<?= url('vouchers') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-arrow-left"></i><?= __('back') ?></a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2"><i class="fas fa-route text-blue-500"></i><?= __('transfer_details', [], 'Transfer Details') ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-xs text-gray-400 mb-1"><?= __('company_name') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['company_name']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('hotel_name') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['hotel_name'] ?: '—') ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('pickup_location') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['pickup_location']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('dropoff_location') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= e($v['dropoff_location']) ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('date') ?> & <?= __('time') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= date('d/m/Y', strtotime($v['pickup_date'])) ?> — <?= $v['pickup_time'] ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('return_date') ?></p><p class="font-semibold text-gray-800 dark:text-gray-200"><?= $v['return_date'] ? date('d/m/Y', strtotime($v['return_date'])) . ' — ' . $v['return_time'] : '—' ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('transfer_type') ?></p><p class="font-semibold"><?= e($v['transfer_type'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 mb-1"><?= __('flight_number') ?></p><p class="font-semibold"><?= e($v['flight_number'] ?: '—') ?></p></div>
            </div>
        </div>

        <?php if (!empty($v['passengers']) || !empty($v['notes'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <?php if (!empty($v['passengers'])): ?>
            <h3 class="text-lg font-semibold text-gray-700 mb-3"><i class="fas fa-users text-purple-500 mr-2"></i><?= __('passengers') ?></h3>
            <p class="text-gray-600 whitespace-pre-line mb-4"><?= e($v['passengers']) ?></p>
            <?php endif; ?>
            <?php if (!empty($v['notes'])): ?>
            <h3 class="text-lg font-semibold text-gray-700 mb-3"><i class="fas fa-sticky-note text-amber-500 mr-2"></i><?= __('notes') ?></h3>
            <p class="text-gray-600 whitespace-pre-line"><?= e($v['notes']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-info-circle text-blue-500 mr-2"></i><?= __('details') ?></h3>
            <div class="space-y-3">
                <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('total_pax') ?></span><span class="font-bold text-lg text-blue-600"><?= $v['total_pax'] ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('transfer_price') ?></span><span class="font-bold text-lg text-emerald-600"><?= number_format($v['price'] ?? 0, 2) ?> <?= $v['currency'] ?? 'USD' ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('payment_status') ?></span><span class="text-sm font-medium"><?= $v['payment_status'] ?? 'unpaid' ?></span></div>
            </div>
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
