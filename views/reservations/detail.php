<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation #<?php echo $reservation['id']; ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="d-flex align-center justify-between mb-lg">
                <div>
                    <a href="<?php echo url('reservations'); ?>" class="btn btn-ghost btn-sm mb-sm">&larr; Back to Reservations</a>
                </div>
                <span class="badge badge-dot badge-<?php echo strtolower($reservation['status']); ?>" style="font-size: 0.875rem; padding: 0.375rem 1rem;">
                    <?php echo sanitize($reservation['status']); ?>
                </span>
            </div>

            <div class="detail-grid">
                <div>
                    <!-- Guest Information -->
                    <div class="card mb-lg">
                        <div class="card-header">
                            <h3>Guest Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Name</span>
                                <span class="detail-value"><?php echo sanitize($reservation['guest_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo sanitize($reservation['phone']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo sanitize($reservation['email'] ?? '--'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">ID Document</span>
                                <span class="detail-value"><?php echo sanitize($reservation['id_document'] ?? '--'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Room & Stay Details -->
                    <div class="card mb-lg">
                        <div class="card-header">
                            <h3>Stay Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Room</span>
                                <span class="detail-value">Room <?php echo sanitize($reservation['room_number']); ?> (<?php echo sanitize($reservation['room_type']); ?>, Floor <?php echo $reservation['floor']; ?>)</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Check-in</span>
                                <span class="detail-value"><?php echo formatDate($reservation['check_in_date']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Check-out</span>
                                <span class="detail-value"><?php echo formatDate($reservation['check_out_date']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Duration</span>
                                <span class="detail-value"><?php echo $reservation['nights']; ?> night(s)</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Number of Guests</span>
                                <span class="detail-value"><?php echo $reservation['num_guests']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Rate per Night</span>
                                <span class="detail-value"><?php echo formatCurrency($reservation['price_per_night']); ?></span>
                            </div>
                            <div class="detail-row" style="border-top: 2px solid var(--accent-gold); padding-top: var(--space-md); margin-top: var(--space-sm);">
                                <span class="detail-label" style="font-weight: 600; color: var(--text-primary);">Estimated Total</span>
                                <span class="detail-value" style="color: var(--accent-gold); font-size: 1.25rem;"><?php echo formatCurrency($reservation['nights'] * $reservation['price_per_night']); ?></span>
                            </div>
                            <?php if ($reservation['notes']): ?>
                            <div style="margin-top: var(--space-md); padding: var(--space-md); background: var(--bg-surface); border-radius: var(--radius-md);">
                                <div class="label mb-sm">Notes</div>
                                <p style="margin: 0; color: var(--text-secondary);"><?php echo nl2br(sanitize($reservation['notes'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Invoice Section -->
                    <?php if ($invoice): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Invoice</h3>
                            <a href="<?php echo url('billing', ['action' => 'view', 'id' => $invoice['id']]); ?>" class="btn btn-ghost btn-sm">View Invoice</a>
                        </div>
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Invoice Number</span>
                                <span class="detail-value"><?php echo sanitize($invoice['invoice_number']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Amount</span>
                                <span class="detail-value"><?php echo formatCurrency($invoice['total_amount']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Amount Paid</span>
                                <span class="detail-value"><?php echo formatCurrency($invoice['amount_paid']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment Status</span>
                                <span class="badge badge-<?php echo strtolower($invoice['status']); ?>"><?php echo sanitize($invoice['status']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Sidebar -->
                <div>
                    <div class="card">
                        <div class="card-header"><h3>Actions</h3></div>
                        <div class="card-body">
                            <?php if ($reservation['status'] === 'Confirmed'): ?>
                                <form method="POST" action="<?php echo url('reservations', ['action' => 'checkin']); ?>">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-block mb-sm">Check In Guest</button>
                                </form>
                                <form method="POST" action="<?php echo url('reservations', ['action' => 'cancel']); ?>">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-block" data-confirm-delete="Are you sure you want to cancel this reservation?">Cancel Reservation</button>
                                </form>
                            <?php elseif ($reservation['status'] === 'CheckedIn'): ?>
                                <form method="POST" action="<?php echo url('reservations', ['action' => 'checkout']); ?>">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-block mb-sm">Check Out Guest</button>
                                </form>
                                <p class="text-sm text-muted mt-sm">Check-out will generate an invoice and create a housekeeping task for the room.</p>
                            <?php elseif ($reservation['status'] === 'CheckedOut'): ?>
                                <?php if ($invoice): ?>
                                    <a href="<?php echo url('billing', ['action' => 'view', 'id' => $invoice['id']]); ?>" class="btn btn-primary btn-block mb-sm">View Invoice</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted text-sm">This reservation has been <?php echo strtolower($reservation['status']); ?>. No actions available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mt-md">
                        <div class="card-body">
                            <div class="detail-row">
                                <span class="detail-label">Created by</span>
                                <span class="detail-value text-sm"><?php echo sanitize($reservation['created_by_name'] ?? 'System'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Created at</span>
                                <span class="detail-value text-sm"><?php echo formatDateTime($reservation['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
