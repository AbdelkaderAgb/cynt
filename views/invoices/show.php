<?php
$inv = $invoice;
$sc = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700'][$inv['status']] ?? 'bg-gray-100 text-gray-600';
$sl = __($inv['status'] ?? 'draft') ?: ucfirst($inv['status'] ?? 'draft');
?>
<div x-data="{ showShare: false, shareTab: 'email', sending: false, sent: false, error: '' }">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3"><?= e($inv['invoice_no']) ?> <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?= $sc ?>"><?= $sl ?></span></h1></div>
    <div class="flex flex-wrap gap-2">
        <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>" target="_blank" class="px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-file-pdf"></i>PDF</a>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-print"></i><?= __('print') ?></button>
        <button @click="showShare = true" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-1.5"><i class="fas fa-share-alt"></i><?= __('share') ?: 'Share' ?></button>
        <?php if (empty($inv['partner_id'])): ?>
        <button onclick="sendToPortal(<?= $inv['id'] ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 transition flex items-center gap-1.5"><i class="fas fa-share-square"></i>Send to Portal</button>
        <?php else: ?>
        <span class="px-4 py-2 bg-purple-100 text-purple-700 rounded-xl text-sm font-semibold flex items-center gap-1.5"><i class="fas fa-check-circle"></i>On Portal</span>
        <?php endif; ?>
        <a href="<?= url('invoices/pdf') ?>?id=<?= $inv['id'] ?>&download=1" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5"><i class="fas fa-download"></i><?= __('download') ?></a>
        <a href="<?= url('invoices/edit') ?>?id=<?= $inv['id'] ?>" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition flex items-center gap-1.5"><i class="fas fa-edit"></i><?= __('edit') ?></a>
        <a href="<?= url('invoices') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 transition flex items-center gap-1.5"><i class="fas fa-arrow-left"></i><?= __('back') ?></a>
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
            <?php if (!empty($inv['notes'])): ?><div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl"><p class="text-xs text-gray-400 mb-1"><?= __('notes') ?></p><p class="text-gray-600 dark:text-gray-300"><?= nl2br(e($inv['notes'])) ?></p></div><?php endif; ?>
        </div>

        <!-- Line Items -->
        <?php if (!empty($invoiceItems)): ?>
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
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4"><?= __('total_amount') ?: 'Financial Summary' ?></h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('subtotal') ?></span><span class="font-medium"><?= number_format($inv['subtotal'] ?? 0, 2) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('tax') ?> (<?= $inv['tax_rate'] ?? 0 ?>%)</span><span class="font-medium"><?= number_format($inv['tax_amount'] ?? 0, 2) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('discount') ?></span><span class="font-medium text-red-500">-<?= number_format($inv['discount'] ?? 0, 2) ?></span></div>
            <div class="border-t pt-3 flex justify-between"><span class="font-semibold"><?= __('total_amount') ?></span><span class="text-xl font-bold text-emerald-600"><?= number_format($inv['total_amount'] ?? 0, 2) ?> <?= $inv['currency'] ?? 'USD' ?></span></div>
            <div class="flex justify-between"><span class="text-gray-400 text-sm"><?= __('paid') ?></span><span class="font-medium text-blue-600"><?= number_format($inv['paid_amount'] ?? 0, 2) ?></span></div>
        </div>
    </div>
</div>

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
