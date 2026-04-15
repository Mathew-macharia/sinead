<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Profile | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="detail-grid">
                <!-- Guest Info Panel -->
                <div>
                    <div class="card mb-lg">
                        <div class="card-header">
                            <h3><?php echo sanitize($guest['first_name'] . ' ' . $guest['last_name']); ?></h3>
                            <a href="<?php echo url('guests', ['action' => 'edit', 'id' => $guest['id']]); ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo sanitize($guest['email'] ?? '--'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo sanitize($guest['phone']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">ID Document</span>
                                <span class="detail-value"><?php echo sanitize($guest['id_document'] ?? '--'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Address</span>
                                <span class="detail-value"><?php echo sanitize($guest['address'] ?? '--'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Registered</span>
                                <span class="detail-value"><?php echo formatDateTime($guest['created_at']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Stay History -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Stay History</h3>
                            <span class="badge badge-gold"><?php echo count($stays); ?> stays</span>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Nights</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stays)): ?>
                                        <tr><td colspan="5" class="text-center p-lg text-muted">No stay history.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($stays as $stay): ?>
                                            <tr>
                                                <td style="font-weight: 500; color: var(--text-primary);">
                                                    <?php echo sanitize($stay['room_number']); ?>
                                                    <span class="text-muted text-sm">(<?php echo sanitize($stay['room_type']); ?>)</span>
                                                </td>
                                                <td><?php echo formatDate($stay['check_in_date']); ?></td>
                                                <td><?php echo formatDate($stay['check_out_date']); ?></td>
                                                <td><?php echo $stay['nights']; ?></td>
                                                <td><span class="badge badge-dot badge-<?php echo strtolower($stay['status']); ?>"><?php echo sanitize($stay['status']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div>
                    <?php if (hasRole(ROLE_ADMIN) && $totalSpent !== null): ?>
                    <div class="card mb-lg">
                        <div class="card-body">
                            <div class="stat-label">Total Spend</div>
                            <div class="stat-value" style="color: var(--accent-gold);"><?php echo formatCurrency($totalSpent); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card mb-lg">
                        <div class="card-body">
                            <div class="stat-label">Total Stays</div>
                            <div class="stat-value"><?php echo count($stays); ?></div>
                        </div>
                    </div>

                    <a href="<?php echo url('reservations', ['action' => 'create', 'guest_id' => $guest['id']]); ?>" class="btn btn-primary btn-block">
                        New Reservation
                    </a>

                    <a href="<?php echo url('guests'); ?>" class="btn btn-secondary btn-block mt-sm">
                        Back to Guests
                    </a>
                </div>
            </div>
        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
