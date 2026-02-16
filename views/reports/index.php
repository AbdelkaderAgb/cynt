<?php /** Reports & Analytics View — Enhanced with Revenue Analytics */ ?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-chart-bar text-indigo-500 mr-2"></i>Reports & Analytics</h1></div>
    <form method="GET" action="<?= url('reports') ?>" class="flex gap-2 items-center">
        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
        <span class="text-gray-400">—</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold"><i class="fas fa-filter mr-1"></i>Filter</button>
    </form>
</div>

<!-- Invoice Revenue Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/25">
        <p class="text-sm opacity-80 mb-1">Total Invoices</p>
        <p class="text-3xl font-bold"><?= number_format($invoiceSummary['invoice_count'] ?? 0) ?></p>
    </div>
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/25">
        <p class="text-sm opacity-80 mb-1">💰 Paid</p>
        <p class="text-2xl font-bold"><?= number_format($invoiceSummary['paid_amount'] ?? 0, 2) ?></p>
    </div>
    <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/25">
        <p class="text-sm opacity-80 mb-1">⏳ Unpaid</p>
        <p class="text-2xl font-bold"><?= number_format($invoiceSummary['unpaid_amount'] ?? 0, 2) ?></p>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-5 text-white shadow-lg shadow-red-500/25">
        <p class="text-sm opacity-80 mb-1">🚨 Overdue</p>
        <p class="text-2xl font-bold"><?= number_format($invoiceSummary['overdue_amount'] ?? 0, 2) ?></p>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-purple-500/25">
        <p class="text-sm opacity-80 mb-1">📊 Grand Total</p>
        <p class="text-2xl font-bold"><?= number_format($invoiceSummary['total_amount'] ?? 0, 2) ?></p>
    </div>
</div>

<!-- Transfer Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"><i class="fas fa-exchange-alt text-blue-500"></i></div>
        <div><p class="text-xs text-gray-400">Total Transfers</p><p class="text-xl font-bold text-gray-800 dark:text-white"><?= number_format($transferStats['total_transfers'] ?? 0) ?></p></div></div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center"><i class="fas fa-users text-emerald-500"></i></div>
        <div><p class="text-xs text-gray-400">Total Passengers</p><p class="text-xl font-bold text-gray-800 dark:text-white"><?= number_format($transferStats['total_passengers'] ?? 0) ?></p></div></div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center"><i class="fas fa-building text-purple-500"></i></div>
        <div><p class="text-xs text-gray-400">Unique Companies</p><p class="text-xl font-bold text-gray-800 dark:text-white"><?= number_format($transferStats['unique_companies'] ?? 0) ?></p></div></div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i class="fas fa-hotel text-amber-500"></i></div>
        <div><p class="text-xs text-gray-400">Unique Hotels</p><p class="text-xl font-bold text-gray-800 dark:text-white"><?= number_format($transferStats['unique_hotels'] ?? 0) ?></p></div></div>
    </div>
</div>

<!-- Charts Row 1: Revenue + Service Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-chart-line text-emerald-500 mr-2"></i>Monthly Revenue</h3>
        <canvas id="revenueChart" height="250"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-chart-pie text-blue-500 mr-2"></i>Service Type Breakdown</h3>
        <div class="flex items-center gap-6">
            <div class="w-48 h-48 flex-shrink-0"><canvas id="serviceChart" height="180"></canvas></div>
            <div class="space-y-3 flex-1">
                <?php
                $svcColors = ['transfer' => 'blue', 'hotel' => 'purple', 'tour' => 'emerald'];
                $svcIcons = ['transfer' => 'fa-car-side', 'hotel' => 'fa-hotel', 'tour' => 'fa-map-marked-alt'];
                $svcTotal = array_sum($serviceBreakdown) ?: 1;
                foreach ($serviceBreakdown as $type => $count):
                    $pct = round(($count / $svcTotal) * 100);
                    $color = $svcColors[$type] ?? 'slate';
                ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-300"><i class="fas <?= $svcIcons[$type] ?? 'fa-box' ?> text-<?= $color ?>-500 mr-1"></i><?= ucfirst($type) ?>s</span>
                        <span class="font-semibold"><?= $count ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full"><div class="h-full bg-<?= $color ?>-500 rounded-full" style="width:<?= $pct ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2: Transfer Types + Revenue By Partner -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-chart-pie text-purple-500 mr-2"></i>Transfer Types</h3>
        <canvas id="typeChart" height="250"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-trophy text-amber-500 mr-2"></i>Revenue by Partner</h3>
        <canvas id="partnerRevenueChart" height="250"></canvas>
    </div>
</div>

<!-- Tables Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Companies -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-building text-purple-500 mr-2"></i>Most Active Companies</h3>
        <div class="space-y-3">
            <?php foreach ($topCompanies as $i => $c): $pct = $topCompanies[0]['voucher_count'] > 0 ? ($c['voucher_count'] / $topCompanies[0]['voucher_count'] * 100) : 0; ?>
            <div>
                <div class="flex justify-between text-sm mb-1"><span class="font-medium text-gray-700 dark:text-gray-300"><?= e($c['company_name']) ?></span><span class="text-gray-400"><?= $c['voucher_count'] ?> vouchers</span></div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2"><div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2 rounded-full transition-all" style="width:<?= $pct ?>%"></div></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($topCompanies)): ?><p class="text-gray-400 text-sm text-center py-4">No data</p><?php endif; ?>
        </div>
    </div>

    <!-- Currency Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-coins text-amber-500 mr-2"></i>Currency Summary</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-600"><th class="py-2 text-left text-gray-500 text-xs uppercase">Currency</th><th class="py-2 text-center text-gray-500 text-xs uppercase">Count</th><th class="py-2 text-right text-gray-500 text-xs uppercase">Total</th></tr></thead>
                <tbody>
                    <?php foreach ($currencySummary as $cs): ?>
                    <tr class="border-b border-gray-100 dark:border-gray-700"><td class="py-2 font-semibold"><?= e($cs['currency']) ?></td><td class="py-2 text-center"><?= $cs['count'] ?></td><td class="py-2 text-right font-bold text-emerald-600"><?= number_format($cs['total'], 2) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($currencySummary)): ?><tr><td colspan="3" class="py-4 text-center text-gray-400">No data</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Revenue By Partner Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4"><i class="fas fa-handshake text-indigo-500 mr-2"></i>Revenue by Partner</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase">Partner</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-500 uppercase">Invoices</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Paid</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Unpaid</th>
                    <th class="py-3 px-4 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php foreach ($revenueByPartner as $i => $rp): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="py-3 px-4">
                        <?php if ($i === 0): ?><span class="text-amber-500"><i class="fas fa-medal"></i></span>
                        <?php elseif ($i === 1): ?><span class="text-slate-400"><i class="fas fa-medal"></i></span>
                        <?php elseif ($i === 2): ?><span class="text-amber-700"><i class="fas fa-medal"></i></span>
                        <?php else: ?><span class="text-gray-400"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 font-medium text-gray-700 dark:text-gray-300"><?= e($rp['company_name']) ?></td>
                    <td class="py-3 px-4 text-center"><?= (int)$rp['invoice_count'] ?></td>
                    <td class="py-3 px-4 text-right font-semibold text-emerald-600"><?= number_format($rp['paid_total'], 2) ?> <?= e($rp['currency'] ?? '') ?></td>
                    <td class="py-3 px-4 text-right font-semibold text-amber-600"><?= number_format($rp['unpaid_total'], 2) ?> <?= e($rp['currency'] ?? '') ?></td>
                    <td class="py-3 px-4 text-right font-bold text-gray-800 dark:text-gray-200"><?= number_format($rp['grand_total'], 2) ?> <?= e($rp['currency'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($revenueByPartner)): ?><tr><td colspan="6" class="py-8 text-center text-gray-400">No invoice data for this period</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Revenue Chart
    const revData = <?= json_encode($monthlyRevenue) ?>;
    if (document.getElementById('revenueChart') && revData.length) {
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: revData.map(r => r.month),
                datasets: [{
                    label: 'Revenue',
                    data: revData.map(r => r.revenue),
                    backgroundColor: 'rgba(16,185,129,0.2)',
                    borderColor: 'rgb(16,185,129)',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    } else if (document.getElementById('revenueChart')) {
        document.getElementById('revenueChart').parentElement.innerHTML += '<p class="text-gray-400 text-sm text-center py-8">No revenue data for this period</p>';
    }

    // Transfer Type Chart
    const typeData = <?= json_encode($transferTypes) ?>;
    if (document.getElementById('typeChart') && typeData.length) {
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: typeData.map(t => t.transfer_type || 'Other'),
                datasets: [{ data: typeData.map(t => t.count), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // Service Breakdown Chart
    const svcData = <?= json_encode($serviceBreakdown) ?>;
    if (document.getElementById('serviceChart')) {
        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: ['Transfers', 'Hotels', 'Tours'],
                datasets: [{ data: [svcData.transfer || 0, svcData.hotel || 0, svcData.tour || 0], backgroundColor: ['#3b82f6','#8b5cf6','#10b981'], borderWidth: 0, hoverOffset: 8 }]
            },
            options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
        });
    }

    // Revenue By Partner Chart
    const partnerData = <?= json_encode($revenueByPartner) ?>;
    if (document.getElementById('partnerRevenueChart') && partnerData.length) {
        new Chart(document.getElementById('partnerRevenueChart'), {
            type: 'bar',
            data: {
                labels: partnerData.map(p => p.company_name.length > 15 ? p.company_name.substring(0, 15) + '…' : p.company_name),
                datasets: [
                    { label: 'Paid', data: partnerData.map(p => p.paid_total), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 4 },
                    { label: 'Unpaid', data: partnerData.map(p => p.unpaid_total), backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            }
        });
    }
});
</script>
