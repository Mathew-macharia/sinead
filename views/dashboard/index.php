<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINEAD Hotel Management System - Dashboard">
    <title>Dashboard | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
    <div class="app-layout">
        <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>

        <div class="main-content">
            <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>

            <div class="content-area">
                <?php renderFlashMessages(); ?>

                <?php if (hasRole(ROLE_ADMIN)): ?>
                <!-- ═══════════════════════════════════════════════════════════
                     ADMIN DASHBOARD — Full operational + financial overview
                ════════════════════════════════════════════════════════════ -->

                <?php if (!empty($overdueCount) && $overdueCount > 0): ?>
                <div class="alert alert-error" role="alert" style="margin-bottom: var(--space-lg);">
                    <span class="alert-message">
                        <strong>Overdue Stays:</strong>
                        <?php echo (int)$overdueCount; ?> reservation<?php echo $overdueCount > 1 ? 's are' : ' is'; ?>
                        past the scheduled checkout date but still marked as Checked In.
                        <a href="<?php echo url('reservations', ['status' => 'CheckedIn']); ?>" style="color: inherit; font-weight: 600; text-decoration: underline;">Review now</a>
                    </span>
                    <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
                </div>
                <?php endif; ?>

                <!-- 4-card stats row -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Total Rooms</div>
                            <div class="stat-value"><?php echo $roomStats['total']; ?></div>
                            <div class="stat-change"><?php echo $occupancyRate; ?>% occupancy</div>
                        </div>
                        <div class="stat-icon gold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Available Rooms</div>
                            <div class="stat-value"><?php echo $roomStats['available']; ?></div>
                            <div class="stat-change positive">Ready for check-in</div>
                        </div>
                        <div class="stat-icon success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Occupied</div>
                            <div class="stat-value"><?php echo $roomStats['occupied']; ?></div>
                            <div class="stat-change"><?php echo $checkOutsCount; ?> check-outs today</div>
                        </div>
                        <div class="stat-icon purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Revenue Today</div>
                            <div class="stat-value" style="font-size: 1.5rem;"><?php echo formatCurrency($todayRevenue); ?></div>
                            <div class="stat-change">Monthly: <?php echo formatCurrency($revenueMonth); ?></div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <!-- Today's Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Today's Summary</h3>
                            <span class="badge badge-gold"><?php echo date('d M Y'); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Expected Check-ins</span>
                                <span class="detail-value"><?php echo $checkInsCount; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Expected Check-outs</span>
                                <span class="detail-value"><?php echo $checkOutsCount; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Rooms Under Maintenance</span>
                                <span class="detail-value"><?php echo $roomStats['maintenance']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Pending Housekeeping Tasks</span>
                                <span class="detail-value"><?php echo $pendingTasks; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header"><h3>Quick Actions</h3></div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm);">
                                <a href="<?php echo url('reservations', ['action' => 'create']); ?>" class="btn btn-primary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    New Reservation
                                </a>
                                <a href="<?php echo url('guests', ['action' => 'create']); ?>" class="btn btn-secondary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                    New Guest
                                </a>
                                <a href="<?php echo url('billing'); ?>" class="btn btn-secondary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                    Billing
                                </a>
                                <a href="<?php echo url('reports'); ?>" class="btn btn-secondary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                    Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reservations -->
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Reservations</h3>
                        <a href="<?php echo url('reservations'); ?>" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentReservations)): ?>
                                    <tr><td colspan="6" class="text-center p-lg text-muted">No reservations found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentReservations as $res): ?>
                                        <tr>
                                            <td style="font-weight:500;color:var(--text-primary);"><?php echo sanitize($res['guest_name']); ?></td>
                                            <td><?php echo sanitize($res['room_number']); ?> <span class="text-muted text-sm">(<?php echo sanitize($res['room_type']); ?>)</span></td>
                                            <td><?php echo formatDate($res['check_in_date']); ?></td>
                                            <td><?php echo formatDate($res['check_out_date']); ?></td>
                                            <td><span class="badge badge-dot badge-<?php echo strtolower($res['status']); ?>"><?php echo sanitize($res['status']); ?></span></td>
                                            <td class="text-muted"><?php echo formatDateTime($res['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Activity Log (Admin only) -->
                <?php if (!empty($recentActivity)): ?>
                <div class="card mt-lg">
                    <div class="card-header"><h3>Recent Activity</h3></div>
                    <div class="card-body">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="detail-row">
                                <div>
                                    <span style="font-weight:500;color:var(--text-primary);"><?php echo sanitize($activity['action']); ?></span>
                                    <?php if ($activity['full_name']): ?>
                                        <span class="text-muted text-sm"> by <?php echo sanitize($activity['full_name']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($activity['details']): ?>
                                        <div class="text-sm text-muted"><?php echo sanitize($activity['details']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="text-muted text-sm"><?php echo formatDateTime($activity['created_at']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>


                <?php elseif (hasRole(ROLE_RECEPTIONIST)): ?>
                <!-- ═══════════════════════════════════════════════════════════
                     RECEPTIONIST DASHBOARD — Operational view only (no revenue)
                ════════════════════════════════════════════════════════════ -->

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Available Rooms</div>
                            <div class="stat-value"><?php echo $roomStats['available']; ?></div>
                            <div class="stat-change positive">Ready for check-in</div>
                        </div>
                        <div class="stat-icon success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Occupied</div>
                            <div class="stat-value"><?php echo $roomStats['occupied']; ?></div>
                            <div class="stat-change"><?php echo $occupancyRate; ?>% occupancy</div>
                        </div>
                        <div class="stat-icon purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Today's Arrivals</div>
                            <div class="stat-value"><?php echo $checkInsCount; ?></div>
                            <div class="stat-change"><?php echo date('d M Y'); ?></div>
                        </div>
                        <div class="stat-icon gold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Today's Departures</div>
                            <div class="stat-value"><?php echo $checkOutsCount; ?></div>
                            <div class="stat-change">Guests checking out</div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-sm);">
                                <a href="<?php echo url('reservations', ['action' => 'create']); ?>" class="btn btn-primary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    New Reservation
                                </a>
                                <a href="<?php echo url('guests', ['action' => 'create']); ?>" class="btn btn-secondary btn-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                    New Guest
                                </a>
                                <a href="<?php echo url('reservations'); ?>" class="btn btn-secondary btn-block" style="grid-column: 1 / -1;">
                                    View All Reservations
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Today's Schedule</h3>
                            <span class="badge badge-gold"><?php echo date('d M Y'); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Rooms Available</span>
                                <span class="detail-value"><?php echo $roomStats['available']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Rooms in Maintenance</span>
                                <span class="detail-value"><?php echo $roomStats['maintenance']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Arrivals Today</span>
                                <span class="detail-value"><?php echo $checkInsCount; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Departures Today</span>
                                <span class="detail-value"><?php echo $checkOutsCount; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Pending Housekeeping</span>
                                <span class="detail-value"><?php echo $pendingTasks; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's reservations (arrivals + departures only, no all-time history) -->
                <?php if (!empty($recentReservations)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Today's Arrivals &amp; Departures</h3>
                        <a href="<?php echo url('reservations'); ?>" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentReservations as $res): ?>
                                    <tr>
                                        <td style="font-weight:500;color:var(--text-primary);"><?php echo sanitize($res['guest_name']); ?></td>
                                        <td><?php echo sanitize($res['room_number']); ?> <span class="text-muted text-sm">(<?php echo sanitize($res['room_type']); ?>)</span></td>
                                        <td><?php echo formatDate($res['check_in_date']); ?></td>
                                        <td><?php echo formatDate($res['check_out_date']); ?></td>
                                        <td><span class="badge badge-dot badge-<?php echo strtolower($res['status']); ?>"><?php echo sanitize($res['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>


                <?php else: ?>
                <!-- ═══════════════════════════════════════════════════════════
                     HOUSEKEEPING DASHBOARD — Task view only
                ════════════════════════════════════════════════════════════ -->

                <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">My Pending Tasks</div>
                            <div class="stat-value"><?php echo count($myTasks); ?></div>
                            <div class="stat-change">Assigned to you</div>
                        </div>
                        <div class="stat-icon warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <div class="stat-label">Rooms in Maintenance</div>
                            <div class="stat-value"><?php echo $roomStats['maintenance']; ?></div>
                            <div class="stat-change">Needs attention</div>
                        </div>
                        <div class="stat-icon purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>My Assigned Tasks</h3>
                        <a href="<?php echo url('housekeeping'); ?>" class="btn btn-ghost btn-sm">View All My Tasks</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($myTasks)): ?>
                            <p class="text-muted text-center p-lg">You have no pending tasks. Great work!</p>
                        <?php else: ?>
                            <?php foreach ($myTasks as $task): ?>
                                <div class="detail-row">
                                    <div>
                                        <span style="font-weight:500;color:var(--text-primary);">
                                            Room <?php echo sanitize($task['room_number']); ?>
                                            <span class="text-muted text-sm">(<?php echo sanitize($task['room_type']); ?>, Floor <?php echo (int)$task['floor']; ?>)</span>
                                        </span>
                                        <div class="text-sm text-muted"><?php echo sanitize($task['task_type']); ?> &mdash; Priority: <?php echo sanitize($task['priority']); ?></div>
                                    </div>
                                    <span class="badge badge-dot badge-<?php echo strtolower($task['status']); ?>"><?php echo sanitize($task['status']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php endif; ?>

            </div><!-- /.content-area -->

            <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
        </div>
    </div>

    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
