<?php
/**
 * Admin — Partner Booking Requests
 */
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-calendar-check text-purple-500 mr-2"></i>Partner Booking Requests</h1>
    <p class="text-sm text-gray-500 mt-1">Review and respond to partner booking requests</p>
</div>

<?php if (empty($requests)): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
        <i class="fas fa-inbox text-4xl mb-3"></i>
        <p>No booking requests from partners yet</p>
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
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5" x-data="{ showAction: false }">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center">
                            <i class="fas <?= $icon ?>"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider"><?= ucfirst($req['request_type']) ?> — <?= e($req['partner_name'] ?? 'Unknown') ?></span>
                            <p class="font-semibold text-sm text-gray-800 dark:text-white">
                                <?= e($details['guest_name'] ?? '') ?>
                                <?php if (!empty($details['destination'])): ?> → <?= e($details['destination']) ?><?php endif; ?>
                                <?php if (!empty($details['hotel_name'])): ?> — <?= e($details['hotel_name']) ?><?php endif; ?>
                                <?php if (!empty($details['tour_name'])): ?> — <?= e($details['tour_name']) ?><?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $sc ?>"><?= ucfirst($req['status']) ?></span>
                </div>

                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                    <?php if (!empty($details['date'])): ?><span><i class="fas fa-calendar mr-1"></i><?= e($details['date']) ?></span><?php endif; ?>
                    <?php if (!empty($details['check_in']) && !empty($details['check_out'])): ?>
                        <span><i class="fas fa-calendar-check mr-1"></i><?= e($details['check_in']) ?> → <?= e($details['check_out']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['room_type'])): ?>
                        <span><i class="fas fa-bed mr-1"></i><?= e($details['room_type']) ?><?php if (!empty($details['room_count']) && $details['room_count'] > 1) echo ' ×' . $details['room_count']; ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['board_type'])): ?>
                        <span><i class="fas fa-utensils mr-1"></i><?= ucwords(str_replace('_', ' ', $details['board_type'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($details['adults'])): ?>
                        <span><i class="fas fa-users mr-1"></i><?= $details['adults'] ?> adults<?php if (!empty($details['children'])) echo ', ' . $details['children'] . ' children'; ?></span>
                    <?php elseif (!empty($details['pax'])): ?><span><i class="fas fa-users mr-1"></i><?= $details['pax'] ?> pax</span><?php endif; ?>
                    <?php if (!empty($details['pickup_location'])): ?><span><i class="fas fa-map-pin mr-1"></i><?= e($details['pickup_location']) ?></span><?php endif; ?>
                    <span><i class="fas fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></span>
                </div>
                <?php if (!empty($details['notes'])): ?>
                    <p class="mt-2 text-xs text-gray-400"><i class="fas fa-sticky-note mr-1"></i><?= e($details['notes']) ?></p>
                <?php endif; ?>

                <?php if ($req['status'] === 'pending'): ?>
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button @click="showAction = !showAction" class="text-sm text-blue-500 hover:underline">
                            <i class="fas fa-reply mr-1"></i>Respond
                        </button>
                        <form x-show="showAction" method="POST" action="<?= url('partner-requests/action') ?>" class="mt-3 space-y-3">
                            <input type="hidden" name="id" value="<?= $req['id'] ?>">
                            <textarea name="admin_notes" rows="2" placeholder="Notes to partner..."
                                      class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm"></textarea>
                            <div class="flex gap-2">
                                <button type="submit" name="action" value="approved" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                                <button type="submit" name="action" value="rejected" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </div>
                        </form>
                    </div>
                <?php elseif (!empty($req['admin_notes'])): ?>
                    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm">
                        <p class="text-xs font-semibold text-blue-600 mb-1">Your Response:</p>
                        <p><?= nl2br(e($req['admin_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
