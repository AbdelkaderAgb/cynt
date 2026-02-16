<?php
/**
 * CYN Tourism - Calendar System (Consolidated)
 * Merged: Calendar.php + cal.php + calendar-view.php + tour-calendar.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

// Get view type
$view = $_GET['view'] ?? 'month';
$year = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));
$day = intval($_GET['day'] ?? date('j'));

// Validate date
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

// Get transfers for the month
$startDate = sprintf("%04d-%02d-01", $year, $month);
$endDate = date("Y-m-t", strtotime($startDate));

try {
    $transfers = Database::fetchAll(
        "SELECT * FROM vouchers WHERE pickup_date BETWEEN ? AND ? ORDER BY pickup_date, pickup_time",
        [$startDate, $endDate]
    );
} catch (Exception $e) {
    $transfers = [];
}

// Group transfers by date
$eventsByDate = [];
foreach ($transfers as $transfer) {
    $date = $transfer['pickup_date'];
    if (!isset($eventsByDate[$date])) {
        $eventsByDate[$date] = [];
    }
    $eventsByDate[$date][] = $transfer;
}

$pageTitle = 'Takvim';
$activePage = 'calendar';
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title"><?php echo __('calendar'); ?></h1>
        </div>
        <div class="page-actions">
            <a href="?view=month&year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-sm <?php echo $view == 'month' ? 'btn-primary' : 'btn-secondary'; ?>">Ay</a>
            <a href="?view=week&year=<?php echo $year; ?>&month=<?php echo $month; ?>&day=<?php echo $day; ?>" class="btn btn-sm <?php echo $view == 'week' ? 'btn-primary' : 'btn-secondary'; ?>">Hafta</a>
            <a href="?view=day&year=<?php echo $year; ?>&month=<?php echo $month; ?>&day=<?php echo $day; ?>" class="btn btn-sm <?php echo $view == 'day' ? 'btn-primary' : 'btn-secondary'; ?>">Gun</a>
        </div>
    </div>
</div>

<div class="calendar-nav">
    <a href="?view=<?php echo $view; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>&month=<?php echo $month == 1 ? 12 : $month - 1; ?>&day=<?php echo $day; ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-chevron-left"></i>
    </a>
    <h2><?php echo date('F Y', strtotime($startDate)); ?></h2>
    <a href="?view=<?php echo $view; ?>&year=<?php echo $month == 12 ? $year + 1 : $year; ?>&month=<?php echo $month == 12 ? 1 : $month + 1; ?>&day=<?php echo $day; ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<?php if ($view == 'month'): ?>
<!-- Month View -->
<div class="calendar-month">
    <div class="calendar-header">
        <div class="calendar-day-header">Pzt</div>
        <div class="calendar-day-header">Sali</div>
        <div class="calendar-day-header">Crs</div>
        <div class="calendar-day-header">Prs</div>
        <div class="calendar-day-header">Cuma</div>
        <div class="calendar-day-header">Cmt</div>
        <div class="calendar-day-header">Paz</div>
    </div>
    <div class="calendar-body">
        <?php
        $firstDay = date('N', strtotime($startDate));
        $daysInMonth = date('t', strtotime($startDate));
        $today = date('Y-m-d');
        
        // Empty cells for days before start of month
        for ($i = 1; $i < $firstDay; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }
        
        // Days of month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $isToday = ($date == $today);
            $hasEvents = isset($eventsByDate[$date]);
            $eventCount = $hasEvents ? count($eventsByDate[$date]) : 0;
            
            echo '<div class="calendar-day ' . ($isToday ? 'today' : '') . ' ' . ($hasEvents ? 'has-events' : '') . '">';
            echo '<div class="day-number">' . $day . '</div>';
            
            if ($hasEvents) {
                echo '<div class="events">';
                foreach (array_slice($eventsByDate[$date], 0, 3) as $event) {
                    echo '<div class="event-item" title="' . htmlspecialchars($event['company_name']) . '">';
                    echo '<span class="event-time">' . substr($event['pickup_time'], 0, 5) . '</span> ';
                    echo htmlspecialchars(substr($event['company_name'], 0, 15));
                    echo '</div>';
                }
                if ($eventCount > 3) {
                    echo '<div class="more-events">+' . ($eventCount - 3) . ' daha</div>';
                }
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php elseif ($view == 'week'): ?>
<!-- Week View -->
<div class="calendar-week">
    <?php
    $weekStart = strtotime("monday this week", strtotime("$year-$month-$day"));
    for ($i = 0; $i < 7; $i++) {
        $currentDay = date('Y-m-d', strtotime("+$i days", $weekStart));
        $dayName = date('D', strtotime($currentDay));
        $dayNum = date('j', strtotime($currentDay));
        $hasEvents = isset($eventsByDate[$currentDay]);
        ?>
        <div class="week-day <?php echo ($currentDay == date('Y-m-d')) ? 'today' : ''; ?>">
            <div class="week-day-header">
                <span class="day-name"><?php echo $dayName; ?></span>
                <span class="day-number"><?php echo $dayNum; ?></span>
            </div>
            <div class="week-day-events">
                <?php if ($hasEvents): ?>
                    <?php foreach ($eventsByDate[$currentDay] as $event): ?>
                    <div class="week-event">
                        <span class="event-time"><?php echo substr($event['pickup_time'], 0, 5); ?></span>
                        <span class="event-title"><?php echo htmlspecialchars($event['company_name']); ?></span>
                        <span class="event-pax"><?php echo $event['total_pax']; ?> kisi</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-events">Transfer yok</div>
                <?php endif; ?>
            </div>
        </div>
    <?php } ?>
</div>

<?php else: ?>
<!-- Day View -->
<div class="calendar-day-view">
    <h3><?php echo date('d F Y l', strtotime("$year-$month-$day")); ?></h3>
    <?php
    $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $day);
    $dayEvents = $eventsByDate[$currentDate] ?? [];
    ?>
    
    <?php if (!empty($dayEvents)): ?>
    <div class="day-events-list">
        <?php foreach ($dayEvents as $event): ?>
        <div class="day-event-card">
            <div class="event-time">
                <i class="fas fa-clock"></i>
                <?php echo substr($event['pickup_time'], 0, 5); ?>
            </div>
            <div class="event-details">
                <h4><?php echo htmlspecialchars($event['company_name']); ?></h4>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['pickup_location']); ?> → <?php echo htmlspecialchars($event['dropoff_location']); ?></p>
                <p><i class="fas fa-users"></i> <?php echo $event['total_pax']; ?> kisi</p>
                <?php if ($event['flight_number']): ?>
                <p><i class="fas fa-plane"></i> <?php echo htmlspecialchars($event['flight_number']); ?></p>
                <?php endif; ?>
            </div>
            <div class="event-actions">
                <a href="view-transfer.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Goruntule</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-calendar-day"></i>
        <p>Bu gun icin transfer bulunmuyor</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: white; border-radius: 8px; }
.calendar-nav h2 { margin: 0; }
.calendar-month { background: white; border-radius: 8px; overflow: hidden; }
.calendar-header { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8f9fa; }
.calendar-day-header { padding: 10px; text-align: center; font-weight: 600; }
.calendar-body { display: grid; grid-template-columns: repeat(7, 1fr); }
.calendar-day { min-height: 100px; padding: 10px; border: 1px solid #e0e0e0; }
.calendar-day.empty { background: #f8f9fa; }
.calendar-day.today { background: #e3f2fd; }
.calendar-day.has-events { background: #fff8e1; }
.day-number { font-weight: 600; margin-bottom: 5px; }
.event-item { font-size: 11px; padding: 2px 5px; margin-bottom: 2px; background: #2196f3; color: white; border-radius: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.event-time { font-weight: bold; }
.more-events { font-size: 10px; color: #666; text-align: center; }
.calendar-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
.week-day { background: white; border-radius: 8px; overflow: hidden; }
.week-day.today { border: 2px solid #2196f3; }
.week-day-header { padding: 10px; background: #f8f9fa; text-align: center; border-bottom: 1px solid #e0e0e0; }
.week-day-events { padding: 10px; min-height: 200px; }
.week-event { padding: 8px; margin-bottom: 8px; background: #e3f2fd; border-radius: 4px; font-size: 12px; }
.no-events { text-align: center; color: #999; padding: 20px; }
.calendar-day-view { background: white; padding: 20px; border-radius: 8px; }
.day-events-list { margin-top: 20px; }
.day-event-card { display: flex; gap: 20px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; border-radius: 8px; }
.event-time { font-size: 24px; font-weight: bold; color: #2196f3; min-width: 80px; }
.event-details { flex: 1; }
.event-details h4 { margin: 0 0 10px 0; }
.event-details p { margin: 5px 0; color: #666; }
</style>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
