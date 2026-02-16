<?php
/**
 * Admin — Partner Messages List (all partners with messages)
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-comments text-emerald-500 mr-2"></i>Partner Messages</h1>
    <p class="text-sm text-gray-500 mt-1">Messages from partner portals</p>
</div>

<?php if (empty($partners)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-inbox text-4xl mb-3"></i>
        <p>No messages from partners yet</p>
    </div>
<?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <?php foreach ($partners as $p): ?>
                <a href="<?= url('partner-messages?partner_id=' . $p['id']) ?>"
                   class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            <?= strtoupper(substr($p['company_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-800 dark:text-white"><?= e($p['company_name']) ?></p>
                            <p class="text-xs text-gray-500"><?= e($p['email']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if ($p['last_message_at']): ?>
                            <span class="text-xs text-gray-400"><?= date('d/m H:i', strtotime($p['last_message_at'])) ?></span>
                        <?php endif; ?>
                        <?php if ($p['unread_count'] > 0): ?>
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $p['unread_count'] ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
