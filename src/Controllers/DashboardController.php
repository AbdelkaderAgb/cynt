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

        // Check if company identity settings are complete
        $companySettings = Database::fetchAll(
            "SELECT setting_key, setting_value FROM settings WHERE setting_group = 'company' AND setting_key IN ('company_name','company_address','company_phone','company_email')"
        );
        $companyComplete = true;
        foreach ($companySettings as $s) {
            if (empty(trim($s['setting_value'] ?? ''))) {
                $companyComplete = false;
                break;
            }
        }
        if (count($companySettings) < 4) $companyComplete = false;

        $this->view('dashboard/index', [
            'stats'             => $stats,
            'upcomingTransfers' => $upcomingTransfers,
            'currencyRevenue'   => $currencyRevenue,
            'monthlyTrend'      => $monthlyTrend,
            'topPartners'       => $topPartners,
            'paymentBreakdown'  => $paymentBreakdown,
            'companyComplete'   => $companyComplete,
            'pageTitle'         => __('dashboard'),
            'activePage'        => 'dashboard',
            'user'              => $this->user(),
        ]);
    }
}
