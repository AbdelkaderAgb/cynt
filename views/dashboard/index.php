<?php
/**
 * Dashboard View — Enhanced with charts, multi-currency, and translations
 * 
 * Variables: $stats, $upcomingTransfers, $currencyRevenue, $monthlyTrend,
 *            $topPartners, $paymentBreakdown, $pageTitle
 */
?>

<!-- Page Header -->
<div class="mb-6 sm:mb-8 animate-fade-in-up">
    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white"><?php echo e($pageTitle); ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= __('welcome_back') ?>! <?= __('dashboard_subtitle', [], "Here's your overview for today.") ?></p>
</div>

<!-- Main Stats Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6 sm:mb-8">

    <?php
    $statCards = [
        ['value' => (int)$stats['todayTransfers'], 'label' => __('todays_transfers'), 'icon' => 'fa-exchange-alt', 'from' => 'blue-500', 'to' => 'cyan-500', 'shadow' => 'blue'],
        ['value' => (int)$stats['monthVouchers'], 'label' => __('monthly_vouchers', [], 'Vouchers'), 'icon' => 'fa-ticket-alt', 'from' => 'pink-500', 'to' => 'rose-500', 'shadow' => 'pink'],
        ['value' => (int)$stats['monthHotelVouchers'], 'label' => __('monthly_hotel_vouchers', [], 'Hotels'), 'icon' => 'fa-hotel', 'from' => 'emerald-500', 'to' => 'teal-500', 'shadow' => 'emerald'],
        ['value' => (int)$stats['monthTourVouchers'], 'label' => __('monthly_tour_vouchers', [], 'Tours'), 'icon' => 'fa-map-marked-alt', 'from' => 'amber-500', 'to' => 'orange-500', 'shadow' => 'amber'],
        ['value' => (int)$stats['pendingInvoices'], 'label' => __('pending_invoices'), 'icon' => 'fa-clock', 'from' => 'violet-500', 'to' => 'purple-500', 'shadow' => 'violet'],
        ['value' => (int)$stats['totalPartners'], 'label' => __('partners'), 'icon' => 'fa-handshake', 'from' => 'teal-500', 'to' => 'emerald-500', 'shadow' => 'teal'],
    ];
    foreach ($statCards as $card):
    ?>
    <div class="group bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-<?= $card['from'] ?> to-<?= $card['to'] ?> opacity-0 group-hover:opacity-100 transition-opacity rounded-r-full"></div>
        <div class="flex flex-col items-center text-center gap-2">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-<?= $card['from'] ?> to-<?= $card['to'] ?> flex items-center justify-center text-white text-base shadow-lg shadow-<?= $card['shadow'] ?>-500/25 group-hover:scale-110 transition-transform">
                <i class="fas <?= $card['icon'] ?>"></i>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white"><?= $card['value'] ?></div>
                <div class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight"><?= $card['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Revenue + Fleet Row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
    <?php if (!empty($currencyRevenue)): ?>
        <?php foreach ($currencyRevenue as $cr): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold shadow-lg shadow-emerald-500/25 flex-shrink-0">
                    <?= e($cr['currency'] ?? 'USD') ?>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] sm:text-xs text-slate-400 truncate"><?= __('monthly_revenue') ?></div>
                    <div class="text-sm sm:text-base font-bold text-emerald-600 truncate"><?= format_currency($cr['total'], $cr['currency'] ?? 'USD') ?></div>
                </div>
            </div>
            <div class="text-[10px] sm:text-xs text-slate-400 mt-1.5"><?= (int)$cr['count'] ?> <?= __('paid_invoices_count', [], 'paid') ?></div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm col-span-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white text-sm font-bold">—</div>
                <div>
                    <div class="text-xs text-slate-400"><?= __('monthly_revenue') ?></div>
                    <div class="text-lg font-bold text-slate-400">0.00</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vehicles -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white text-sm shadow-lg shadow-indigo-500/25 flex-shrink-0">
                <i class="fas fa-car"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('vehicles', [], 'Vehicles') ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)$stats['totalVehicles'] ?></div>
            </div>
        </div>
    </div>

    <!-- Drivers -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-amber-600 to-yellow-500 flex items-center justify-center text-white text-sm shadow-lg shadow-amber-500/25 flex-shrink-0">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('drivers') ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)$stats['totalDrivers'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Operations Row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
    <a href="<?= url('missions') ?>" class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm shadow-lg shadow-indigo-500/25 flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-tasks"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('pending_missions') ?: 'Pending Missions' ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)($stats['pendingMissions'] ?? 0) ?></div>
            </div>
        </div>
    </a>
    <a href="<?= url('missions') ?>" class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center text-white text-sm shadow-lg shadow-cyan-500/25 flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-running"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('active_missions') ?: 'Active Missions' ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)($stats['activeMissions'] ?? 0) ?></div>
            </div>
        </div>
    </a>
    <a href="<?= url('quotations') ?>" class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-sm shadow-lg shadow-orange-500/25 flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('open_quotations') ?: 'Open Quotations' ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)($stats['openQuotations'] ?? 0) ?></div>
            </div>
        </div>
    </a>
    <a href="<?= url('group-files') ?>" class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white text-sm shadow-lg shadow-violet-500/25 flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-folder-open"></i>
            </div>
            <div>
                <div class="text-[10px] sm:text-xs text-slate-400"><?= __('active_group_files') ?: 'Active Groups' ?></div>
                <div class="text-sm sm:text-lg font-bold text-slate-900 dark:text-white"><?= (int)($stats['activeGroupFiles'] ?? 0) ?></div>
            </div>
        </div>
    </a>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <!-- Monthly Trend Chart -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-6">
        <h3 class="text-sm sm:text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-blue-500"></i> <?= __('monthly_trend', [], 'Monthly Trend') ?>
        </h3>
        <div class="relative" style="height: 220px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Payment Breakdown Chart -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-6">
        <h3 class="text-sm sm:text-lg font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-chart-pie text-purple-500"></i> <?= __('payment_breakdown', [], 'Payment Breakdown') ?>
        </h3>
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
            <div class="w-32 h-32 sm:w-44 sm:h-44 flex-shrink-0">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="space-y-2.5 w-full flex-1">
                <?php
                $pbColors = ['paid'=>'emerald','pending'=>'amber','overdue'=>'red','draft'=>'slate'];
                $pbLabels = ['paid'=>__('paid'),'pending'=>__('pending'),'overdue'=>__('overdue'),'draft'=>__('draft')];
                $pbTotal = array_sum($paymentBreakdown) ?: 1;
                foreach ($paymentBreakdown as $status => $count):
                    $pct = round(($count / $pbTotal) * 100);
                    $color = $pbColors[$status] ?? 'slate';
                ?>
                <div>
                    <div class="flex justify-between text-xs sm:text-sm mb-1">
                        <span class="text-slate-600 dark:text-slate-300"><?= $pbLabels[$status] ?? ucfirst($status) ?></span>
                        <span class="font-semibold"><?= $count ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="h-1.5 sm:h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-<?= $color ?>-500 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Partners & Upcoming Transfers -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-8">
    <!-- Top Partners -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm sm:text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-trophy text-amber-500"></i> <?= __('top_partners', [], 'Top Partners') ?>
            </h3>
        </div>
        <?php if (empty($topPartners)): ?>
        <div class="text-center py-10 px-6">
            <div class="w-14 h-14 mx-auto mb-3 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                <i class="fas fa-handshake text-xl text-slate-400"></i>
            </div>
            <p class="text-sm text-slate-500"><?= __('no_data_found') ?></p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-xs sm:text-sm min-w-[400px]">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 text-left">
                    <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">#</th>
                    <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('company_name') ?></th>
                    <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('total_invoices', [], 'Inv.') ?></th>
                    <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('total_amount') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php foreach ($topPartners as $i => $p): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-3 sm:px-6 py-2.5 sm:py-3">
                        <?php if ($i === 0): ?><span class="text-amber-500"><i class="fas fa-medal"></i></span>
                        <?php elseif ($i === 1): ?><span class="text-slate-400"><i class="fas fa-medal"></i></span>
                        <?php elseif ($i === 2): ?><span class="text-amber-700"><i class="fas fa-medal"></i></span>
                        <?php else: ?><span class="text-slate-400"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 sm:px-6 py-2.5 sm:py-3 font-medium text-slate-700 dark:text-slate-300"><?= e($p['company_name']) ?></td>
                    <td class="px-3 sm:px-6 py-2.5 sm:py-3 text-slate-500"><?= (int)$p['invoice_count'] ?></td>
                    <td class="px-3 sm:px-6 py-2.5 sm:py-3 font-semibold text-emerald-600"><?= format_currency($p['total_revenue'], $p['currency'] ?? 'USD') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Transfers -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm sm:text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-calendar-day text-brand-500"></i>
                <?= __('upcoming_bookings') ?>
            </h3>
            <a href="<?= url('/transfers') ?>" class="text-xs sm:text-sm text-brand-500 hover:text-brand-600 font-medium transition-colors whitespace-nowrap">
                <?= __('view_all') ?> <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <?php if (empty($upcomingTransfers)): ?>
            <div class="text-center py-10 px-6">
                <div class="w-14 h-14 mx-auto mb-3 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-times text-xl text-slate-400"></i>
                </div>
                <h4 class="text-slate-700 dark:text-slate-300 font-medium mb-1"><?= __('no_upcoming_transfers', [], 'No upcoming transfers') ?></h4>
                <p class="text-sm text-slate-500"><?= __('create_first_transfer', [], 'Create a new transfer to get started.') ?></p>
            </div>
            <?php else: ?>
            <table class="w-full text-xs sm:text-sm min-w-[480px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 text-left">
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('voucher_no') ?></th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('company_name') ?></th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('date') ?></th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('time') ?></th>
                        <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500"><?= __('status') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php foreach ($upcomingTransfers as $transfer): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-3 sm:px-6 py-2.5 sm:py-3 font-medium text-brand-600 dark:text-brand-400">
                            <a href="<?= url('vouchers/show') ?>?id=<?= $transfer['id'] ?>"><?= e($transfer['voucher_no']) ?></a>
                        </td>
                        <td class="px-3 sm:px-6 py-2.5 sm:py-3 text-slate-700 dark:text-slate-300"><?= e($transfer['company_name']) ?></td>
                        <td class="px-3 sm:px-6 py-2.5 sm:py-3 text-slate-700 dark:text-slate-300"><?= date('d/m/Y', strtotime($transfer['pickup_date'])) ?></td>
                        <td class="px-3 sm:px-6 py-2.5 sm:py-3 text-slate-700 dark:text-slate-300"><?= e($transfer['pickup_time']) ?></td>
                        <td class="px-3 sm:px-6 py-2.5 sm:py-3">
                            <?php
                            $statusColors = [
                                'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            ];
                            $status = $transfer['status'] ?? 'pending';
                            $colorClass = $statusColors[$status] ?? $statusColors['pending'];
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-medium <?= $colorClass ?>"><?= __(strtolower($status)) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(203, 213, 225, 0.5)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // Monthly Trend Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlyTrend, 'label')) ?>,
                datasets: [
                    {
                        label: '<?= __('vouchers', [], 'Vouchers') ?>',
                        data: <?= json_encode(array_column($monthlyTrend, 'vouchers')) ?>,
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
                    },
                    {
                        label: '<?= __('total_invoices', [], 'Invoices') ?>',
                        data: <?= json_encode(array_column($monthlyTrend, 'invoices')) ?>,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, font: { size: 11 }, color: textColor } } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 }, maxRotation: 45 } }
                }
            }
        });
    }

    // Payment Breakdown Chart
    const payCtx = document.getElementById('paymentChart');
    if (payCtx) {
        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: ['<?= __('paid') ?>', '<?= __('pending') ?>', '<?= __('overdue') ?>', '<?= __('draft') ?>'],
                datasets: [{
                    data: [<?= (int)$paymentBreakdown['paid'] ?>, <?= (int)$paymentBreakdown['pending'] ?>, <?= (int)$paymentBreakdown['overdue'] ?>, <?= (int)$paymentBreakdown['draft'] ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                    borderWidth: 0, hoverOffset: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, cutout: '65%', plugins: { legend: { display: false } } }
        });
    }
});
</script>
