<?php
/**
 * CYN Tourism - Dashboard Model
 * 
 * Encapsulates all dashboard data queries including
 * statistics, trends, and multi-currency revenue.
 * 
 * @package CYN_Tourism
 * @version 4.0.0
 */

class Dashboard
{
    /**
     * Get all dashboard statistics
     */
    public static function getStats(): array
    {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-7 days'));
        $monthStart = date('Y-m-01');
        $db = Database::getInstance();

        $stats = [
            'todayTransfers'    => 0,
            'weeklyRevenue'     => 0,
            'monthRevenue'      => 0,
            'monthVouchers'     => 0,
            'monthHotelVouchers'=> 0,
            'monthTourVouchers' => 0,
            'pendingInvoices'   => 0,
            'totalPartners'     => 0,
            'totalVehicles'     => 0,
            'totalDrivers'      => 0,
        ];

        $queries = [
            'todayTransfers'  => ["SELECT COUNT(*) as val FROM vouchers WHERE pickup_date = ?", [$today]],
            'weeklyRevenue'   => ["SELECT COALESCE(SUM(total_amount), 0) as val FROM invoices WHERE created_at >= ? AND status = 'paid'", [$weekStart . ' 00:00:00']],
            'monthRevenue'    => ["SELECT COALESCE(SUM(total_amount), 0) as val FROM invoices WHERE created_at >= ? AND status = 'paid'", [$monthStart . ' 00:00:00']],
            'monthVouchers'   => ["SELECT COUNT(*) as val FROM vouchers WHERE created_at >= ?", [$monthStart . ' 00:00:00']],
            'monthHotelVouchers' => ["SELECT COUNT(*) as val FROM hotel_vouchers WHERE created_at >= ?", [$monthStart . ' 00:00:00']],
            'monthTourVouchers'  => ["SELECT COUNT(*) as val FROM tours WHERE created_at >= ?", [$monthStart . ' 00:00:00']],
            'pendingInvoices' => ["SELECT COUNT(*) as val FROM invoices WHERE status IN ('pending','draft')", []],
            'totalPartners'   => ["SELECT COUNT(*) as val FROM partners WHERE status = 'active'", []],
            'totalVehicles'   => ["SELECT COUNT(*) as val FROM vehicles WHERE status = 'available'", []],
            'totalDrivers'    => ["SELECT COUNT(*) as val FROM drivers WHERE status = 'active'", []],
        ];

        foreach ($queries as $key => [$sql, $params]) {
            try {
                $row = $db->fetchOne($sql, $params);
                $stats[$key] = $row['val'] ?? 0;
            } catch (\Exception $e) {
                // Table might not exist yet — keep default 0
            }
        }

        return $stats;
    }

    /**
     * Get upcoming transfers for today and beyond
     */
    public static function getUpcomingTransfers(int $limit = 5): array
    {
        $today = date('Y-m-d');

        try {
            return Database::getInstance()->fetchAll(
                "SELECT id, voucher_no, company_name, pickup_date, pickup_time,
                        pickup_location, dropoff_location, status
                 FROM vouchers
                 WHERE pickup_date >= ?
                 ORDER BY pickup_date ASC, pickup_time ASC
                 LIMIT ?",
                [$today, $limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get revenue grouped by currency for the current month
     */
    public static function getRevenuePerCurrency(): array
    {
        $monthStart = date('Y-m-01') . ' 00:00:00';
        try {
            return Database::fetchAll(
                "SELECT currency, COALESCE(SUM(total_amount), 0) as total, COUNT(*) as count
                 FROM invoices
                 WHERE created_at >= ? AND status = 'paid'
                 GROUP BY currency
                 ORDER BY total DESC",
                [$monthStart]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get monthly trend for the last 6 months
     */
    public static function getMonthlyTrend(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = date('Y-m-01', strtotime("-{$i} months"));
            $end = date('Y-m-t', strtotime("-{$i} months")) . ' 23:59:59';
            $label = date('M Y', strtotime($start));

            $vouchers = 0;
            $invoices = 0;
            $revenue = 0;

            try {
                $row = Database::fetchOne("SELECT COUNT(*) as c FROM vouchers WHERE created_at BETWEEN ? AND ?", [$start . ' 00:00:00', $end]);
                $vouchers = (int)($row['c'] ?? 0);
            } catch (\Exception $e) {}

            try {
                $row = Database::fetchOne("SELECT COUNT(*) as c FROM invoices WHERE created_at BETWEEN ? AND ?", [$start . ' 00:00:00', $end]);
                $invoices = (int)($row['c'] ?? 0);
            } catch (\Exception $e) {}

            try {
                $row = Database::fetchOne("SELECT COALESCE(SUM(total_amount), 0) as t FROM invoices WHERE created_at BETWEEN ? AND ? AND status = 'paid'", [$start . ' 00:00:00', $end]);
                $revenue = (float)($row['t'] ?? 0);
            } catch (\Exception $e) {}

            $months[] = [
                'label'    => $label,
                'vouchers' => $vouchers,
                'invoices' => $invoices,
                'revenue'  => $revenue,
            ];
        }
        return $months;
    }

    /**
     * Get top 5 partners by revenue
     */
    public static function getTopPartners(int $limit = 5): array
    {
        try {
            return Database::fetchAll(
                "SELECT company_name, COUNT(*) as invoice_count,
                        COALESCE(SUM(total_amount), 0) as total_revenue,
                        currency
                 FROM invoices
                 WHERE status = 'paid'
                 GROUP BY company_name, currency
                 ORDER BY total_revenue DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get payment status breakdown
     */
    public static function getPaymentBreakdown(): array
    {
        $result = ['paid' => 0, 'pending' => 0, 'overdue' => 0, 'draft' => 0];
        try {
            $rows = Database::fetchAll(
                "SELECT status, COUNT(*) as count FROM invoices GROUP BY status"
            );
            foreach ($rows as $row) {
                $result[$row['status']] = (int)$row['count'];
            }
        } catch (\Exception $e) {}
        return $result;
    }
}
