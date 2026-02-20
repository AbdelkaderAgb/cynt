<?php
/**
 * CYN Tourism — CalendarController
 */
class CalendarController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $view  = $_GET['view'] ?? 'month';
        $year  = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));
        $day   = (int)($_GET['day'] ?? date('d'));

        // Fetch vouchers for this month range
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        $transfers = Database::fetchAll(
            "SELECT id, voucher_no, company_name, pickup_date, pickup_time,
                    pickup_location, dropoff_location, transfer_type, total_pax, status,
                    flight_number, return_date, return_time, guest_name, notes
             FROM vouchers WHERE pickup_date BETWEEN ? AND ? ORDER BY pickup_time ASC",
            [$startDate, $endDate]
        );

        // Group by date; also index round-trips on their return date
        $eventsByDate = [];
        foreach ($transfers as $t) {
            $eventsByDate[$t['pickup_date']][] = $t;
            // Show round-trips on return date too (marked as return leg)
            if ($t['transfer_type'] === 'round_trip' && !empty($t['return_date'])
                && $t['return_date'] !== '1970-01-01'
                && $t['return_date'] !== $t['pickup_date']) {
                $rt        = $t;
                $rt['_is_return'] = true;
                $eventsByDate[$t['return_date']][] = $rt;
            }
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
            'pageTitle'    => __('calendar') ?: 'Calendar',
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

    public function tourCalendar(): void
    {
        $this->requireAuth();

        $year  = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));
        $day   = (int)($_GET['day'] ?? date('d'));

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        $tourVouchers = Database::fetchAll(
            "SELECT id, tour_code AS voucher_no, company_name,
                    tour_date AS pickup_date, '' AS pickup_time,
                    tour_name AS pickup_location, '' AS dropoff_location, 'one_way' AS transfer_type,
                    total_pax, status, '' AS flight_number, '' AS return_date, '' AS return_time,
                    guest_name, COALESCE(description, '') AS notes
             FROM tours
             WHERE tour_date BETWEEN ? AND ?
             ORDER BY tour_date ASC",
            [$startDate, $endDate]
        );

        $eventsByDate = [];
        foreach ($tourVouchers as $t) {
            $eventsByDate[$t['pickup_date']][] = $t;
        }

        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $this->view('calendar/index', [
            'view'         => $_GET['view'] ?? 'month',
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
            'pageTitle'    => __('tour_calendar') ?: 'Tour Calendar',
            'activePage'   => 'tour-calendar',
        ]);
    }
}
