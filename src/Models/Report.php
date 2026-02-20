<?php
/**
 * CYN Tourism — Report Model
 * Fixed for MySQL (DATE_FORMAT)
 * Enhanced with revenue analytics
 */
class Report
{
    /**
     * Monthly revenue from invoices (SQLite-compatible)
     */
    public static function getMonthlyRevenue(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT strftime('%Y-%m', invoice_date) as month,
                    SUM(total_amount) as revenue, COUNT(*) as count
             FROM invoices
             WHERE invoice_date BETWEEN ? AND ? AND status IN ('paid','partial')
             GROUP BY month ORDER BY month",
            [$startDate, $endDate]
        );
    }

    /**
     * Transfer statistics
     */
    public static function getTransferStats(string $startDate, string $endDate): array
    {
        return Database::fetchOne(
            "SELECT COUNT(*) as total_transfers,
                    COALESCE(SUM(total_pax),0) as total_passengers,
                    COUNT(DISTINCT company_name) as unique_companies,
                    COUNT(DISTINCT hotel_name) as unique_hotels
             FROM vouchers WHERE pickup_date BETWEEN ? AND ?",
            [$startDate, $endDate]
        ) ?: [];
    }

    /**
     * Transfer types breakdown
     */
    public static function getTransferTypes(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT transfer_type, COUNT(*) as count FROM vouchers
             WHERE pickup_date BETWEEN ? AND ? GROUP BY transfer_type",
            [$startDate, $endDate]
        );
    }

    /**
     * Currency summary
     */
    public static function getCurrencySummary(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT currency, COUNT(*) as count, SUM(total_amount) as total
             FROM invoices WHERE invoice_date BETWEEN ? AND ?
             GROUP BY currency ORDER BY total DESC",
            [$startDate, $endDate]
        );
    }

    /**
     * Top companies by voucher count
     */
    public static function getTopCompanies(string $startDate, string $endDate, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT company_name, COUNT(*) as voucher_count, COALESCE(SUM(total_pax),0) as total_pax
             FROM vouchers WHERE pickup_date BETWEEN ? AND ?
             GROUP BY company_name ORDER BY voucher_count DESC LIMIT $limit",
            [$startDate, $endDate]
        );
    }

    /**
     * Partner performance
     */
    public static function getPartnerPerformance(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT p.company_name, COUNT(v.id) as voucher_count, COALESCE(SUM(v.total_pax),0) as total_pax
             FROM partners p LEFT JOIN vouchers v ON p.company_name = v.company_name
                AND v.pickup_date BETWEEN ? AND ?
             WHERE p.status = 'active' GROUP BY p.company_name
             ORDER BY voucher_count DESC LIMIT 20",
            [$startDate, $endDate]
        );
    }

    // =======================================
    // NEW: Revenue Analytics
    // =======================================

    /**
     * Revenue by partner — Top partners ranked by total invoice amount
     */
    public static function getRevenueByPartner(string $startDate, string $endDate, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT company_name, currency,
                    COUNT(*) as invoice_count,
                    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_total,
                    SUM(CASE WHEN status != 'paid' THEN total_amount ELSE 0 END) as unpaid_total,
                    SUM(total_amount) as grand_total
             FROM invoices
             WHERE invoice_date BETWEEN ? AND ?
             GROUP BY company_name, currency
             ORDER BY grand_total DESC
             LIMIT $limit",
            [$startDate, $endDate]
        );
    }

    /**
     * Service type breakdown — counts of transfers, hotel vouchers, tour vouchers
     */
    public static function getServiceTypeBreakdown(string $startDate, string $endDate): array
    {
        $result = ['transfer' => 0, 'hotel' => 0, 'tour' => 0];

        try {
            $row = Database::fetchOne("SELECT COUNT(*) as c FROM vouchers WHERE pickup_date BETWEEN ? AND ?", [$startDate, $endDate]);
            $result['transfer'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {}

        try {
            $row = Database::fetchOne("SELECT COUNT(*) as c FROM hotel_vouchers WHERE created_at BETWEEN ? AND ?", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $result['hotel'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {}

        try {
            $row = Database::fetchOne("SELECT COUNT(*) as c FROM tours WHERE created_at BETWEEN ? AND ?", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $result['tour'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {}

        return $result;
    }

    /**
     * Invoice summary — total paid, unpaid, overdue amounts
     */
    public static function getInvoiceSummary(string $startDate, string $endDate): array
    {
        $summary = ['paid_amount' => 0, 'unpaid_amount' => 0, 'overdue_amount' => 0, 'total_amount' => 0, 'invoice_count' => 0];
        try {
            $row = Database::fetchOne(
                "SELECT COUNT(*) as cnt,
                        COALESCE(SUM(total_amount), 0) as total,
                        COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) as paid,
                        COALESCE(SUM(CASE WHEN status IN ('pending','draft') THEN total_amount ELSE 0 END), 0) as unpaid,
                        COALESCE(SUM(CASE WHEN status = 'overdue' OR (status = 'pending' AND due_date < date('now')) THEN total_amount ELSE 0 END), 0) as overdue
                 FROM invoices WHERE invoice_date BETWEEN ? AND ?",
                [$startDate, $endDate]
            );
            $summary['paid_amount'] = (float)($row['paid'] ?? 0);
            $summary['unpaid_amount'] = (float)($row['unpaid'] ?? 0);
            $summary['overdue_amount'] = (float)($row['overdue'] ?? 0);
            $summary['total_amount'] = (float)($row['total'] ?? 0);
            $summary['invoice_count'] = (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {}

        return $summary;
    }

    // =======================================
    // Profitability Analytics
    // =======================================

    /**
     * Profitability: revenue vs cost by service type
     * Uses cost_price / selling_price columns on hotel_vouchers and tours
     */
    public static function getProfitability(string $startDate, string $endDate): array
    {
        $result = [
            'byService' => [],
            'byMonth'   => [],
            'totals'    => ['revenue' => 0, 'cost' => 0, 'profit' => 0, 'margin' => 0],
        ];

        // Hotel profitability
        try {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(selling_price), 0) as revenue, COALESCE(SUM(cost_price), 0) as cost
                 FROM hotel_vouchers 
                 WHERE created_at BETWEEN ? AND ?",
                [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
            );
            $rev = (float)($row['revenue'] ?? 0);
            $cost = (float)($row['cost'] ?? 0);
            $result['byService'][] = [
                'service' => 'Hotel',
                'revenue' => $rev,
                'cost'    => $cost,
                'profit'  => $rev - $cost,
                'margin'  => $rev > 0 ? round(($rev - $cost) / $rev * 100, 1) : 0,
            ];
        } catch (\Exception $e) {}

        // Tour profitability
        try {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(selling_price), 0) as revenue, COALESCE(SUM(cost_price), 0) as cost
                 FROM tours 
                 WHERE created_at BETWEEN ? AND ?",
                [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
            );
            $rev = (float)($row['revenue'] ?? 0);
            $cost = (float)($row['cost'] ?? 0);
            $result['byService'][] = [
                'service' => 'Tour',
                'revenue' => $rev,
                'cost'    => $cost,
                'profit'  => $rev - $cost,
                'margin'  => $rev > 0 ? round(($rev - $cost) / $rev * 100, 1) : 0,
            ];
        } catch (\Exception $e) {}

        // Transfer profitability (from invoices)
        try {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(total_amount), 0) as revenue
                 FROM invoices 
                 WHERE invoice_date BETWEEN ? AND ?",
                [$startDate, $endDate]
            );
            $rev = (float)($row['revenue'] ?? 0);
            $result['byService'][] = [
                'service' => 'Invoiced Services',
                'revenue' => $rev,
                'cost'    => 0,
                'profit'  => $rev,
                'margin'  => 100,
            ];
        } catch (\Exception $e) {}

        // Monthly profitability (hotel + tour combined)
        try {
            $months = Database::fetchAll(
                "SELECT strftime('%Y-%m', created_at) as month,
                        COALESCE(SUM(selling_price), 0) as revenue,
                        COALESCE(SUM(cost_price), 0) as cost
                 FROM hotel_vouchers
                 WHERE created_at BETWEEN ? AND ?
                 GROUP BY month
                 ORDER BY month",
                [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
            );
            foreach ($months as $m) {
                $result['byMonth'][$m['month']] = [
                    'revenue' => (float)$m['revenue'],
                    'cost'    => (float)$m['cost'],
                    'profit'  => (float)$m['revenue'] - (float)$m['cost'],
                ];
            }
        } catch (\Exception $e) {}

        // Totals
        foreach ($result['byService'] as $s) {
            $result['totals']['revenue'] += $s['revenue'];
            $result['totals']['cost'] += $s['cost'];
        }
        $result['totals']['profit'] = $result['totals']['revenue'] - $result['totals']['cost'];
        $result['totals']['margin'] = $result['totals']['revenue'] > 0
            ? round($result['totals']['profit'] / $result['totals']['revenue'] * 100, 1)
            : 0;

        return $result;
    }
}