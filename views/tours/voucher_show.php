<?php
$statusLabels = ['pending'=>'Pending','confirmed'=>'Confirmed','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'];
$statusColors = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-cyan-100 text-cyan-700','completed'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-700'];
$customers = json_decode($t['customers'] ?? '[]', true) ?: [];
$tourItems = json_decode($t['tour_items'] ?? '[]', true) ?: [];
?>

<?php if (isset($_GET['updated'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i><?= __('updated_successfully') ?></div>
<?php endif; ?>

<div x-data="{ showShare: false, shareTab: 'email', sending: false, sent: false, error: '' }">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-map-marked-alt text-purple-500 mr-2"></i><?= e($t['tour_name']) ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= e($t['tour_code'] ?? '') ?> · <?= e($t['company_name'] ?: '—') ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= url('tour-voucher/pdf') ?>?id=<?= $t['id'] ?>" target="_blank" class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-file-pdf"></i>PDF</a>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-print"></i><?= __('print') ?></button>
        <button @click="showShare = true" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-share-alt"></i>Share</button>
        <a href="<?= url('tour-voucher/pdf') ?>?id=<?= $t['id'] ?>&download=1" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5"><i class="fas fa-download"></i><?= __('download') ?></a>
        <a href="<?= url('tour-voucher/edit') ?>?id=<?= $t['id'] ?>" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition flex items-center gap-1.5"><i class="fas fa-edit"></i><?= __('edit') ?></a>
        <a href="<?= url('tour-voucher') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-arrow-left"></i><?= __('back') ?></a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Company & Tour Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-building text-blue-500 mr-1"></i><?= __('company_name') ?> & <?= __('tour_name') ?></h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div><p class="text-xs text-gray-400"><?= __('company_name') ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['company_name'] ?: '—') ?></p></div>
                <div><p class="text-xs text-gray-400"><?= __('hotel_name') ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['hotel_name'] ?: '—') ?></p></div>
                <div><p class="text-xs text-gray-400"><?= __('phone') ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['customer_phone'] ?: '—') ?></p></div>
                <div><p class="text-xs text-gray-400"><?= __('tour_date') ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= $t['tour_date'] ? date('d/m/Y', strtotime($t['tour_date'])) : '—' ?></p></div>
                <div><p class="text-xs text-gray-400"><?= __('description') ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['description'] ?: '—') ?></p></div>
                <?php if (!empty($t['guest_name'])): ?>
                <div><p class="text-xs text-gray-400"><?= __('guest_name') ?: 'Guest Name' ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['guest_name']) ?></p></div>
                <?php endif; ?>
                <?php if (!empty($t['passenger_passport'])): ?>
                <div><p class="text-xs text-gray-400"><i class="fas fa-passport text-amber-500 mr-1"></i><?= __('passenger_passport') ?: 'Passport' ?></p><p class="font-medium text-gray-800 dark:text-gray-200"><?= e($t['passenger_passport']) ?></p></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tour Items -->
        <?php if (!empty($tourItems)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-route text-purple-500 mr-1"></i><?= __('tours') ?></h3>
            <div class="space-y-3">
                <?php foreach ($tourItems as $i => $ti): ?>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold"><?= $i + 1 ?></span>
                    <div class="flex-1">
                        <span class="font-medium text-gray-800 dark:text-gray-200"><?= e($ti['name'] ?? '') ?></span>
                        <?php if (!empty($ti['date'])): ?><span class="text-xs text-gray-400 ml-2"><?= e($ti['date']) ?></span><?php endif; ?>
                        <?php if (!empty($ti['duration'])): ?><span class="text-xs text-gray-400 ml-2">· <?= e($ti['duration']) ?></span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Customers -->
        <?php if (!empty($customers)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-users text-teal-500 mr-1"></i><?= __('customers') ?></h3>
            <div class="space-y-2">
                <?php foreach ($customers as $i => $c): ?>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <span class="w-7 h-7 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold"><?= $i + 1 ?></span>
                    <span class="font-medium text-gray-800 dark:text-gray-200"><?= e(($c['title'] ?? '') . ' ' . ($c['name'] ?? '')) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4"><i class="fas fa-info-circle text-blue-500 mr-1"></i><?= __('status') ?> & PAX</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500"><?= __('status') ?></span>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?= $statusColors[$t['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusLabels[$t['status']] ?? $t['status'] ?></span>
                </div>
                <div class="flex justify-between"><span class="text-sm text-gray-500"><?= __('adult') ?></span><span class="font-medium"><?= $t['adults'] ?></span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500"><?= __('child') ?></span><span class="font-medium"><?= $t['children'] ?></span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500"><?= __('infant') ?></span><span class="font-medium"><?= $t['infants'] ?></span></div>
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3 flex justify-between">
                    <span class="text-sm text-gray-500"><?= __('total_pax') ?></span>
                    <span class="font-bold text-lg"><?= $t['total_pax'] ?></span>
                </div>
                <!-- Pricing hidden — managed via invoices/receipts -->
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-200 dark:border-red-700/30 p-6">
            <h3 class="text-sm font-semibold text-red-500 uppercase tracking-wider mb-3"><i class="fas fa-exclamation-triangle mr-1"></i>Danger Zone</h3>
            <a href="<?= url('tour-voucher/delete') ?>?id=<?= $t['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')"
               class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-semibold hover:bg-red-100 transition">
                <i class="fas fa-trash"></i><?= __('delete') ?>
            </a>
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
        <form x-show="shareTab === 'email'" @submit.prevent="
            sending = true; error = ''; sent = false;
            fetch('<?= url('export/email') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ type: 'tour_voucher', id: '<?= $t['id'] ?>', email: $refs.emailTo.value, subject: $refs.emailSubject.value, message: $refs.emailMessage.value })
            }).then(r => r.json()).then(d => { sending = false; if(d.success) { sent = true; } else { error = d.message || 'Failed to send.'; } }).catch(() => { sending = false; error = 'Network error.'; });
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recipient Email</label><input x-ref="emailTo" type="email" required placeholder="recipient@example.com" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Subject</label><input x-ref="emailSubject" type="text" value="<?= htmlspecialchars($t['tour_code'] ?? $t['tour_name'] ?? '') ?> — <?= htmlspecialchars(COMPANY_NAME) ?>" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></div>
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message (optional)</label><textarea x-ref="emailMessage" rows="3" placeholder="Additional message..." class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea></div>
            <button type="submit" :disabled="sending" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2"><i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i><span x-text="sending ? 'Sending...' : 'Send Email with PDF'"></span></button>
        </form>
        <form x-show="shareTab === 'whatsapp'" @submit.prevent="
            const phone = $refs.waPhone.value.replace(/[^0-9]/g, '');
            window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent('🗺 Tour Voucher: <?= addslashes($t['tour_name']) ?>\n🏢 <?= addslashes($t['company_name'] ?? '') ?>\n📍 <?= addslashes($t['destination'] ?? '') ?>\n📅 <?= $t['tour_date'] ? date('d/m/Y', strtotime($t['tour_date'])) : '' ?>\n👥 <?= $t['total_pax'] ?> Pax\n🏢 <?= addslashes(COMPANY_NAME) ?>'), '_blank');
            sent = true;
        " class="space-y-4">
            <div><label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number (with country code)</label><input x-ref="waPhone" type="tel" placeholder="+905551234567" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></div>
            <p class="text-xs text-gray-400">Leave empty to open WhatsApp without a specific recipient.</p>
            <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2"><i class="fab fa-whatsapp text-lg"></i> Share via WhatsApp</button>
        </form>
    </div>
</div>
</div>
