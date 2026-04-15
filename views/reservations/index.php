<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <!-- Status Summary Cards -->
            <div class="stats-grid mb-lg">
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Confirmed</div>
                        <div class="stat-value"><?php echo $statusCounts['Confirmed'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon warning"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Checked In</div>
                        <div class="stat-value"><?php echo $statusCounts['CheckedIn'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Checked Out</div>
                        <div class="stat-value"><?php echo $statusCounts['CheckedOut'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value"><?php echo $statusCounts['Cancelled'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon danger"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div>
                </div>
            </div>

            <div class="filter-bar">
                <div class="search-input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" class="search-input" placeholder="Search by guest name or room..." data-table-search="reservationsTable">
                </div>
                <select class="filter-select" data-filter-status="reservationsTable">
                    <option value="">All Statuses</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="checkedin">Checked In</option>
                    <option value="checkedout">Checked Out</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <a href="<?php echo url('reservations', ['action' => 'create']); ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New Reservation
                </a>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="reservationsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Nights</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservations)): ?>
                                <tr><td colspan="8" class="text-center p-lg text-muted">No reservations found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $r['id']; ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($r['guest_name']); ?></td>
                                        <td><?php echo sanitize($r['room_number']); ?> <span class="text-muted text-sm">(<?php echo sanitize($r['room_type']); ?>)</span></td>
                                        <td><?php echo formatDate($r['check_in_date']); ?></td>
                                        <td><?php echo formatDate($r['check_out_date']); ?></td>
                                        <td><?php echo $r['nights']; ?></td>
                                        <td><span class="badge badge-dot badge-<?php echo strtolower($r['status']); ?>"><?php echo sanitize($r['status']); ?></span></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="<?php echo url('reservations', ['action' => 'view', 'id' => $r['id']]); ?>" class="btn btn-ghost btn-sm">View</a>
                                                <?php if ($r['status'] === 'Confirmed'): ?>
                                                    <form method="POST" action="<?php echo url('reservations', ['action' => 'checkin']); ?>" style="display:inline;">
                                                        <?php csrfField(); ?>
                                                        <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                                        <button type="submit" class="btn btn-ghost btn-sm text-success">Check In</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($r['status'] === 'CheckedIn'): ?>
                                                    <form method="POST" action="<?php echo url('reservations', ['action' => 'checkout']); ?>" style="display:inline;">
                                                        <?php csrfField(); ?>
                                                        <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                                        <button type="submit" class="btn btn-ghost btn-sm text-gold">Check Out</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
