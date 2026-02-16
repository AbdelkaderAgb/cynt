<?php
/**
 * CYN Tourism — DashboardController
 * 
 * Passes full statistics data to the dashboard view.
 * 
 * @package CYN_Tourism
 * @version 4.0.0
 */

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(): void
    {
        $this->requireAuth();

        require_once ROOT_PATH . '/src/Models/Dashboard.php';

        $stats             = Dashboard::getStats();
        $upcomingTransfers = Dashboard::getUpcomingTransfers();
        $currencyRevenue   = Dashboard::getRevenuePerCurrency();
        $monthlyTrend      = Dashboard::getMonthlyTrend();
        $topPartners       = Dashboard::getTopPartners();
        $paymentBreakdown  = Dashboard::getPaymentBreakdown();

        $this->view('dashboard/index', [
            'stats'             => $stats,
            'upcomingTransfers' => $upcomingTransfers,
            'currencyRevenue'   => $currencyRevenue,
            'monthlyTrend'      => $monthlyTrend,
            'topPartners'       => $topPartners,
            'paymentBreakdown'  => $paymentBreakdown,
            'pageTitle'         => __('dashboard'),
            'activePage'        => 'dashboard',
            'user'              => $this->user(),
        ]);
    }
}
