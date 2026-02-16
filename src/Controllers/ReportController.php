<?php
/**
 * CYN Tourism — ReportController
 * Enhanced with revenue analytics
 */
class ReportController extends Controller
{
    public function index(): void
    {
        require_once ROOT_PATH . '/src/Models/Report.php';

        $startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-6 months'));
        $endDate   = $_GET['end_date']   ?? date('Y-m-d');

        // Wrap each report query in try-catch so the page loads even if some tables are missing
        $data = [
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'pageTitle'  => 'Reports & Analytics',
            'activePage' => 'reports',
        ];

        $reportMethods = [
            'monthlyRevenue'     => 'getMonthlyRevenue',
            'transferStats'      => 'getTransferStats',
            'transferTypes'      => 'getTransferTypes',
            'currencySummary'    => 'getCurrencySummary',
            'topCompanies'       => 'getTopCompanies',
            'partnerPerformance' => 'getPartnerPerformance',
            'revenueByPartner'   => 'getRevenueByPartner',
            'serviceBreakdown'   => 'getServiceTypeBreakdown',
            'invoiceSummary'     => 'getInvoiceSummary',
        ];

        foreach ($reportMethods as $key => $method) {
            try {
                $data[$key] = Report::$method($startDate, $endDate);
            } catch (\Exception $e) {
                $data[$key] = ($key === 'transferStats' || $key === 'invoiceSummary') ? [] : [];
            }
        }

        $this->view('reports/index', $data);
    }
}
