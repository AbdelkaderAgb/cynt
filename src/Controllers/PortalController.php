<?php
/**
 * CYN Tourism — Portal Controller
 * 
 * Handles all partner portal routes: login, dashboard, invoices,
 * vouchers, booking requests, messages, profile, and receipts.
 */
class PortalController extends Controller
{
    /**
     * Require partner authentication (redirects to portal login)
     */
    private function requirePartnerAuth(): array
    {
        Auth::requirePartner();
        return Auth::partner();
    }

    /**
     * Render a portal view inside the portal layout
     */
    private function portalView(string $view, array $data = []): void
    {
        if (!isset($data['partner'])) {
            $data['partner'] = Auth::partner();
        }
        $this->view('portal/' . $view, $data, 'layouts/portal');
    }

    // ========================================
    // Authentication
    // ========================================

    public function login(): void
    {
        // Already logged in?
        if (Auth::checkPartner()) {
            header('Location: ' . url('portal/dashboard'));
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $result = Auth::loginPartner($email, $password);
            if ($result['success']) {
                header('Location: ' . url('portal/dashboard'));
                exit;
            }
            $error = $result['message'];
        }

        // Login page is standalone (no layout)
        $this->viewStandalone('portal/login', ['error' => $error]);
    }

    public function logout(): void
    {
        Auth::logoutPartner();
        header('Location: ' . url('portal/login'));
        exit;
    }

    // ========================================
    // Dashboard
    // ========================================

    public function dashboard(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';

        // Stats
        $invoiceCount = 0;
        $pendingInvoices = 0;
        $voucherCount = 0;
        $pendingRequests = 0;
        $unreadMessages = 0;
        $recentInvoices = [];

        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE company_id = ? OR company_name = ?");
            $stmt->execute([$pid, $companyName]);
            $invoiceCount = (int)$stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE (company_id = ? OR company_name = ?) AND status IN ('sent','draft')");
            $stmt->execute([$pid, $companyName]);
            $pendingInvoices = (int)$stmt->fetchColumn();

            // Count all voucher types
            $stmt = $db->prepare("SELECT COUNT(*) FROM vouchers WHERE company_id = ? OR company_name = ?");
            $stmt->execute([$pid, $companyName]);
            $transferCount = (int)$stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COUNT(*) FROM hotel_vouchers WHERE company_id = ? OR company_name = ?");
            $stmt->execute([$pid, $companyName]);
            $hotelCount = (int)$stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COUNT(*) FROM tours WHERE company_id = ? OR company_name = ?");
            $stmt->execute([$pid, $companyName]);
            $tourCount = (int)$stmt->fetchColumn();

            $voucherCount = $transferCount + $hotelCount + $tourCount;

            $stmt = $db->prepare("SELECT COUNT(*) FROM partner_booking_requests WHERE partner_id = ? AND status = 'pending'");
            $stmt->execute([$pid]);
            $pendingRequests = (int)$stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COUNT(*) FROM partner_messages WHERE partner_id = ? AND sender_type = 'admin' AND is_read = 0");
            $stmt->execute([$pid]);
            $unreadMessages = (int)$stmt->fetchColumn();

            // Recent invoices
            $stmt = $db->prepare("SELECT id, invoice_no, invoice_date, total_amount, currency, status FROM invoices WHERE company_id = ? OR company_name = ? ORDER BY invoice_date DESC LIMIT 5");
            $stmt->execute([$pid, $companyName]);
            $recentInvoices = $stmt->fetchAll();
        } catch (Exception $e) {
            // Continue with defaults
        }

        $this->portalView('dashboard', [
            'partner'         => $partner,
            'invoiceCount'    => $invoiceCount,
            'pendingInvoices' => $pendingInvoices,
            'voucherCount'    => $voucherCount,
            'pendingRequests' => $pendingRequests,
            'unreadMessages'  => $unreadMessages,
            'recentInvoices'  => $recentInvoices,
            'pageTitle'       => 'Dashboard',
            'activePage'      => 'portal-dashboard',
        ]);
    }

    // ========================================
    // Invoices
    // ========================================

    public function invoices(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';

        $where = "(company_id = ? OR company_name = ?)";
        $params = [$pid, $companyName];

        if ($search) {
            $where .= " AND (invoice_no LIKE ? OR company_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM invoices WHERE $where");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM invoices WHERE $where ORDER BY invoice_date DESC");
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        $this->portalView('invoices', [
            'partner'    => $partner,
            'invoices'   => $invoices,
            'total'      => $total,
            'search'     => $search,
            'status'     => $status,
            'page'       => 1,
            'pages'      => 1,
            'pageTitle'  => 'My Invoices',
            'activePage' => 'portal-invoices',
        ]);
    }

    public function invoiceView(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND (company_id = ? OR company_name = ?)");
        $stmt->execute([$id, $pid, $companyName]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            header('Location: ' . url('portal/invoices'));
            exit;
        }

        // Get invoice items
        $items = [];
        try {
            $stmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
        } catch (Exception $e) {}

        $this->portalView('invoice_detail', [
            'partner'    => $partner,
            'invoice'    => $invoice,
            'items'      => $items,
            'pageTitle'  => 'Invoice: ' . $invoice['invoice_no'],
            'activePage' => 'portal-invoices',
        ]);
    }

    // ========================================
    // Vouchers (Transfer + Hotel + Tour)
    // ========================================

    public function vouchers(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';

        $search = trim($_GET['search'] ?? '');
        $type = $_GET['type'] ?? '';

        $vouchers = [];

        try {
            // Transfer vouchers
            if (!$type || $type === 'transfer') {
                $sql = "SELECT id, voucher_no, company_name, pickup_location, dropoff_location, 
                               pickup_date, total_pax, price AS total_price, currency, status,
                               'transfer' AS voucher_type
                        FROM vouchers WHERE (company_id = ? OR company_name = ?)";
                $params = [$pid, $companyName];
                if ($search) {
                    $sql .= " AND (voucher_no LIKE ? OR pickup_location LIKE ? OR dropoff_location LIKE ?)";
                    $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
                }
                $sql .= " ORDER BY pickup_date DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $vouchers = array_merge($vouchers, $stmt->fetchAll());
            }

            // Hotel vouchers
            if (!$type || $type === 'hotel') {
                $sql = "SELECT id, voucher_no, guest_name, hotel_name, check_in, check_out, 
                               total_pax, total_price, currency, status,
                               'hotel' AS voucher_type
                        FROM hotel_vouchers WHERE (company_id = ? OR company_name = ?)";
                $params = [$pid, $companyName];
                if ($search) {
                    $sql .= " AND (voucher_no LIKE ? OR guest_name LIKE ? OR hotel_name LIKE ?)";
                    $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
                }
                $sql .= " ORDER BY check_in DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $vouchers = array_merge($vouchers, $stmt->fetchAll());
            }

            // Tour vouchers
            if (!$type || $type === 'tour') {
                $sql = "SELECT id, tour_code AS voucher_no, tour_name, company_name, tour_date, 
                               total_pax, total_price, currency, status,
                               'tour' AS voucher_type
                        FROM tours WHERE (company_id = ? OR company_name = ?)";
                $params = [$pid, $companyName];
                if ($search) {
                    $sql .= " AND (tour_code LIKE ? OR tour_name LIKE ? OR destination LIKE ?)";
                    $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
                }
                $sql .= " ORDER BY tour_date DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $vouchers = array_merge($vouchers, $stmt->fetchAll());
            }
        } catch (Exception $e) {}

        $total = count($vouchers);

        $this->portalView('vouchers', [
            'partner'    => $partner,
            'vouchers'   => $vouchers,
            'total'      => $total,
            'search'     => $search,
            'type'       => $type,
            'pageTitle'  => 'My Vouchers',
            'activePage' => 'portal-vouchers',
        ]);
    }

    public function voucherView(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? 'transfer';

        $voucher = null;

        try {
            if ($type === 'hotel') {
                $stmt = $db->prepare("SELECT *, 'hotel' AS voucher_type FROM hotel_vouchers WHERE id = ? AND (company_id = ? OR company_name = ?)");
                $stmt->execute([$id, $pid, $companyName]);
                $voucher = $stmt->fetch();
            } elseif ($type === 'tour') {
                $stmt = $db->prepare("SELECT *, 'tour' AS voucher_type FROM tours WHERE id = ? AND (company_id = ? OR company_name = ?)");
                $stmt->execute([$id, $pid, $companyName]);
                $voucher = $stmt->fetch();
            } else {
                $stmt = $db->prepare("SELECT *, 'transfer' AS voucher_type FROM vouchers WHERE id = ? AND (company_id = ? OR company_name = ?)");
                $stmt->execute([$id, $pid, $companyName]);
                $voucher = $stmt->fetch();
            }
        } catch (Exception $e) {}

        if (!$voucher) {
            header('Location: ' . url('portal/vouchers'));
            exit;
        }

        $this->portalView('voucher_detail', [
            'partner'    => $partner,
            'voucher'    => $voucher,
            'pageTitle'  => 'Voucher Details',
            'activePage' => 'portal-vouchers',
        ]);
    }

    // ========================================
    // Booking Requests
    // ========================================

    public function bookingRequests(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];

        $stmt = $db->prepare("SELECT * FROM partner_booking_requests WHERE partner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$pid]);
        $requests = $stmt->fetchAll();

        $this->portalView('bookings', [
            'partner'    => $partner,
            'requests'   => $requests,
            'pageTitle'  => 'Booking Requests',
            'activePage' => 'portal-bookings',
        ]);
    }

    public function bookingRequest(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();

        // Get available services
        $services = [];
        try {
            $stmt = $db->query("SELECT id, service_type, name, description, price, currency, unit FROM services WHERE status = 'active' ORDER BY service_type, name");
            $services = $stmt->fetchAll();
        } catch (Exception $e) {}

        // Get hotel list with city for filtering
        $hotels = [];
        try {
            $stmt = $db->query("SELECT id, name, city, stars FROM hotels WHERE status = 'active' ORDER BY city, name");
            $hotels = $stmt->fetchAll();
        } catch (Exception $e) {}

        // Get hotel rooms with pricing (grouped by hotel)
        $hotelRooms = [];
        try {
            $stmt = $db->query("SELECT hr.*, h.name as hotel_name, h.city as hotel_city 
                                FROM hotel_rooms hr 
                                JOIN hotels h ON hr.hotel_id = h.id 
                                WHERE h.status = 'active' 
                                ORDER BY h.name, hr.room_type");
            $hotelRooms = $stmt->fetchAll();
        } catch (Exception $e) {}

        // Get unique cities for filter
        $cities = [];
        try {
            $stmt = $db->query("SELECT DISTINCT city FROM hotels WHERE status = 'active' AND city != '' ORDER BY city");
            $cities = array_column($stmt->fetchAll(), 'city');
        } catch (Exception $e) {}

        $this->portalView('booking_form', [
            'partner'    => $partner,
            'services'   => $services,
            'hotels'     => $hotels,
            'hotelRooms' => $hotelRooms,
            'cities'     => $cities,
            'pageTitle'  => 'New Booking',
            'activePage' => 'portal-bookings',
        ]);
    }

    /**
     * Validate (adults + children) <= room capacity for portal hotel booking.
     * Returns error message or null if valid / capacity unknown.
     */
    private function validateHotelBookingCapacity(PDO $db, int $adults, int $children, int $roomCount, string $roomType, int $hotelIdFromPost, string $hotelName): ?string
    {
        $hotelId = $hotelIdFromPost > 0 ? $hotelIdFromPost : $this->resolveHotelIdByName($db, $hotelName);
        $capacity = $this->getRoomCapacityForPortal($db, $hotelId, $roomType);
        if ($capacity === null) return null;
        $maxGuests = $capacity * max(1, $roomCount);
        $occupancy = $adults + $children;
        if ($occupancy > $maxGuests) {
            return sprintf('Adults + children (%d) exceed room capacity (%d for %d room(s)).', $occupancy, $capacity, max(1, $roomCount));
        }
        return null;
    }

    private function resolveHotelIdByName(PDO $db, string $name): int
    {
        $name = trim($name);
        if ($name === '') return 0;
        $stmt = $db->prepare("SELECT id FROM hotels WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : 0;
    }

    /**
     * Get room capacity. If roomType is numeric (portal sends room index), resolve room by hotel_id and index.
     */
    private function getRoomCapacityForPortal(PDO $db, int $hotelId, string $roomType): ?int
    {
        $roomType = trim($roomType);
        if ($roomType === '') return null;
        if ($hotelId > 0 && is_numeric($roomType)) {
            $stmt = $db->prepare("SELECT capacity FROM hotel_rooms WHERE hotel_id = ? ORDER BY id ASC LIMIT 1 OFFSET ?");
            $stmt->execute([$hotelId, (int)$roomType]);
        } elseif ($hotelId > 0) {
            $stmt = $db->prepare("SELECT capacity FROM hotel_rooms WHERE hotel_id = ? AND room_type = ? LIMIT 1");
            $stmt->execute([$hotelId, $roomType]);
        } else {
            $stmt = $db->prepare("SELECT capacity FROM hotel_rooms WHERE room_type = ? LIMIT 1");
            $stmt->execute([$roomType]);
        }
        $row = $stmt->fetch();
        return $row !== false ? (int)$row['capacity'] : null;
    }

    public function bookingRequestStore(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();

        $requestType = $_POST['request_type'] ?? 'transfer';

        $details = [
            'company_name'    => $partner['company_name'] ?? '',
            'guest_name'      => trim($_POST['guest_name'] ?? ''),
            'date'            => $_POST['date'] ?? '',
            'pickup_location' => trim($_POST['pickup_location'] ?? ''),
            'destination'     => trim($_POST['destination'] ?? ''),
            'hotel_name'      => trim($_POST['hotel_name'] ?? ''),
            'tour_name'       => trim($_POST['tour_name'] ?? ''),
            'pax'             => (int)($_POST['pax'] ?? 1),
            'notes'           => trim($_POST['notes'] ?? ''),
            'service_id'      => (int)($_POST['service_id'] ?? 0),
            'service_name'    => trim($_POST['service_name'] ?? ''),
            'service_price'   => trim($_POST['service_price'] ?? ''),
        ];

        // Hotel-specific fields + capacity validation
        if ($requestType === 'hotel') {
            $details['check_in']    = $_POST['check_in'] ?? '';
            $details['check_out']   = $_POST['check_out'] ?? '';
            $details['room_type']   = trim($_POST['room_type'] ?? '');
            $details['board_type']  = $_POST['board_type'] ?? '';
            $details['room_count']  = (int)($_POST['room_count'] ?? 1);
            $details['adults']      = (int)($_POST['adults'] ?? 1);
            $details['children']    = (int)($_POST['children'] ?? 0);
            if (empty($details['date']) && !empty($details['check_in'])) {
                $details['date'] = $details['check_in'];
            }
            $hotelId = (int)($_POST['hotel_id'] ?? 0);
            if ($hotelId === 0 && isset($_POST['hotel_name']) && is_numeric($_POST['hotel_name'])) {
                $hotelId = (int)$_POST['hotel_name'];
            }
            $capacityError = $this->validateHotelBookingCapacity($db, $details['adults'], $details['children'], $details['room_count'], $details['room_type'], $hotelId, $details['hotel_name']);
            if ($capacityError !== null) {
                header('Location: ' . url('portal/booking') . '?error=capacity&message=' . urlencode($capacityError));
                exit;
            }
        }

        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM partner_booking_requests")->fetchColumn();
        $stmt = $db->prepare("INSERT INTO partner_booking_requests (id, partner_id, request_type, details, status, created_at) VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)");
        $stmt->execute([
            $nextId,
            (int)$partner['id'],
            $requestType,
            json_encode($details, JSON_UNESCAPED_UNICODE),
        ]);

        header('Location: ' . url('portal/bookings') . '?saved=1');
        exit;
    }

    // ========================================
    // Messages
    // ========================================

    public function messages(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];

        $stmt = $db->prepare("SELECT * FROM partner_messages WHERE partner_id = ? ORDER BY created_at ASC");
        $stmt->execute([$pid]);
        $messages = $stmt->fetchAll();

        // Mark admin messages as read
        $db->prepare("UPDATE partner_messages SET is_read = 1 WHERE partner_id = ? AND sender_type = 'admin'")->execute([$pid]);

        $this->portalView('messages', [
            'partner'    => $partner,
            'messages'   => $messages,
            'pageTitle'  => 'Messages',
            'activePage' => 'portal-messages',
        ]);
    }

    public function messageSend(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];

        $message = trim($_POST['message'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $filePath = '';  // Must be '' not null — file_path column is NOT NULL

        // Handle file upload
        if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/uploads/messages/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','zip'];
            if (in_array($ext, $allowed)) {
                $fileName = 'partner_' . $pid . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                    $filePath = url('uploads/messages/' . $fileName);
                }
            }
        }

        if ($message || $filePath) {
            // Use explicit next ID to work even without AUTO_INCREMENT
            $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM partner_messages")->fetchColumn();
            $stmt = $db->prepare("INSERT INTO partner_messages (id, partner_id, sender_type, sender_id, subject, message, file_path) VALUES (?, ?, 'partner', ?, ?, ?, ?)");
            $stmt->execute([$nextId, $pid, $pid, $subject, $message ?: '📎 File attachment', $filePath ?: '']);
        }

        header('Location: ' . url('portal/messages'));
        exit;
    }

    // ========================================
    // Profile
    // ========================================

    public function profile(): void
    {
        $partner = $this->requirePartnerAuth();

        $this->portalView('profile', [
            'partner'    => $partner,
            'pageTitle'  => 'My Profile',
            'activePage' => 'portal-profile',
        ]);
    }

    public function profileUpdate(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];

        $fields = [
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'mobile'         => trim($_POST['mobile'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'country'        => trim($_POST['country'] ?? ''),
        ];

        $sets = [];
        $params = [];
        foreach ($fields as $col => $val) {
            $sets[] = "$col = ?";
            $params[] = $val;
        }

        // Optional password change
        $newPass = trim($_POST['new_password'] ?? '');
        if ($newPass && strlen($newPass) >= 6) {
            $sets[] = "password = ?";
            $params[] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $params[] = $pid;
        $stmt = $db->prepare("UPDATE partners SET " . implode(', ', $sets) . " WHERE id = ?");
        $stmt->execute($params);

        header('Location: ' . url('portal/profile') . '?updated=1');
        exit;
    }

    // ========================================
    // Receipts
    // ========================================

    public function receipts(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';

        $receipts = [];
        $total = 0;

        try {
            // Receipts are paid invoices — no separate 'receipts' table exists
            $stmt = $db->prepare(
                "SELECT * FROM invoices
                 WHERE status = 'paid' AND (partner_id = ? OR company_id = ? OR company_name = ?)
                 ORDER BY payment_date DESC, created_at DESC"
            );
            $stmt->execute([$pid, $pid, $companyName]);
            $receipts = $stmt->fetchAll();
            $total = count($receipts);
        } catch (Exception $e) {
            // Silently handle
        }

        $this->portalView('receipts', [
            'partner'    => $partner,
            'receipts'   => $receipts,
            'total'      => $total,
            'search'     => trim($_GET['search'] ?? ''),
            'page'       => 1,
            'pages'      => 1,
            'pageTitle'  => 'My Receipts',
            'activePage' => 'portal-receipts',
        ]);
    }

    public function receiptView(): void
    {
        $partner = $this->requirePartnerAuth();
        $db = Database::getInstance()->getConnection();
        $pid = (int)$partner['id'];
        $companyName = $partner['company_name'] ?? '';
        $id = (int)($_GET['id'] ?? 0);

        $receipt = null;
        try {
            // Receipts are paid invoices — query invoices table directly
            $stmt = $db->prepare(
                "SELECT * FROM invoices
                 WHERE id = ? AND status = 'paid' AND (partner_id = ? OR company_id = ? OR company_name = ?)"
            );
            $stmt->execute([$id, $pid, $pid, $companyName]);
            $receipt = $stmt->fetch();
        } catch (Exception $e) {}

        if (!$receipt) {
            header('Location: ' . url('portal/receipts'));
            exit;
        }

        $this->portalView('receipt_detail', [
            'partner'    => $partner,
            'receipt'    => $receipt,
            'pageTitle'  => 'Receipt Details',
            'activePage' => 'portal-receipts',
        ]);
    }
}
