<?php
/**
 * Hotel Calendar View — Inspired by Transfer Calendar
 * Month view + Agenda view, status legend, popup with colored card headers & action links
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
$firstDay    = date('N', strtotime($startDate));
$daysInMonth = date('t', strtotime($startDate));
$today       = date('Y-m-d');

$eventsJson = json_encode($eventsByDate, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_TAG);
?>
<script>window.__hotelCalEvents = <?= $eventsJson ?>;</script>

<div x-data='{
    showPopup: false,
    popupDate: "",
    popupLabel: "",
    popupEvents: [],
    allEvents: {},
    view: window.innerWidth < 768 ? "agenda" : "month",
    init() { this.allEvents = window.__hotelCalEvents || {}; },
    openDay(dateStr) {
        this.popupDate  = dateStr;
        const d = new Date(dateStr + "T00:00:00");
        this.popupLabel = d.toLocaleDateString("en-GB", {weekday:"long", day:"numeric", month:"long", year:"numeric"});
        this.popupEvents = this.allEvents[dateStr] || [];
        this.showPopup  = true;
    },
    get agendaDays() {
        return Object.keys(this.allEvents).sort().filter(k => this.allEvents[k] && this.allEvents[k].length > 0);
    }
}' @keydown.escape.window="showPopup = false">

<!-- ── Page Header ── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <span class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-hotel text-white text-sm"></i>
            </span>
            <?= $monthNames[$month] ?> <?= $year ?>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-11"><?= __('hotel_calendar') ?: 'Hotel Calendar' ?> — Check-in / Check-out Overview</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Legend (desktop) -->
        <div class="hidden lg:flex items-center gap-3 mr-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Pending</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>Confirmed</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-teal-400 inline-block"></span>Checked In</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>Completed</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Cancelled</span>
        </div>
        <!-- View toggle -->
        <div class="flex rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden text-sm font-semibold">
            <button @click="view='month'" :class="view==='month' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'" class="px-3 py-2 transition"><i class="fas fa-th mr-1"></i><span class="hidden sm:inline">Month</span></button>
            <button @click="view='agenda'" :class="view==='agenda' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'" class="px-3 py-2 transition border-l border-gray-200 dark:border-gray-600"><i class="fas fa-list mr-1"></i><span class="hidden sm:inline">Agenda</span></button>
        </div>
        <!-- Nav -->
        <a href="<?= url('hotel-calendar') ?>?year=<?= $prevYear ?>&month=<?= $prevMonth ?>"
           class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm">
            <i class="fas fa-chevron-left"></i>
        </a>
        <a href="<?= url('hotel-calendar') ?>"
           class="px-3 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
            <?= __('today') ?>
        </a>
        <a href="<?= url('hotel-calendar') ?>?year=<?= $nextYear ?>&month=<?= $nextMonth ?>"
           class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<!-- ══ MONTH VIEW ══ -->
<div x-show="view==='month'" x-cloak>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

    <!-- Day headers -->
    <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700">
        <?php foreach ($dayNames as $dn): ?>
        <div class="py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest"><?= $dn ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Cells -->
    <div class="grid grid-cols-7">
        <?php for ($i = 1; $i < $firstDay; $i++): ?>
        <div class="min-h-[110px] border-b border-r border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40"></div>
        <?php endfor; ?>

        <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday   = ($dateStr === $today);
            $events    = $eventsByDate[$dateStr] ?? [];
            $evCount   = count($events);
            $dow       = date('N', strtotime($dateStr));
            $isWeekend = $dow >= 6;
        ?>
        <div
            class="min-h-[110px] border-b border-r border-gray-100 dark:border-gray-700/60 p-1.5 transition cursor-pointer group
                   <?= $isToday ? 'bg-emerald-50 dark:bg-emerald-900/15' : ($isWeekend ? 'bg-gray-50/60 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800') ?>
                   hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10"
            @click="openDay('<?= $dateStr ?>')"
        >
            <!-- Day number -->
            <div class="flex items-center justify-between mb-1.5">
                <span class="w-7 h-7 flex items-center justify-center rounded-full text-sm font-bold
                    <?= $isToday
                        ? 'bg-emerald-600 text-white shadow-sm'
                        : ($isWeekend ? 'text-gray-400 dark:text-gray-500' : 'text-gray-700 dark:text-gray-300') ?>">
                    <?= $d ?>
                </span>
                <?php if ($evCount > 0): ?>
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                    <?= $evCount ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Event pills (max 3) -->
            <?php foreach (array_slice($events, 0, 3) as $ev):
                $st = $ev['status'] ?? 'pending';
                $pillBg = match($st) {
                    'confirmed'   => 'bg-blue-50 dark:bg-blue-500/10 border-l-blue-500',
                    'checked_in'  => 'bg-teal-50 dark:bg-teal-500/10 border-l-teal-500',
                    'checked_out' => 'bg-emerald-50 dark:bg-emerald-500/10 border-l-emerald-500',
                    'completed'   => 'bg-emerald-50 dark:bg-emerald-500/10 border-l-emerald-500',
                    'cancelled'   => 'bg-red-50 dark:bg-red-500/10 border-l-red-400',
                    default       => 'bg-amber-50 dark:bg-amber-500/10 border-l-amber-400',
                };
                $guestShort  = mb_substr($ev['guest_name'] ?? '', 0, 10);
                $hotelShort  = mb_substr($ev['hotel_name'] ?? '', 0, 9);
            ?>
            <div class="mb-0.5 px-1.5 py-0.5 text-[10px] border-l-2 rounded-r <?= $pillBg ?> leading-tight">
                <div class="flex items-center gap-1 truncate">
                    <i class="fas fa-hotel text-[7px] text-emerald-500 shrink-0"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-200 truncate"><?= htmlspecialchars($guestShort) ?></span>
                </div>
                <div class="truncate text-gray-500 dark:text-gray-400"><?= htmlspecialchars($hotelShort) ?></div>
            </div>
            <?php endforeach; ?>

            <?php if ($evCount > 3): ?>
            <div class="text-[10px] text-emerald-500 dark:text-emerald-400 px-1.5 font-medium">+<?= $evCount - 3 ?> more</div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>

        <?php
        $lastDay = date('N', strtotime("$year-$month-$daysInMonth"));
        for ($i = $lastDay; $i < 7; $i++): ?>
        <div class="min-h-[110px] border-b border-r border-gray-100 dark:border-gray-700/60 bg-gray-50/40 dark:bg-gray-800/40"></div>
        <?php endfor; ?>
    </div>
</div>
</div><!-- end month view -->

<!-- ══ AGENDA VIEW ══ -->
<div x-show="view==='agenda'" x-cloak class="space-y-3">
    <template x-if="agendaDays.length === 0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center text-gray-400">
            <i class="fas fa-hotel text-3xl mb-3 block"></i>
            <p class="font-medium">No hotel check-ins this month</p>
        </div>
    </template>
    <template x-for="dateKey in agendaDays" :key="dateKey">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Date header -->
            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/60 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold"
                        :class="dateKey === '<?= $today ?>' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-500'"
                        x-text="new Date(dateKey+'T00:00:00').getDate()">
                    </span>
                    <div>
                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100"
                            x-text="new Date(dateKey+'T00:00:00').toLocaleDateString('en-GB',{weekday:'long',month:'short',day:'numeric'})">
                        </div>
                        <div class="text-xs text-gray-400" x-text="(allEvents[dateKey]||[]).length + ' booking' + ((allEvents[dateKey]||[]).length!==1?'s':'')"></div>
                    </div>
                </div>
                <button @click="openDay(dateKey)" class="text-xs text-emerald-500 hover:text-emerald-700 font-semibold transition">
                    View All <i class="fas fa-chevron-right text-[10px]"></i>
                </button>
            </div>
            <!-- Booking rows -->
            <div>
                <template x-for="(ev, i) in (allEvents[dateKey]||[])" :key="i">
                    <div class="flex items-center gap-3 px-4 py-3 border-b last:border-b-0 border-gray-100 dark:border-gray-700/60 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <!-- Status dot + nights -->
                        <div class="flex flex-col items-center gap-1 shrink-0 w-10">
                            <span class="w-2.5 h-2.5 rounded-full"
                                :class="{'bg-amber-400':ev.status==='pending','bg-blue-400':ev.status==='confirmed','bg-teal-400':ev.status==='checked_in','bg-emerald-400':ev.status==='completed'||ev.status==='checked_out','bg-red-400':ev.status==='cancelled'}">
                            </span>
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 text-center leading-tight">
                                <span x-text="ev.nights"></span>N
                            </span>
                        </div>
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="ev.voucher_no"></span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400"
                                    x-text="ev.room_type || 'DBL'"></span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate mt-0.5" x-text="ev.guest_name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                <i class="fas fa-hotel text-emerald-400 mr-1"></i>
                                <span x-text="ev.hotel_name"></span>
                            </div>
                        </div>
                        <!-- Check-in/out + actions -->
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="ev.check_in"></span>
                                <span class="mx-0.5 text-gray-300">→</span>
                                <span x-text="ev.check_out"></span>
                            </span>
                            <div class="flex gap-1">
                                <a :href="'<?= url('hotel-voucher/show') ?>?id='+ev.id" class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded-lg transition text-xs"><i class="fas fa-eye"></i></a>
                                <a :href="'<?= url('hotel-voucher/pdf') ?>?id='+ev.id" target="_blank" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition text-xs"><i class="fas fa-file-pdf"></i></a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div><!-- end agenda view -->

<!-- ══ Day Detail Popup ══ -->
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
    style="display:none;"
>
    <div
        x-show="showPopup"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-xl max-h-[85vh] overflow-hidden flex flex-col"
    >
        <!-- Modal header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-600">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fas fa-hotel"></i>
                    <span x-text="popupLabel"></span>
                </h3>
                <p class="text-xs text-emerald-100 mt-0.5" x-text="popupEvents.length + ' booking' + (popupEvents.length !== 1 ? 's' : '')"></p>
            </div>
            <button @click="showPopup = false" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-5 py-4 overflow-y-auto flex-1 space-y-3">

            <!-- Empty state -->
            <template x-if="popupEvents.length === 0">
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-hotel text-2xl text-gray-300 dark:text-gray-500"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No hotel bookings on this day</p>
                </div>
            </template>

            <!-- Booking cards -->
            <template x-for="(ev, idx) in popupEvents" :key="idx">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                    <!-- Card header — status coloured background -->
                    <div class="px-4 py-2.5 flex items-center justify-between"
                        :class="{
                            'bg-amber-50 dark:bg-amber-500/10 border-b border-amber-100 dark:border-amber-500/20':  ev.status === 'pending',
                            'bg-blue-50 dark:bg-blue-500/10 border-b border-blue-100 dark:border-blue-500/20':      ev.status === 'confirmed',
                            'bg-teal-50 dark:bg-teal-500/10 border-b border-teal-100 dark:border-teal-500/20':      ev.status === 'checked_in',
                            'bg-emerald-50 dark:bg-emerald-500/10 border-b border-emerald-100 dark:border-emerald-500/20': ev.status === 'completed' || ev.status === 'checked_out',
                            'bg-red-50 dark:bg-red-500/10 border-b border-red-100 dark:border-red-500/20':          ev.status === 'cancelled'
                        }">
                        <div class="flex items-center gap-2">
                            <!-- Nights badge -->
                            <span class="text-xs font-bold px-2 py-0.5 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-indigo-600 dark:text-indigo-300 font-mono"
                                x-text="(ev.nights || '?') + ' nights'">
                            </span>
                            <!-- Voucher no -->
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="ev.voucher_no"></span>
                        </div>
                        <!-- Status pill + room type + board type -->
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="{
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400':    ev.status === 'pending',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400':        ev.status === 'confirmed',
                                    'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-400':        ev.status === 'checked_in',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400': ev.status === 'completed' || ev.status === 'checked_out',
                                    'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400':            ev.status === 'cancelled'
                                }"
                                x-text="ev.status ? ev.status.replace('_',' ').toUpperCase() : ''">
                            </span>
                            <template x-if="ev.room_type">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400"
                                    x-text="ev.room_type"></span>
                            </template>
                            <template x-if="ev.board_type">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400"
                                    x-text="ev.board_type.replace('_',' ').toUpperCase()"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Card body -->
                    <div class="px-4 py-3 bg-white dark:bg-gray-800 space-y-2">

                        <!-- Guest name -->
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-gray-400 text-xs w-4 text-center"></i>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="ev.guest_name || '—'"></span>
                        </div>

                        <!-- Hotel -->
                        <div class="flex items-center gap-2">
                            <i class="fas fa-hotel text-emerald-400 text-xs w-4 text-center"></i>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="ev.hotel_name || '—'"></span>
                        </div>

                        <!-- Stay dates -->
                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-sign-in-alt text-blue-400 w-4 text-center"></i>
                                Check-in: <span class="font-mono font-semibold text-gray-700 dark:text-gray-200 ml-1" x-text="ev.check_in || '—'"></span>
                            </span>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-sign-out-alt text-red-400 w-4 text-center"></i>
                                Out: <span class="font-mono font-semibold text-gray-700 dark:text-gray-200 ml-1" x-text="ev.check_out || '—'"></span>
                            </span>
                        </div>

                        <!-- Pax + Company -->
                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-users text-gray-400 w-4 text-center"></i>
                                <span x-text="ev.total_pax || 1"></span> pax
                            </span>
                            <template x-if="ev.company_name">
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-building text-gray-400 w-4 text-center"></i>
                                    <span x-text="ev.company_name"></span>
                                </span>
                            </template>
                        </div>

                        <!-- Guest Program (compact) -->
                        <template x-if="ev.guest_program && ev.guest_program.length">
                            <div class="flex items-start gap-2 text-xs text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg px-2.5 py-2">
                                <i class="fas fa-route w-4 text-center mt-0.5 shrink-0"></i>
                                <div class="flex-1">
                                    <div class="font-semibold mb-1">Guest Program <span class="text-gray-400 font-normal" x-text="'(' + ev.guest_program.length + ' activities)'"></span></div>
                                    <table class="w-full text-[10px]">
                                        <thead><tr class="text-left text-gray-400 uppercase">
                                            <th class="pr-2 pb-0.5">Date</th><th class="pr-2 pb-0.5">Time</th>
                                            <th class="pr-2 pb-0.5">Service</th><th class="pb-0.5">Pickup</th>
                                        </tr></thead>
                                        <tbody>
                                            <template x-for="row in ev.guest_program" :key="row.date + row.service">
                                                <tr class="border-t border-indigo-100 dark:border-indigo-500/20">
                                                    <td class="py-0.5 pr-2" x-text="row.date || '—'"></td>
                                                    <td class="py-0.5 pr-2" x-text="row.time || '—'"></td>
                                                    <td class="py-0.5 pr-2 font-medium text-gray-700 dark:text-gray-200" x-text="row.service || '—'"></td>
                                                    <td class="py-0.5 text-gray-500" x-text="row.pickup || '—'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-1 border-t border-gray-100 dark:border-gray-700">
                            <a :href="'<?= url('hotel-voucher/show') ?>?id=' + ev.id"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 hover:underline transition">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <span class="text-gray-200 dark:text-gray-700">|</span>
                            <a :href="'<?= url('hotel-voucher/pdf') ?>?id=' + ev.id" target="_blank"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-600 dark:text-red-400 hover:underline transition">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <span class="text-gray-200 dark:text-gray-700">|</span>
                            <a :href="'<?= url('hotel-voucher/edit') ?>?id=' + ev.id"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal footer -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="popupLabel"></span>
            <button @click="showPopup = false"
                class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium text-sm transition">
                <?= __('close') ?>
            </button>
        </div>
    </div>
</div>

</div><!-- end Alpine scope -->
