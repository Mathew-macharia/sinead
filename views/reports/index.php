<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <!-- Date Range Filter -->
            <div class="card mb-lg">
                <div class="card-body">
                    <form method="GET" class="d-flex align-center gap-md" style="flex-wrap: wrap;">
                        <input type="hidden" name="page" value="reports">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start" class="form-control" value="<?php echo sanitize($startDate); ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end" class="form-control" value="<?php echo sanitize($endDate); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 1.25rem;">Apply Filter</button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid mb-lg">
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Period Revenue</div>
                        <div class="stat-value" style="font-size: 1.5rem;"><?php echo formatCurrency($periodRevenue); ?></div>
                    </div>
                    <div class="stat-icon gold"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Period Bookings</div>
                        <div class="stat-value"><?php echo $periodBookings; ?></div>
                    </div>
                    <div class="stat-icon purple"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Avg. per Booking</div>
                        <div class="stat-value" style="font-size: 1.5rem;"><?php echo $periodBookings > 0 ? formatCurrency($periodRevenue / $periodBookings) : 'KES 0.00'; ?></div>
                    </div>
                    <div class="stat-icon info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="dashboard-grid mb-lg">
                <!-- Revenue Trend Chart -->
                <div class="card">
                    <div class="card-header"><h3>Revenue Trend</h3></div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Room Occupancy Chart -->
                <div class="card">
                    <div class="card-header"><h3>Room Occupancy by Type</h3></div>
                    <div class="card-body">
                        <canvas id="occupancyChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Revenue by Room Type -->
            <div class="dashboard-grid mb-lg">
                <div class="card">
                    <div class="card-header"><h3>Revenue by Room Type</h3></div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Avg. per Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($typeData)): ?>
                                    <tr><td colspan="4" class="text-center p-lg text-muted">No data for this period.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($typeData as $t): ?>
                                        <tr>
                                            <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($t['type']); ?></td>
                                            <td><?php echo $t['bookings']; ?></td>
                                            <td><?php echo formatCurrency($t['revenue']); ?></td>
                                            <td><?php echo $t['bookings'] > 0 ? formatCurrency($t['revenue'] / $t['bookings']) : '--'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Guests -->
                <div class="card">
                    <div class="card-header"><h3>Top Guests by Revenue</h3></div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Stays</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topGuestsData)): ?>
                                    <tr><td colspan="3" class="text-center p-lg text-muted">No data for this period.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($topGuestsData as $g): ?>
                                        <tr>
                                            <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($g['name']); ?></td>
                                            <td><?php echo $g['stays']; ?></td>
                                            <td style="color: var(--accent-gold);"><?php echo formatCurrency($g['total_spent']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Room Utilization -->
            <div class="card">
                <div class="card-header"><h3>Current Room Utilization</h3></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Total</th>
                                <th>Available</th>
                                <th>Occupied</th>
                                <th>Maintenance</th>
                                <th>Occupancy Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roomStats as $rs): ?>
                                <?php $occRate = $rs['total'] > 0 ? round(($rs['occupied'] / $rs['total']) * 100, 1) : 0; ?>
                                <tr>
                                    <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($rs['type']); ?></td>
                                    <td><?php echo $rs['total']; ?></td>
                                    <td><span class="text-success"><?php echo $rs['available']; ?></span></td>
                                    <td><span class="text-gold"><?php echo $rs['occupied']; ?></span></td>
                                    <td><span class="text-muted"><?php echo $rs['maintenance']; ?></span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: var(--space-sm);">
                                            <div style="flex: 1; height: 6px; background: var(--bg-surface); border-radius: 3px; overflow: hidden;">
                                                <div style="width: <?php echo $occRate; ?>%; height: 100%; background: var(--accent-gold); border-radius: 3px; transition: width 0.5s ease;"></div>
                                            </div>
                                            <span class="text-sm"><?php echo $occRate; ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
<script>
    /**
     * Chart.js Configuration
     * Styled to match the SINEAD dark luxury theme.
     */
    var chartDefaults = {
        color: '#B8A99A',
        borderColor: 'rgba(200, 148, 62, 0.12)',
        font: { family: "'Inter', sans-serif" }
    };

    Chart.defaults.color = chartDefaults.color;
    Chart.defaults.borderColor = chartDefaults.borderColor;
    Chart.defaults.font.family = chartDefaults.font.family;

    // Revenue Trend Line Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($r) { return "'" . date('d M', strtotime($r['date'])) . "'"; }, $revenueData)); ?>],
            datasets: [{
                label: 'Revenue (KES)',
                data: [<?php echo implode(',', array_column($revenueData, 'revenue')); ?>],
                borderColor: '#C8943E',
                backgroundColor: 'rgba(200, 148, 62, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#C8943E',
                pointBorderColor: '#C8943E',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(200, 148, 62, 0.06)' },
                    ticks: {
                        callback: function(value) { return 'KES ' + value.toLocaleString(); }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Room Occupancy Doughnut Chart
    var occCtx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(occCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', array_map(function($r) { return "'" . $r['type'] . "'"; }, $roomStats)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($roomStats, 'occupied')); ?>],
                backgroundColor: ['#C8943E', '#7C5CBF', '#4A8C5C'],
                borderColor: '#2C1A0E',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true, pointStyleWidth: 10 }
                }
            }
        }
    });
</script>
</body>
</html>
