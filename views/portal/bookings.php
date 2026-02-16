<?php
/**
 * Partner Portal — Booking Requests List
 */
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-calendar-plus text-purple-500 mr-2"></i>Booking Requests</h1>
        <p class="text-sm text-gray-500 mt-1">Track your booking requests</p>
    </div>
    <a href="<?= url('portal/booking/create') ?>"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20">
        <i class="fas fa-plus"></i> New Request
    </a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> Booking request submitted successfully! We'll review it soon.
    </div>
<?php endif; ?>

<?php if (empty($requests)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-calendar-plus text-4xl mb-3"></i>
        <p class="mb-4">No booking requests yet</p>
        <a href="<?= url('portal/booking/create') ?>" class="text-blue-500 hover:underline">Create your first request →</a>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($requests as $req): ?>
            <?php
                $details = json_decode($req['details'], true) ?? [];
                $typeIcons = ['transfer' => 'fa-car-side text-blue-500', 'hotel' => 'fa-hotel text-purple-500', 'tour' => 'fa-map-marked-alt text-emerald-500'];
                $statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700'];
                $sc = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600';
                $icon = $typeIcons[$req['request_type']] ?? 'fa-receipt text-gray-500';
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
                            <i class="fas <?= $icon ?>"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider"><?= ucfirst($req['request_type']) ?> Request</span>
                            <p class="font-semibold text-sm text-gray-800 dark:text-white">
                                <?= e($details['guest_name'] ?? '') ?>
                                <?php if (!empty($details['destination'])): ?> — <?= e($details['destination']) ?><?php endif; ?>
                                <?php if (!empty($details['hotel_name'])): ?> — <?= e($details['hotel_name']) ?><?php endif; ?>
                                <?php if (!empty($details['tour_name'])): ?> — <?= e($details['tour_name']) ?><?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $sc ?>"><?= ucfirst($req['status']) ?></span>
                </div>

                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                    <?php if (!empty($details['date'])): ?>
                        <span><i class="fas fa-calendar mr-1"></i><?= e($details['date']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['check_in']) && !empty($details['check_out'])): ?>
                        <span><i class="fas fa-calendar-check mr-1"></i><?= e($details['check_in']) ?> → <?= e($details['check_out']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['room_type'])): ?>
                        <span><i class="fas fa-bed mr-1"></i><?= e($details['room_type']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['room_count']) && $details['room_count'] > 1): ?>
                        <span><i class="fas fa-door-open mr-1"></i><?= $details['room_count'] ?> rooms</span>
                    <?php endif; ?>
                    <?php if (!empty($details['adults'])): ?>
                        <span><i class="fas fa-users mr-1"></i><?= $details['adults'] ?> adults<?php if (!empty($details['children'])): ?>, <?= $details['children'] ?> children<?php endif; ?></span>
                    <?php elseif (!empty($details['pax'])): ?>
                        <span><i class="fas fa-users mr-1"></i><?= $details['pax'] ?> pax</span>
                    <?php endif; ?>
                    <span><i class="fas fa-clock mr-1"></i>Submitted: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></span>
                </div>

                <?php if (!empty($req['admin_notes'])): ?>
                    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm">
                        <p class="text-xs font-semibold text-blue-600 mb-1"><i class="fas fa-comment mr-1"></i>Admin Response:</p>
                        <p class="text-gray-700 dark:text-gray-300"><?= nl2br(e($req['admin_notes'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($details['notes'])): ?>
                    <p class="mt-2 text-xs text-gray-400"><i class="fas fa-sticky-note mr-1"></i><?= e($details['notes']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
