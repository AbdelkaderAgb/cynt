<?php
/**
 * CYN Tourism - Enhanced Reports & Analytics
 * Comprehensive reporting dashboard with charts
 */

require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';

Auth::requireAuth();

// Date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Revenue Report
$revenueReport = Database::fetchAll(
    "SELECT DATE(created_at) as date, currency, COUNT(*) as invoice_count, SUM(total_amount) as total
     FROM invoices WHERE created_at BETWEEN ? AND ? AND status = 'paid'
     GROUP BY DATE(created_at), currency ORDER BY date DESC",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Company Report
$companyReport = Database::fetchAll(
    "SELECT company_name, COUNT(*) as voucher_count,
     SUM(CASE WHEN i.total_amount IS NOT NULL THEN i.total_amount ELSE 0 END) as total_revenue
     FROM vouchers v LEFT JOIN invoices i ON v.company_name = i.company_name AND i.created_at BETWEEN ? AND ?
     WHERE v.created_at BETWEEN ? AND ? GROUP BY company_name ORDER BY voucher_count DESC LIMIT 20",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59', $startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Transfer Statistics
$transferStats = Database::fetchOne(
    "SELECT COUNT(*) as total_transfers, SUM(total_pax) as total_passengers,
     COUNT(DISTINCT company_name) as unique_companies, COUNT(DISTINCT hotel_name) as unique_hotels
     FROM vouchers WHERE created_at BETWEEN ? AND ?",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Currency Summary
$currencySummary = Database::fetchAll(
    "SELECT currency, COUNT(*) as count, SUM(total_amount) as total
     FROM invoices WHERE created_at BETWEEN ? AND ? AND status = 'paid' GROUP BY currency",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Monthly revenue for chart
$monthlyRevenue = Database::fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total
     FROM invoices WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status = 'paid'
     GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month",
    []
);

// Transfer types distribution
$transferTypes = Database::fetchAll(
    "SELECT transfer_type, COUNT(*) as count FROM vouchers
     WHERE created_at BETWEEN ? AND ? GROUP BY transfer_type",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

// Partner performance
$partnerPerformance = Database::fetchAll(
    "SELECT p.company, COUNT(v.id) as voucher_count, SUM(v.total_pax) as total_pax
     FROM partners p LEFT JOIN vouchers v ON p.company = v.company_name
     AND v.created_at BETWEEN ? AND ? GROUP BY p.id ORDER BY voucher_count DESC LIMIT 10",
    [$startDate . ' 00:00:00', $endDate . ' 23:59:59']
);

$pageTitle = 'Raporlar';
$activePage = 'reports';
// TODO: Convert to MVC view -- include 'header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Raporlar & Analizler</h1>
            <p class="page-subtitle">Isletme performansinizi analiz edin</p>
        </div>
        <div class="page-actions">
            <a href="export-excel.php?type=reports&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="export-pdf.php?type=reports&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-secondary">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form action="reports.php" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label class="form-label">Baslangic Tarihi</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
            </div>
            <div>
                <label class="form-label">Bitis Tarihi</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrele
            </button>
            <a href="reports.php" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Sifirla
            </a>
        </form>
    </div>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-exchange-alt"></i></div>
        <div class="stat-content">
            <div class="stat-label">Toplam Transfer</div>
            <div class="stat-value"><?php echo number_format($transferStats['total_transfers'] ?? 0); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <div class="stat-label">Toplam Yolcu</div>
            <div class="stat-value"><?php echo number_format($transferStats['total_passengers'] ?? 0); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-building"></i></div>
        <div class="stat-content">
            <div class="stat-label">Aktif Sirket</div>
            <div class="stat-value"><?php echo number_format($transferStats['unique_companies'] ?? 0); ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-hotel"></i></div>
        <div class="stat-content">
            <div class="stat-label">Aktif Otel</div>
            <div class="stat-value"><?php echo number_format($transferStats['unique_hotels'] ?? 0); ?></div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-2" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="color: var(--primary); margin-right: 0.5rem;"></i>Aylik Gelir</h3>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="250"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color: var(--warning); margin-right: 0.5rem;"></i>Transfer Tipleri</h3>
        </div>
        <div class="card-body">
            <canvas id="transferTypeChart" height="250"></canvas>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="grid grid-cols-2" style="margin-bottom: 2rem;">
    <!-- Currency Summary -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-coins" style="color: var(--warning); margin-right: 0.5rem;"></i>Para Birimi Ozeti</h3>
        </div>
        <div class="card-body">
            <?php if (empty($currencySummary)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <p class="text-muted">Bu donem icin veri bulunmuyor.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr><th>Para Birimi</th><th>Fatura Sayisi</th><th>Toplam</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currencySummary as $row): ?>
                        <tr>
                            <td><?php echo $row['currency']; ?></td>
                            <td><?php echo $row['count']; ?></td>
                            <td style="font-weight: 600; color: var(--success);">
                                <?php echo format_currency($row['total'], $row['currency']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Top Companies -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy" style="color: var(--success); margin-right: 0.5rem;"></i>En Iyi Sirketler</h3>
        </div>
        <div class="card-body">
            <?php if (empty($companyReport)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <p class="text-muted">Bu donem icin veri bulunmuyor.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr><th>Sirket</th><th>Voucher</th><th>Gelir</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($companyReport, 0, 10) as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td><?php echo $row['voucher_count']; ?></td>
                            <td style="font-weight: 600;">$<?php echo number_format($row['total_revenue'] ?? 0, 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Partner Performance -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-handshake" style="color: var(--info); margin-right: 0.5rem;"></i>Partner Performansi</h3>
    </div>
    <div class="card-body">
        <?php if (empty($partnerPerformance)): ?>
        <div class="empty-state" style="padding: 2rem;">
            <p class="text-muted">Bu donem icin veri bulunmuyor.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Partner</th><th>Voucher Sayisi</th><th>Toplam Yolcu</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($partnerPerformance as $partner): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($partner['company']); ?></td>
                        <td><?php echo $partner['voucher_count']; ?></td>
                        <td><?php echo $partner['total_pax']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyRevenue, 'month')); ?>,
        datasets: [{
            label: 'Gelir ($)',
            data: <?php echo json_encode(array_column($monthlyRevenue, 'total')); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// Transfer Type Chart
const typeCtx = document.getElementById('transferTypeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($transferTypes, 'transfer_type')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($transferTypes, 'count')); ?>,
            backgroundColor: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php // TODO: Convert to MVC view -- include 'footer.php'; ?>
