<?php
/**
 * CYN Tourism — CalendarController
 */
class CalendarController extends Controller
{
    public function index(): void
    {
        $view  = $_GET['view'] ?? 'month';
        $year  = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));
        $day   = (int)($_GET['day'] ?? date('d'));

        // Fetch vouchers for this month range
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        $transfers = Database::fetchAll(
            "SELECT id, voucher_no, company_name, hotel_name, pickup_date, pickup_time,
                    pickup_location, dropoff_location, transfer_type, total_pax, status
             FROM vouchers WHERE pickup_date BETWEEN ? AND ? ORDER BY pickup_time ASC",
            [$startDate, $endDate]
        );

        // Group by date
        $eventsByDate = [];
        foreach ($transfers as $t) {
            $eventsByDate[$t['pickup_date']][] = $t;
        }

        // Navigation
        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $this->view('calendar/index', [
            'view'         => $view,
            'year'         => $year,
            'month'        => $month,
            'day'          => $day,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'eventsByDate' => $eventsByDate,
            'prevYear'     => $prevYear,
            'prevMonth'    => $prevMonth,
            'nextYear'     => $nextYear,
            'nextMonth'    => $nextMonth,
            'pageTitle'    => 'Takvim',
            'activePage'   => 'calendar',
        ]);
    }

    public function hotelCalendar(): void
    {
        $this->requireAuth();

        $year  = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        // Fetch hotel vouchers whose stay overlaps this month (including additional_services for calendar)
        $hotelVouchers = Database::fetchAll(
            "SELECT id, voucher_no, guest_name, hotel_name, company_name, room_type,
                    check_in, check_out, nights, total_pax, adults, children, infants,
                    board_type, status, additional_services
             FROM hotel_vouchers
             WHERE check_in <= ? AND check_out >= ?
             ORDER BY check_in ASC",
            [$endDate, $startDate]
        );

        // Attach guest program (linked tours/transfers) and legacy additional_services text; group by check-in date
        $eventsByDate = [];
        foreach ($hotelVouchers as $hv) {
            $hv['guest_program'] = HotelController::resolveGuestProgram($hv['id']);
            $addSvc = $hv['additional_services'] ?? '';
            $hv['additional_services_text'] = '';
            if (!empty($hv['guest_program'])) {
                $hv['additional_services_text'] = implode(' · ', array_map(function ($r) {
                    return ($r['date'] ?? '') . ' ' . ($r['time'] ?? '') . ' — ' . ($r['service'] ?? '');
                }, $hv['guest_program']));
            } elseif ($addSvc !== '') {
                $arr = json_decode($addSvc, true);
                if (is_array($arr)) {
                    $lines = [];
                    foreach ($arr as $s) {
                        $lines[] = (ucfirst($s['type'] ?? '') . ': ' . ($s['description'] ?? ''));
                    }
                    $hv['additional_services_text'] = implode(' · ', $lines);
                }
            }
            $eventsByDate[$hv['check_in']][] = $hv;
        }

        // Navigation
        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $this->view('calendar/hotel', [
            'year'         => $year,
            'month'        => $month,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'eventsByDate' => $eventsByDate,
            'prevYear'     => $prevYear,
            'prevMonth'    => $prevMonth,
            'nextYear'     => $nextYear,
            'nextMonth'    => $nextMonth,
            'pageTitle'    => __('hotel_calendar') ?: 'Hotel Calendar',
            'activePage'   => 'hotel-calendar',
        ]);
    }
}
