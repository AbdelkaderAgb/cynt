<?php
/**
 * CYN Tourism - API Dashboard Endpoint
 */

$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('-7 days'));
$monthStart = date('Y-m-01');

// Today's stats
$todayTransfers = Database::fetchOne(
    "SELECT COUNT(*) as count FROM vouchers WHERE pickup_date = ?",
    [$today]
)['count'];

$todayTours = Database::fetchOne(
    "SELECT COUNT(*) as count FROM city_tour_voucher_tours WHERE tour_date = ?",
    [$today]
)['count'];

// Weekly revenue
$weeklyRevenue = Database::fetchOne(
    "SELECT SUM(total_amount) as total FROM invoices WHERE created_at >= ? AND status = 'paid'",
    [$weekStart . ' 00:00:00']
)['total'] ?? 0;

// Monthly stats
$monthRevenue = Database::fetchOne(
    "SELECT SUM(total_amount) as total FROM invoices WHERE created_at >= ? AND status = 'paid'",
    [$monthStart . ' 00:00:00']
)['total'] ?? 0;

$monthVouchers = Database::fetchOne(
    "SELECT COUNT(*) as count FROM vouchers WHERE created_at >= ?",
    [$monthStart . ' 00:00:00']
)['count'];

// Pending invoices
$pendingInvoices = Database::fetchOne(
    "SELECT COUNT(*) as count FROM invoices WHERE status = 'pending'"
)['count'];

// Upcoming transfers
$upcomingTransfers = Database::fetchAll(
    "SELECT id, voucher_no, company_name, pickup_date, pickup_time, pickup_location, dropoff_location
     FROM vouchers WHERE pickup_date >= ? ORDER BY pickup_date ASC, pickup_time ASC LIMIT 5",
    [$today]
);

jsonResponse([
    'stats' => [
        'today_transfers' => (int)$todayTransfers,
        'today_tours' => (int)$todayTours,
        'weekly_revenue' => (float)$weeklyRevenue,
        'monthly_revenue' => (float)$monthRevenue,
        'month_vouchers' => (int)$monthVouchers,
        'pending_invoices' => (int)$pendingInvoices
    ],
    'upcoming_transfers' => $upcomingTransfers,
    'timestamp' => date('Y-m-d H:i:s')
]);
