<?php
/**
 * Mission Calendar View — FullCalendar integration
 */
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-calendar-alt mr-2 text-indigo-500"></i><?= __('missions') ?: 'Missions' ?> — <?= __('calendar_view') ?: 'Calendar' ?></h1>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('missions') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold text-sm hover:bg-gray-50 transition">
            <i class="fas fa-list"></i> <?= __('list_view') ?: 'List View' ?>
        </a>
        <a href="<?= url('missions/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition-all hover:-translate-y-0.5">
            <i class="fas fa-plus"></i> <?= __('new_mission') ?: 'New Mission' ?>
        </a>
    </div>
</div>

<!-- Legend -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-wrap items-center gap-4 text-xs">
        <span class="font-semibold text-gray-500 uppercase"><?= __('status') ?: 'Status' ?>:</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-400 inline-block"></span> Pending</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Assigned</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> In Progress</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Completed</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Cancelled</span>
    </div>
</div>

<!-- Calendar Container -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div id="missionCalendar" style="min-height: 600px;"></div>
</div>

<!-- FullCalendar CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('missionCalendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: 4,
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        events: function(info, successCallback, failureCallback) {
            fetch('<?= url('missions/calendar-data') ?>?start=' + info.startStr.substring(0,10) + '&end=' + info.endStr.substring(0,10))
                .then(function(resp) { return resp.json(); })
                .then(function(data) { successCallback(data); })
                .catch(function(err) { failureCallback(err); });
        },
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
        eventDidMount: function(info) {
            if (info.event.extendedProps && info.event.extendedProps.pax) {
                info.el.title = info.event.title +
                    '\nPax: ' + info.event.extendedProps.pax +
                    (info.event.extendedProps.driver ? '\nDriver: ' + info.event.extendedProps.driver : '') +
                    (info.event.extendedProps.guide ? '\nGuide: ' + info.event.extendedProps.guide : '') +
                    (info.event.extendedProps.vehicle ? '\nVehicle: ' + info.event.extendedProps.vehicle : '');
            }
        }
    });
    calendar.render();
});
</script>

<style>
    .fc { font-family: 'Inter', sans-serif; font-size: 12px; }
    .fc .fc-toolbar-title { font-size: 1.1rem; font-weight: 700; }
    .fc .fc-button { font-size: 0.75rem; padding: 4px 10px; border-radius: 8px; }
    .fc .fc-button-primary { background: #6366f1; border-color: #6366f1; }
    .fc .fc-button-primary:hover { background: #4f46e5; border-color: #4f46e5; }
    .fc .fc-button-primary:not(:disabled).fc-button-active { background: #4338ca; border-color: #4338ca; }
    .fc .fc-daygrid-event { border-radius: 4px; padding: 1px 4px; font-size: 10px; }
    .fc .fc-event { cursor: pointer; }
    .dark .fc { color: #e2e8f0; }
    .dark .fc .fc-col-header-cell { background: rgba(51,65,85,0.5); }
    .dark .fc td, .dark .fc th { border-color: rgba(51,65,85,0.5); }
    .dark .fc .fc-day-today { background: rgba(99,102,241,0.1); }
</style>
