<?php
/**
 * Admin — Partner Message Thread (with File Sharing)
 */
?>
<div class="mb-6 flex items-center gap-3">
    <a href="<?= url('partner-messages') ?>" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 hover:bg-gray-200">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Messages: <?= e($partner['company_name'] ?? 'Partner') ?></h1>
        <p class="text-sm text-gray-500"><?= e($partner['email'] ?? '') ?></p>
    </div>
</div>

<div class="max-w-3xl">
    <!-- Message Thread -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-4 overflow-hidden">
        <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto" id="admin-messages">
            <?php if (empty($messages)): ?>
                <p class="text-center text-gray-400 py-8">No messages yet</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
                    <div class="flex <?= $isAdmin ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-[75%] <?= $isAdmin ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' ?> rounded-2xl px-4 py-3 <?= $isAdmin ? 'rounded-br-md' : 'rounded-bl-md' ?>">
                            <?php if (!empty($msg['subject'])): ?>
                                <p class="text-xs font-bold mb-1 <?= $isAdmin ? 'text-blue-200' : 'text-gray-500' ?>"><?= e($msg['subject']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm"><?= nl2br(e($msg['message'])) ?></p>
                            <?php if (!empty($msg['file_path'])): ?>
                                <?php
                                $filePath = $msg['file_path'];
                                $fileName = basename($filePath);
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                ?>
                                <div class="mt-2 p-2 rounded-lg <?= $isAdmin ? 'bg-blue-700/50' : 'bg-gray-200 dark:bg-gray-600' ?>">
                                    <?php if ($isImage): ?>
                                        <a href="<?= e($filePath) ?>" target="_blank">
                                            <img src="<?= e($filePath) ?>" alt="<?= e($fileName) ?>" class="max-w-full max-h-48 rounded-lg">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= e($filePath) ?>" target="_blank" download class="flex items-center gap-2 text-xs font-medium <?= $isAdmin ? 'text-blue-100 hover:text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800' ?>">
                                            <i class="fas fa-file-download"></i>
                                            <span><?= e($fileName) ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <p class="text-[10px] mt-1 <?= $isAdmin ? 'text-blue-200' : 'text-gray-400' ?>">
                                <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                — <?= $isAdmin ? 'You' : e($partner['company_name'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Form with File Upload -->
    <form method="POST" action="<?= url('partner-messages/reply') ?>" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
        <input type="hidden" name="partner_id" value="<?= $partner['id'] ?>">
        <div class="flex gap-3 items-end">
            <div class="flex-1 space-y-2">
                <textarea name="message" required rows="2" placeholder="Type your reply..."
                          class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm resize-none"></textarea>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer transition">
                        <i class="fas fa-paperclip"></i>
                        <span id="fileLabel">Attach file</span>
                        <input type="file" name="attachment" class="hidden" onchange="document.getElementById('fileLabel').textContent = this.files[0]?.name || 'Attach file'">
                    </label>
                </div>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-paper-plane"></i> Reply
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const c = document.getElementById('admin-messages');
    if (c) c.scrollTop = c.scrollHeight;
});
</script>
