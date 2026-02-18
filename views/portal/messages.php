<?php
/**
 * Partner Portal — Messages (Chat Thread with Admin + File Sharing)
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-comments text-emerald-500 mr-2"></i>Messages</h1>
    <p class="text-sm text-gray-500 mt-1">Communicate with CYN Tourism team</p>
</div>

<div class="max-w-3xl">
    <!-- Message Thread -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 mb-4 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-inbox mr-1"></i>Conversation Thread</p>
        </div>

        <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto" id="messages-container">
            <?php if (empty($messages)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-comments text-3xl mb-3"></i>
                    <p>No messages yet. Send your first message below!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isPartner = ($msg['sender_type'] === 'partner'); ?>
                    <div class="flex <?= $isPartner ? 'justify-end' : 'justify-start' ?>">
                        <div class="max-w-[75%] <?= $isPartner ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' ?> rounded-2xl px-4 py-3 <?= $isPartner ? 'rounded-br-md' : 'rounded-bl-md' ?>">
                            <?php if (!empty($msg['subject'])): ?>
                                <p class="text-xs font-bold mb-1 <?= $isPartner ? 'text-blue-200' : 'text-gray-500' ?>"><?= e($msg['subject']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm"><?= nl2br(e($msg['message'])) ?></p>
                            <?php if (!empty($msg['file_path'])): ?>
                                <?php
                                $filePath = $msg['file_path'];
                                $fileName = basename($filePath);
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                ?>
                                <div class="mt-2 p-2 rounded-lg <?= $isPartner ? 'bg-blue-700/50' : 'bg-gray-200 dark:bg-gray-600' ?>">
                                    <?php if ($isImage): ?>
                                        <a href="<?= e($filePath) ?>" target="_blank">
                                            <img src="<?= e($filePath) ?>" alt="<?= e($fileName) ?>" class="max-w-full max-h-48 rounded-lg">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= e($filePath) ?>" target="_blank" download class="flex items-center gap-2 text-xs font-medium <?= $isPartner ? 'text-blue-100 hover:text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800' ?>">
                                            <i class="fas fa-file-download"></i>
                                            <span><?= e($fileName) ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <p class="text-[10px] mt-1 <?= $isPartner ? 'text-blue-200' : 'text-gray-400' ?>">
                                <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                <?= $isPartner ? '' : ' — CYN Tourism' ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Message Form with File Upload -->
    <form method="POST" action="<?= url('portal/messages/send') ?>" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
        <?= csrf_field() ?>
        <div class="mb-3">
            <input type="text" name="subject" placeholder="Subject (optional)"
                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm">
        </div>
        <div class="flex gap-3 items-end">
            <div class="flex-1 space-y-2">
                <textarea name="message" required rows="2" placeholder="Type your message..."
                          class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm resize-none"></textarea>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer transition">
                        <i class="fas fa-paperclip"></i>
                        <span id="portalFileLabel">Attach file</span>
                        <input type="file" name="attachment" class="hidden" onchange="document.getElementById('portalFileLabel').textContent = this.files[0]?.name || 'Attach file'">
                    </label>
                </div>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-paper-plane"></i>
                <span class="hidden sm:inline">Send</span>
            </button>
        </div>
    </form>
</div>

<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    if (container) container.scrollTop = container.scrollHeight;
});
</script>
