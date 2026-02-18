<?php
/**
 * CYN Tourism - Front Controller
 * 
 * Single entry point for all HTTP requests in the MVC application.
 * Apache mod_rewrite routes everything here via .htaccess.
 * 
 * @package CYN_Tourism
 * @version 3.0.0
 */

// Define the base path (same directory as index.php for public_html deployment)
define('BASE_PATH', __DIR__);
define('ROOT_PATH', BASE_PATH);

// Error reporting
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/php-errors.log');
error_reporting(E_ALL);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

// Load configuration
require_once BASE_PATH . '/config/config.php';

// Composer autoload (for Dompdf etc.)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Load the MVC core
require_once BASE_PATH . '/src/Core/App.php';
require_once BASE_PATH . '/src/Core/Controller.php';
require_once BASE_PATH . '/src/Core/Logger.php';

// Set base path for the App
App::setBasePath(BASE_PATH);

// Load database, auth, and helpers
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/src/Core/Auth.php';
require_once BASE_PATH . '/src/Core/helpers.php';
require_once BASE_PATH . '/config/language.php';

// ============================================
// Define Routes
// ============================================

// Home / Entry Point
App::get('/', ['AuthController', 'index']);

// Authentication
App::any('/login', ['AuthController', 'login']);
App::get('/logout', ['AuthController', 'logout']);

// Dashboard
App::get('/dashboard', ['DashboardController', 'index']);

// Vouchers
App::get('/vouchers', ['VoucherController', 'index']);
App::get('/vouchers/create', ['VoucherController', 'create']);
App::any('/vouchers/store', ['VoucherController', 'store']);
App::get('/vouchers/show', ['VoucherController', 'show']);
App::get('/vouchers/edit', ['VoucherController', 'edit']);
App::get('/vouchers/delete', ['VoucherController', 'delete']);

// Invoices
App::get('/invoices', ['InvoiceController', 'index']);
App::get('/invoices/create', ['InvoiceController', 'create']);
App::any('/invoices/store', ['InvoiceController', 'store']);
App::get('/invoices/show', ['InvoiceController', 'show']);
App::get('/invoices/edit', ['InvoiceController', 'edit']);
App::get('/invoices/delete', ['InvoiceController', 'delete']);
App::get('/invoices/mark-paid', ['InvoiceController', 'markPaid']);

// Partners
App::get('/partners', ['PartnerController', 'index']);
App::get('/partners/create', ['PartnerController', 'create']);
App::any('/partners/store', ['PartnerController', 'store']);
App::get('/partners/show', ['PartnerController', 'show']);
App::get('/partners/edit', ['PartnerController', 'edit']);
App::get('/partners/delete', ['PartnerController', 'delete']);
App::get('/api/partners/search', ['PartnerController', 'searchApi']);
App::get('/api/search-services', ['HotelController', 'searchServicesApi']);

// Fleet — Drivers
App::get('/drivers', ['FleetController', 'drivers']);
App::get('/drivers/form', ['FleetController', 'driverForm']);
App::any('/drivers/store', ['FleetController', 'driverStore']);
App::get('/drivers/delete', ['FleetController', 'driverDelete']);

// Fleet — Vehicles
App::get('/vehicles', ['FleetController', 'vehicles']);
App::get('/vehicles/form', ['FleetController', 'vehicleForm']);
App::any('/vehicles/store', ['FleetController', 'vehicleStore']);
App::get('/vehicles/delete', ['FleetController', 'vehicleDelete']);

// Fleet — Tour Guides
App::get('/guides', ['FleetController', 'guides']);
App::get('/guides/form', ['FleetController', 'guideForm']);
App::any('/guides/store', ['FleetController', 'guideStore']);
App::get('/guides/delete', ['FleetController', 'guideDelete']);

// Calendar
App::get('/calendar', ['CalendarController', 'index']);
App::get('/hotel-calendar', ['CalendarController', 'hotelCalendar']);

// Transfers
App::get('/transfers', ['TransferController', 'index']);
App::any('/transfers/store', ['TransferController', 'store']);
App::get('/transfer-invoice', ['TransferController', 'invoice']);
App::get('/transfer-invoice/create', ['TransferController', 'invoiceCreate']);
App::any('/transfer-invoice/store', ['TransferController', 'invoiceStore']);

// Hotels
App::get('/hotel-voucher', ['HotelController', 'voucher']);
App::get('/hotel-voucher/show', ['HotelController', 'voucherShow']);
App::get('/hotel-voucher/edit', ['HotelController', 'voucherEdit']);
App::any('/hotel-voucher/store', ['HotelController', 'voucherStore']);
App::any('/hotel-voucher/update', ['HotelController', 'voucherUpdate']);
App::get('/hotel-voucher/delete', ['HotelController', 'voucherDelete']);
App::get('/hotel-voucher/pdf', ['ExportController', 'hotelVoucherPdf']);
App::get('/hotel-invoice', ['HotelController', 'invoice']);
App::get('/hotel-invoice/create', ['HotelController', 'invoiceCreate']);
App::any('/hotel-invoice/store', ['HotelController', 'invoiceStore']);

// Tours
App::get('/tour-voucher', ['TourController', 'voucher']);
App::get('/tour-voucher/show', ['TourController', 'voucherShow']);
App::get('/tour-voucher/edit', ['TourController', 'voucherEdit']);
App::get('/tour-voucher/create', ['TourController', 'voucherCreate']);
App::any('/tour-voucher/store', ['TourController', 'voucherStore']);
App::any('/tour-voucher/update', ['TourController', 'voucherUpdate']);
App::get('/tour-voucher/delete', ['TourController', 'voucherDelete']);
App::get('/tour-voucher/pdf', ['ExportController', 'tourVoucherPdf']);
App::get('/tour-invoice', ['TourController', 'invoice']);
App::get('/tour-invoice/create', ['TourController', 'invoiceCreate']);
App::any('/tour-invoice/store', ['TourController', 'invoiceStore']);

// Missions
App::get('/missions', ['MissionController', 'index']);
App::get('/missions/create', ['MissionController', 'create']);
App::any('/missions/store', ['MissionController', 'store']);
App::get('/missions/show', ['MissionController', 'show']);
App::get('/missions/edit', ['MissionController', 'edit']);
App::any('/missions/update', ['MissionController', 'update']);
App::get('/missions/delete', ['MissionController', 'delete']);
App::get('/missions/calendar', ['MissionController', 'calendar']);
App::get('/missions/calendar-data', ['MissionController', 'calendarData']);
App::any('/missions/quick-create', ['MissionController', 'quickCreate']);

// Quotations
App::get('/quotations', ['QuotationController', 'index']);
App::get('/quotations/create', ['QuotationController', 'create']);
App::any('/quotations/store', ['QuotationController', 'store']);
App::get('/quotations/show', ['QuotationController', 'show']);
App::get('/quotations/edit', ['QuotationController', 'edit']);
App::any('/quotations/update', ['QuotationController', 'update']);
App::get('/quotations/delete', ['QuotationController', 'delete']);
App::get('/quotations/pdf', ['QuotationController', 'pdf']);
App::any('/quotations/convert', ['QuotationController', 'convert']);

// Group Files
App::get('/group-files', ['GroupFileController', 'index']);
App::get('/group-files/create', ['GroupFileController', 'create']);
App::any('/group-files/store', ['GroupFileController', 'store']);
App::get('/group-files/show', ['GroupFileController', 'show']);
App::get('/group-files/edit', ['GroupFileController', 'edit']);
App::any('/group-files/update', ['GroupFileController', 'update']);
App::get('/group-files/delete', ['GroupFileController', 'delete']);
App::get('/group-files/pdf', ['GroupFileController', 'pdf']);

// Board Pricing API
App::get('/api/rooms/board-prices', ['HotelProfileController', 'boardPricesApi']);
App::get('/api/hotels/list',        ['HotelProfileController', 'listApi']);
App::any('/api/rooms/board-prices/save', ['HotelProfileController', 'boardPricesSave']);

// Receipts
App::get('/receipts', ['ReceiptController', 'index']);
App::get('/receipts/show', ['ReceiptController', 'show']);
App::get('/receipts/edit', ['ReceiptController', 'edit']);
App::any('/receipts/update', ['ReceiptController', 'update']);
App::any('/receipts/mark-paid', ['ReceiptController', 'markPaid']);
App::get('/receipts/revert', ['ReceiptController', 'revert']);
App::get('/receipts/send-to-portal', ['ReceiptController', 'sendToPortal']);
App::get('/receipts/pdf', ['ExportController', 'receiptPdf']);

// Notifications
App::get('/notifications', ['NotificationController', 'index']);
App::get('/notifications/mark-read', ['NotificationController', 'markRead']);
App::get('/notifications/mark-all-read', ['NotificationController', 'markAllRead']);

// Reports
App::get('/reports', ['ReportController', 'index']);

// Users
App::get('/users', ['UserController', 'index']);
App::get('/users/create', ['UserController', 'create']);
App::any('/users/store', ['UserController', 'store']);
App::get('/users/edit', ['UserController', 'edit']);

// Profile
App::get('/profile', ['UserController', 'profile']);
App::any('/profile/update', ['UserController', 'updateProfile']);

// Settings
App::get('/settings', ['SettingsController', 'index']);
App::any('/settings/update', ['SettingsController', 'update']);
App::any('/settings/email', ['SettingsController', 'email']);

// Export / PDF / Share
App::get('/invoices/pdf', ['ExportController', 'invoicePdf']);
App::get('/invoices/send-to-portal', ['InvoiceController', 'sendToPortal']);
App::get('/vouchers/pdf', ['ExportController', 'voucherPdf']);
App::any('/export/email', ['ExportController', 'sendEmail']);
App::get('/export/whatsapp', ['ExportController', 'whatsappShare']);

// ============================================
// Partner Portal Routes
// ============================================
App::any('/portal/login', ['PortalController', 'login']);
App::get('/portal/logout', ['PortalController', 'logout']);
App::get('/portal/dashboard', ['PortalController', 'dashboard']);
App::get('/portal/invoices', ['PortalController', 'invoices']);
App::get('/portal/invoices/view', ['PortalController', 'invoiceView']);
App::get('/portal/vouchers', ['PortalController', 'vouchers']);
App::get('/portal/vouchers/view', ['PortalController', 'voucherView']);
App::get('/portal/bookings', ['PortalController', 'bookingRequests']);
App::get('/portal/booking/create', ['PortalController', 'bookingRequest']);
App::any('/portal/booking/store', ['PortalController', 'bookingRequestStore']);
App::get('/portal/messages', ['PortalController', 'messages']);
App::any('/portal/messages/send', ['PortalController', 'messageSend']);
App::get('/portal/profile', ['PortalController', 'profile']);
App::any('/portal/profile/update', ['PortalController', 'profileUpdate']);
App::get('/portal/receipts', ['PortalController', 'receipts']);
App::get('/portal/receipts/view', ['PortalController', 'receiptView']);

// Admin: Partner Booking Requests & Messages
App::get('/partner-requests', ['PartnerController', 'bookingRequests']);
App::any('/partner-requests/action', ['PartnerController', 'bookingRequestAction']);
App::get('/partner-messages', ['PartnerController', 'partnerMessages']);
App::any('/partner-messages/reply', ['PartnerController', 'messageReply']);

// Services & Pricing
App::get('/services', ['ServiceController', 'index']);
App::get('/services/create', ['ServiceController', 'create']);
App::get('/services/edit', ['ServiceController', 'edit']);
App::any('/services/store', ['ServiceController', 'store']);
App::any('/services/delete', ['ServiceController', 'delete']);
App::get('/api/services/search', ['ServiceController', 'searchApi']);
App::any('/services/import-tours', ['ServiceController', 'importTours']);
App::any('/services/import-transfers', ['ServiceController', 'importTransfers']);

// Hotel Profiles & Room Pricing
App::get('/hotels/profiles', ['HotelProfileController', 'index']);
App::get('/hotels/profiles/create', ['HotelProfileController', 'create']);
App::get('/hotels/profiles/edit', ['HotelProfileController', 'edit']);
App::any('/hotels/profiles/store', ['HotelProfileController', 'store']);
App::any('/hotels/profiles/delete', ['HotelProfileController', 'delete']);
App::any('/hotels/profiles/import', ['HotelProfileController', 'importXlsx']);

// ============================================
// Phase 2 — Core Tourism Operations
// ============================================

// Seasonal Pricing
App::get('/hotels/seasons', ['SeasonalPricingController', 'index']);
App::any('/hotels/seasons/store', ['SeasonalPricingController', 'store']);
App::get('/hotels/seasons/delete', ['SeasonalPricingController', 'delete']);
App::get('/api/seasons/rates', ['SeasonalPricingController', 'ratesApi']);
App::get('/api/seasons/check', ['SeasonalPricingController', 'checkApi']);

// Room Allotments
App::get('/hotels/allotments', ['AllotmentController', 'index']);
App::any('/hotels/allotments/store', ['AllotmentController', 'store']);
App::get('/hotels/allotments/delete', ['AllotmentController', 'delete']);
App::get('/api/allotments/check', ['AllotmentController', 'checkApi']);

// Rooming List
App::get('/hotels/rooming-list', ['RoomingListController', 'index']);
App::any('/hotels/rooming-list/store', ['RoomingListController', 'store']);
App::get('/hotels/rooming-list/export', ['RoomingListController', 'export']);

// ============================================
// Phase 3 — Financial & Quotation System
// ============================================

// Credit Notes
App::get('/credit-notes', ['CreditNoteController', 'index']);
App::get('/credit-notes/create', ['CreditNoteController', 'create']);
App::any('/credit-notes/store', ['CreditNoteController', 'store']);
App::get('/credit-notes/delete', ['CreditNoteController', 'delete']);

// Tax Rates
App::get('/settings/tax-rates', ['TaxController', 'index']);
App::any('/settings/tax-rates/store', ['TaxController', 'store']);
App::get('/settings/tax-rates/delete', ['TaxController', 'delete']);
App::get('/api/tax/default', ['TaxController', 'defaultApi']);

// Partner Statement of Account
App::get('/partners/statement', ['PartnerController', 'statement']);

// ============================================
// Run the Application
// ============================================
App::run();
