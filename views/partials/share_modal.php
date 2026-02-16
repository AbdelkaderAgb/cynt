<?php
/**
 * Share Modal — Email & WhatsApp
 * Variables: $shareType ('invoice' or 'voucher'), $shareId, $shareDocNo
 */
$shareType  = $shareType ?? 'invoice';
$shareId    = $shareId ?? 0;
$shareDocNo = $shareDocNo ?? '';
?>

<!-- Share Modal (Alpine.js) -->
<div x-data="{ showShare: false, shareTab: 'email', sending: false, sent: false, error: '' }"
     x-show="showShare" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showShare = false">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6" @click.outside="showShare = false">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-share-alt text-blue-500"></i> Share Document
            </h2>
            <button @click="showShare = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-lg"></i></button>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-5">
            <button @click="shareTab = 'email'; error = ''; sent = false"
                    :class="shareTab === 'email' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button @click="shareTab = 'whatsapp'; error = ''; sent = false"
                    :class="shareTab === 'whatsapp' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
        </div>

        <!-- Success message -->
        <div x-show="sent" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i> <span x-text="shareTab === 'email' ? 'Email sent successfully!' : 'Redirecting to WhatsApp...'"></span>
        </div>

        <!-- Error message -->
        <div x-show="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <span x-text="error"></span>
        </div>

        <!-- Email Form -->
        <form x-show="shareTab === 'email'" @submit.prevent="
            sending = true; error = ''; sent = false;
            fetch('<?= url('export/email') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    type: '<?= $shareType ?>',
                    id: '<?= $shareId ?>',
                    email: $refs.emailTo.value,
                    subject: $refs.emailSubject.value,
                    message: $refs.emailMessage.value
                })
            })
            .then(r => r.json())
            .then(d => { sending = false; if(d.success) { sent = true; } else { error = d.message || 'Failed to send.'; } })
            .catch(() => { sending = false; error = 'Network error.'; });
        " class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Recipient Email</label>
                <input x-ref="emailTo" type="email" required placeholder="recipient@example.com"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Subject</label>
                <input x-ref="emailSubject" type="text" value="<?= htmlspecialchars($shareDocNo) ?> — <?= htmlspecialchars(COMPANY_NAME) ?>"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Message (optional)</label>
                <textarea x-ref="emailMessage" rows="3" placeholder="Additional message..."
                          class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <button type="submit" :disabled="sending"
                    class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-50 flex items-center justify-center gap-2">
                <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                <span x-text="sending ? 'Sending...' : 'Send Email with PDF'"></span>
            </button>
        </form>

        <!-- WhatsApp Form -->
        <form x-show="shareTab === 'whatsapp'" @submit.prevent="
            const phone = $refs.waPhone.value.replace(/[^0-9]/g, '');
            const url = '<?= url('export/whatsapp') ?>?type=<?= $shareType ?>&id=<?= $shareId ?>&phone=' + phone;
            window.open(url, '_blank');
            sent = true;
        " class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number (with country code)</label>
                <input x-ref="waPhone" type="tel" placeholder="+905551234567"
                       class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <p class="text-xs text-gray-400">Leave empty to open WhatsApp without a specific recipient.</p>
            <button type="submit"
                    class="w-full py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <i class="fab fa-whatsapp text-lg"></i> Share via WhatsApp
            </button>
        </form>
    </div>
</div>
