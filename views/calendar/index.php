<?php
/**
 * Calendar View — Enhanced with day-detail popup and translated labels
 *
 * Variables: $year, $month, $day, $startDate, $endDate, $eventsByDate,
 *            $prevYear, $prevMonth, $nextYear, $nextMonth
 */
$monthNames = [
    '', __('january'), __('february'), __('march'), __('april'),
    __('may'), __('june'), __('july'), __('august'),
    __('september'), __('october'), __('november'), __('december')
];
$dayNames = [
    __('mon'), __('tue'), __('wed'), __('thu'),
    __('fri'), __('sat'), __('sun')
];
$firstDay = date('N', strtotime($startDate));
$daysInMonth = date('t', strtotime($startDate));
$today = date('Y-m-d');

// Serialize events for JS popup
$eventsJson = json_encode($eventsByDate, JSON_UNESCAPED_UNICODE);
?>

<div x-data='{
    showPopup: false,
    popupDate: "",
    popupEvents: [],
    allEvents: <?= $eventsJson ?>,
    openDay(dateStr) {
        this.popupDate = dateStr;
        this.popupEvents = this.allEvents[dateStr] || [];
        this.showPopup = true;
    }
}'>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i><?= $monthNames[$month] ?> <?= $year ?>
        </h1>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('calendar') ?>?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="fas fa-chevron-left"></i>
        </a>
        <a href="<?= url('calendar') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
            <?= __('today') ?>
        </a>
        <a href="<?= url('calendar') ?>?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Day Headers -->
    <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-700/50 border-b">
        <?php foreach ($dayNames as $d): ?>
        <div class="px-2 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= $d ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7">
        <?php
        // Empty cells before first day
        for ($i = 1; $i < $firstDay; $i++): ?>
        <div class="min-h-[100px] border-b border-r border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50"></div>
        <?php endfor; ?>

        <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday = ($dateStr === $today);
            $events = $eventsByDate[$dateStr] ?? [];
            $dayOfWeek = date('N', strtotime($dateStr));
        ?>
        <div
            class="min-h-[100px] border-b border-r border-gray-100 dark:border-gray-700 p-1.5 <?= $isToday ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' ?> hover:bg-gray-50 dark:hover:bg-gray-700/20 transition cursor-pointer"
            @click="openDay('<?= $dateStr ?>')"
        >
            <!-- Day Number -->
            <div class="flex items-center justify-between mb-1">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-semibold <?= $isToday ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300' ?>"><?= $d ?></span>
                <?php if (count($events)): ?>
                <span class="text-xs text-gray-400"><?= count($events) ?></span>
                <?php endif; ?>
            </div>

            <!-- Events Preview (max 3) -->
            <?php foreach (array_slice($events, 0, 3) as $ev):
                $evColors = ['pending'=>'border-l-amber-400 bg-amber-50 dark:bg-amber-500/10','confirmed'=>'border-l-blue-400 bg-blue-50 dark:bg-blue-500/10','completed'=>'border-l-emerald-400 bg-emerald-50 dark:bg-emerald-500/10'];
                $ec = $evColors[$ev['status']] ?? 'border-l-gray-300 bg-gray-50 dark:bg-gray-700/20';
            ?>
            <div class="mb-0.5 px-1.5 py-0.5 text-xs border-l-2 rounded-r <?= $ec ?> truncate" title="<?= e($ev['company_name']) ?>">
                <span class="font-semibold"><?= $ev['pickup_time'] ?></span> <?= e(mb_substr($ev['company_name'], 0, 12)) ?>
            </div>
            <?php endforeach; ?>

            <?php if (count($events) > 3): ?>
            <p class="text-xs text-gray-400 px-1.5">+<?= count($events) - 3 ?> <?= __('more_events') ?></p>
            <?php endif; ?>
        </div>
        <?php endfor; ?>

        <?php
        // Empty cells after last day
        $lastDay = date('N', strtotime("$year-$month-$daysInMonth"));
        for ($i = $lastDay; $i < 7; $i++): ?>
        <div class="min-h-[100px] border-b border-r border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50"></div>
        <?php endfor; ?>
    </div>
</div>

<!-- Day Detail Popup Modal -->
<div
    x-show="showPopup"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
    @click.self="showPopup = false"
    @keydown.escape.window="showPopup = false"
    style="display: none;"
>
    <div
        x-show="showPopup"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-lg max-h-[80vh] overflow-hidden flex flex-col"
    >
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-500"></i>
                    <span x-text="popupDate"></span>
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= __('events_on_day') ?></p>
            </div>
            <button @click="showPopup = false" class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 overflow-y-auto flex-1">
            <!-- No events -->
            <template x-if="popupEvents.length === 0">
                <div class="text-center py-10">
                    <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-times text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium"><?= __('no_events') ?></p>
                </div>
            </template>

            <!-- Events list -->
            <template x-if="popupEvents.length > 0">
                <div class="space-y-3">
                    <template x-for="(ev, idx) in popupEvents" :key="idx">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-shadow relative overflow-hidden">
                            <!-- Status accent -->
                            <div class="absolute left-0 top-0 bottom-0 w-1"
                                :class="{
                                    'bg-amber-400': ev.status === 'pending',
                                    'bg-blue-400': ev.status === 'confirmed',
                                    'bg-emerald-400': ev.status === 'completed',
                                    'bg-red-400': ev.status === 'cancelled'
                                }"></div>

                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 text-xs font-bold px-2 py-1 rounded-lg" x-text="ev.pickup_time"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="ev.voucher_no"></span>
                                </div>
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                    :class="{
                                        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400': ev.status === 'pending',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400': ev.status === 'confirmed',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400': ev.status === 'completed',
                                        'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': ev.status === 'cancelled'
                                    }"
                                    x-text="ev.status"></span>
                            </div>

                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-1" x-text="ev.company_name"></div>

                            <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <i class="fas fa-route text-blue-400 mr-1"></i>
                                <span x-text="ev.pickup_location"></span>
                                <i class="fas fa-arrow-right text-gray-300 mx-1"></i>
                                <span x-text="ev.dropoff_location"></span>
                            </div>

                            <div class="flex items-center gap-4 text-xs text-gray-500 mt-2">
                                <span><i class="fas fa-users mr-1"></i> <span x-text="ev.total_pax"></span> <?= __('pax') ?></span>
                                <span x-show="ev.hotel_name"><i class="fas fa-hotel mr-1"></i> <span x-text="ev.hotel_name"></span></span>
                            </div>

                            <div class="mt-3">
                                <a :href="'<?= url('vouchers/show') ?>?id=' + ev.id"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 transition">
                                    <i class="fas fa-external-link-alt"></i> <?= __('details') ?>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-500 flex items-center justify-between">
            <span x-text="popupEvents.length + ' <?= __('events_on_day') ?>'"></span>
            <button @click="showPopup = false" class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition">
                <?= __('close') ?>
            </button>
        </div>
    </div>
</div>

</div> <!-- end Alpine scope -->
