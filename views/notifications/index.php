<?php /** Notifications View */ $typeIcons = ['info'=>'fa-info-circle text-blue-500','success'=>'fa-check-circle text-emerald-500','warning'=>'fa-exclamation-triangle text-amber-500','error'=>'fa-times-circle text-red-500']; ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= __('notifications') ?: 'Notifications' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= $unreadCount ?> unread</p>
    </div>
    <?php if ($unreadCount > 0): ?>
    <a href="<?= url('notifications/mark-all-read') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
        <i class="fas fa-check-double"></i> Mark All Read
    </a>
    <?php endif; ?>
</div>
<div class="space-y-3">
    <?php if (empty($notifications)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-bell-slash text-4xl mb-3 block"></i>No notifications
    </div>
    <?php else: foreach ($notifications as $n): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border <?= $n['is_read'] ? 'border-gray-200 dark:border-gray-700' : 'border-blue-300 dark:border-blue-700 bg-blue-50/50 dark:bg-blue-900/10' ?> p-4 flex items-start gap-4 transition hover:shadow-md">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            <i class="fas <?= $typeIcons[$n['type']] ?? 'fa-bell text-gray-400' ?>"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white"><?= e($n['title']) ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5"><?= e($n['message']) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></p>
        </div>
        <?php if (!$n['is_read']): ?>
        <a href="<?= url('notifications/mark-read') ?>?id=<?= $n['id'] ?>" class="flex-shrink-0 p-2 text-gray-400 hover:text-blue-600 rounded-lg transition" title="Mark as read"><i class="fas fa-check"></i></a>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>
