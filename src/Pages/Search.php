<?php
/**
 * CYN Tourism - Advanced Search
 * Universal search with filters and autocomplete
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

Auth::requireAuth();

$query = trim($_GET['q'] ?? '');
$filterType = $_GET['type'] ?? 'all';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$company = $_GET['company'] ?? '';
$status = $_GET['status'] ?? '';

$results = [];
$hasSearch = strlen($query) >= 2 || !empty($dateFrom) || !empty($dateTo) || !empty($company) || !empty($status);

if ($hasSearch) {
    $searchTerm = "%{$query}%";

    // Search vouchers
    if ($filterType === 'all' || $filterType === 'voucher') {
        $voucherWhere = "(v.voucher_no LIKE ? OR v.company_name LIKE ? OR v.hotel_name LIKE ? OR v.flight_number LIKE ?)";
        $voucherParams = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];

        if (!empty($dateFrom)) {
            $voucherWhere .= " AND v.pickup_date >= ?";
            $voucherParams[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $voucherWhere .= " AND v.pickup_date <= ?";
            $voucherParams[] = $dateTo;
        }
        if (!empty($company)) {
            $voucherWhere .= " AND v.company_name = ?";
            $voucherParams[] = $company;
        }

        $vouchers = Database::fetchAll(
            "SELECT v.id, v.voucher_no as title, v.company_name as subtitle, v.hotel_name, 
             v.pickup_date, v.total_pax, 'voucher' as type, v.created_at, v.transfer_type
             FROM vouchers v WHERE $voucherWhere ORDER BY v.created_at DESC LIMIT 20",
            $voucherParams
        );
        $results = array_merge($results, $vouchers);
    }

    // Search hotel vouchers
    if ($filterType === 'all' || $filterType === 'hotel_voucher') {
        $hotelVouchers = Database::fetchAll(
            "SELECT hv.id, hv.voucher_no as title, hv.company_name as subtitle, hv.hotel as hotel_name,
             hv.check_in_date as pickup_date, hv.room_count as total_pax, 'hotel_voucher' as type, hv.created_at
             FROM h_vouchers hv WHERE hv.voucher_no LIKE ? OR hv.company_name LIKE ? OR hv.hotel LIKE ?
             ORDER BY hv.created_at DESC LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );
        $results = array_merge($results, $hotelVouchers);
    }

    // Search invoices
    if ($filterType === 'all' || $filterType === 'invoice') {
        $invoiceWhere = "(i.invoice_no LIKE ? OR i.company_name LIKE ? OR i.hotel_name LIKE ?)";
        $invoiceParams = [$searchTerm, $searchTerm, $searchTerm];

        if (!empty($status)) {
            $invoiceWhere .= " AND i.status = ?";
            $invoiceParams[] = $status;
        }

        $invoices = Database::fetchAll(
            "SELECT i.id, i.invoice_no as title, i.company_name as subtitle, i.hotel_name,
             i.created_at as pickup_date, i.total_amount as total_pax, 'invoice' as type, i.created_at, i.status
             FROM invoices i WHERE $invoiceWhere ORDER BY i.created_at DESC LIMIT 10",
            $invoiceParams
        );
        $results = array_merge($results, $invoices);
    }

    // Search tour vouchers
    if ($filterType === 'all' || $filterType === 'tour_voucher') {
        $tourVouchers = Database::fetchAll(
            "SELECT tv.id, tv.voucher_no as title, tv.company_name as subtitle, tv.hotel_name,
             tv.created_at as pickup_date, (tv.adult + tv.child + tv.infant) as total_pax,
             'tour_voucher' as type, tv.created_at
             FROM city_tour_vouchers tv WHERE tv.voucher_no LIKE ? OR tv.company_name LIKE ? OR tv.hotel_name LIKE ?
             ORDER BY tv.created_at DESC LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );
        $results = array_merge($results, $tourVouchers);
    }

    // Search companies/partners
    if ($filterType === 'all' || $filterType === 'company') {
        $companies = Database::fetchAll(
            "SELECT p.id, p.company as title, p.email as subtitle, p.phone as hotel_name,
             p.created_at as pickup_date, 0 as total_pax, 'company' as type, p.created_at
             FROM partners p WHERE p.company LIKE ? OR p.email LIKE ? OR p.phone LIKE ?
             ORDER BY p.company ASC LIMIT 10",
            [$searchTerm, $searchTerm, $searchTerm]
        );
        $results = array_merge($results, $companies);
    }

    // Sort by created_at
    usort($results, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    $results = array_slice($results, 0, 50);
}

// Get companies for filter dropdown
$companies = Database::fetchAll("SELECT DISTINCT company as name FROM partners WHERE status = 'active' ORDER BY company");

$pageTitle = 'Arama Sonuclari';
$activePage = '';
// TODO: Convert to MVC view -- include 'header.php';

function getTypeInfo($type) {
    $types = [
        'voucher' => ['label' => 'Transfer Voucher', 'icon' => 'fa-ticket-alt', 'color' => '#6366f1', 'link' => 'view-transfer.php?id='],
        'hotel_voucher' => ['label' => 'Otel Voucher', 'icon' => 'fa-hotel', 'color' => '#06b6d4', 'link' => 'view-hotel.php?id='],
        'invoice' => ['label' => 'Fatura', 'icon' => 'fa-file-invoice-dollar', 'color' => '#22c55e', 'link' => 'view-invoice.php?id='],
        'tour_voucher' => ['label' => 'Tur Voucher', 'icon' => 'fa-route', 'color' => '#8b5cf6', 'link' => 'view-tour.php?id='],
        'company' => ['label' => 'Sirket', 'icon' => 'fa-building', 'color' => '#64748b', 'link' => 'partners.php?search=']
    ];
    return $types[$type] ?? ['label' => 'Bilinmiyor', 'icon' => 'fa-question', 'color' => '#94a3b8', 'link' => '#'];
}
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Arama Sonuclari</h1>
            <p class="page-subtitle">
                <?php if (empty($query) && !$hasSearch): ?>Arama yapmak icin bir terim girin
                <?php else: ?>"<?php echo htmlspecialchars($query ?: 'Tum'); ?>" icin <?php echo count($results); ?> sonuc bulundu
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Advanced Search Form -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form action="search.php" method="GET">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 0.75rem; align-items: end;">
                <div>
                    <label class="form-label">Arama</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--gray-500);"></i>
                        <input type="text" name="q" class="form-control" placeholder="Voucher, fatura, sirket ara..." 
                               value="<?php echo htmlspecialchars($query); ?>" style="padding-left: 2.25rem;" autofocus autocomplete="off">
                    </div>
                </div>
                <div>
                    <label class="form-label">Tip</label>
                    <select name="type" class="form-control">
                        <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>Tumu</option>
                        <option value="voucher" <?php echo $filterType === 'voucher' ? 'selected' : ''; ?>>Transfer</option>
                        <option value="hotel_voucher" <?php echo $filterType === 'hotel_voucher' ? 'selected' : ''; ?>>Otel</option>
                        <option value="invoice" <?php echo $filterType === 'invoice' ? 'selected' : ''; ?>>Fatura</option>
                        <option value="tour_voucher" <?php echo $filterType === 'tour_voucher' ? 'selected' : ''; ?>>Tur</option>
                        <option value="company" <?php echo $filterType === 'company' ? 'selected' : ''; ?>>Sirket</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Baslangic</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                <div>
                    <label class="form-label">Bitis</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                <div>
                    <label class="form-label">Sirket</label>
                    <select name="company" class="form-control">
                        <option value="">Tumu</option>
                        <?php foreach ($companies as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['name']); ?>" <?php echo $company === $c['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-search"></i> Ara
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($results)): ?>
<!-- Filter Tabs -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => 'all'])); ?>" 
       class="btn btn-sm <?php echo $filterType === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
        Tumu (<?php echo count($results); ?>)
    </a>
    <?php
    $typeCounts = [];
    foreach ($results as $r) {
        $typeCounts[$r['type']] = ($typeCounts[$r['type']] ?? 0) + 1;
    }
    foreach ($typeCounts as $type => $count):
        $info = getTypeInfo($type);
    ?>
    <a href="?<?php echo http_build_query(array_merge($_GET, ['type' => $type])); ?>" 
       class="btn btn-sm <?php echo $filterType === $type ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas <?php echo $info['icon']; ?>"></i> <?php echo $info['label']; ?> (<?php echo $count; ?>)
    </a>
    <?php endforeach; ?>
</div>

<!-- Results Grid -->
<div class="grid grid-cols-2">
    <?php foreach ($results as $result): 
        $info = getTypeInfo($result['type']);
    ?>
    <div class="card search-result" data-type="<?php echo $result['type']; ?>">
        <div class="card-body">
            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; 
                            background: <?php echo $info['color']; ?>20; color: <?php echo $info['color']; ?>; font-size: 1.25rem;">
                    <i class="fas <?php echo $info['icon']; ?>"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <span class="badge" style="background: <?php echo $info['color']; ?>20; color: <?php echo $info['color']; ?>">
                            <?php echo $info['label']; ?>
                        </span>
                        <?php if (!empty($result['status'])): ?>
                        <span class="badge badge-<?php echo $result['status'] === 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo $result['status']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h4 style="font-size: 1rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($result['title']); ?></h4>
                    <p style="color: var(--gray-500); font-size: 0.875rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($result['subtitle']); ?>
                    </p>
                    <?php if (!empty($result['hotel_name'])): ?>
                    <p style="font-size: 0.75rem; color: var(--gray-400);">
                        <i class="fas fa-hotel" style="margin-right: 0.25rem;"></i><?php echo htmlspecialchars($result['hotel_name']); ?>
                    </p>
                    <?php endif; ?>
                    <p style="font-size: 0.75rem; color: var(--gray-400);">
                        <i class="fas fa-calendar" style="margin-right: 0.25rem;"></i><?php echo format_date($result['pickup_date']); ?>
                        <?php if (!empty($result['total_pax'])): ?>
                        <i class="fas fa-users" style="margin: 0 0.25rem 0 0.5rem;"></i><?php echo $result['total_pax']; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <a href="<?php echo $info['link'] . $result['id']; ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif ($hasSearch): ?>
<div class="empty-state" style="padding: 4rem;">
    <div class="icon"><i class="fas fa-search"></i></div>
    <h3>Sonuc Bulunamadi</h3>
    <p>"<?php echo htmlspecialchars($query); ?>" icin herhangi bir sonuc bulunamadi.</p>
</div>
<?php endif; ?>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
