<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo sanitize($invoice['invoice_number']); ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="d-flex align-center justify-between mb-lg no-print">
                <a href="<?php echo url('billing'); ?>" class="btn btn-ghost btn-sm">&larr; Back to Billing</a>
                <div class="d-flex gap-sm">
                    <?php if ($invoice['status'] !== 'Paid'): ?>
                        <button class="btn btn-primary" data-modal-target="paymentModal">Record Payment</button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Print Invoice
                    </button>
                </div>
            </div>

            <!-- Invoice Document -->
            <div class="invoice-container">
                <div class="invoice-header-section">
                    <div class="invoice-brand">
                        <h2>SINEAD</h2>
                        <p style="color: var(--text-muted); font-size: 0.8125rem; margin-top: 0.25rem;">Integrated Hotel Management System</p>
                    </div>
                    <div class="invoice-info">
                        <div style="font-weight: 600; font-size: 1.125rem; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo sanitize($invoice['invoice_number']); ?></div>
                        <div style="font-size: 0.8125rem; color: var(--text-muted);">Date: <?php echo formatDate($invoice['created_at']); ?></div>
                        <div style="margin-top: 0.5rem;"><span class="badge badge-<?php echo strtolower($invoice['status']); ?>"><?php echo sanitize($invoice['status']); ?></span></div>
                    </div>
                </div>

                <div class="invoice-body">
                    <!-- Bill To -->
                    <div class="form-row mb-lg">
                        <div>
                            <h6 style="margin-bottom: var(--space-sm);">Bill To</h6>
                            <p style="font-weight: 500; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo sanitize($invoice['guest_name']); ?></p>
                            <p style="font-size: 0.8125rem; margin-bottom: 0.125rem;"><?php echo sanitize($invoice['guest_email'] ?? ''); ?></p>
                            <p style="font-size: 0.8125rem; margin-bottom: 0.125rem;"><?php echo sanitize($invoice['guest_phone']); ?></p>
                            <p style="font-size: 0.8125rem;"><?php echo sanitize($invoice['guest_address'] ?? ''); ?></p>
                        </div>
                        <div>
                            <h6 style="margin-bottom: var(--space-sm);">Stay Details</h6>
                            <p style="font-size: 0.8125rem; margin-bottom: 0.25rem;">Room <?php echo sanitize($invoice['room_number']); ?> (<?php echo sanitize($invoice['room_type']); ?>)</p>
                            <p style="font-size: 0.8125rem; margin-bottom: 0.25rem;">Check-in: <?php echo formatDate($invoice['check_in_date']); ?></p>
                            <p style="font-size: 0.8125rem; margin-bottom: 0.25rem;">Check-out: <?php echo formatDate($invoice['check_out_date']); ?></p>
                            <p style="font-size: 0.8125rem;">Guests: <?php echo $invoice['num_guests']; ?></p>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Unit Price</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lineItems as $item): ?>
                                <tr>
                                    <td><?php echo sanitize($item['description']); ?></td>
                                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right;"><?php echo formatCurrency($item['unit_price']); ?></td>
                                    <td style="text-align: right;"><?php echo formatCurrency($item['total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="invoice-total-section">
                        <div class="invoice-totals">
                            <div class="invoice-total-row">
                                <span>Subtotal</span>
                                <span><?php echo formatCurrency($invoice['total_amount']); ?></span>
                            </div>
                            <div class="invoice-total-row">
                                <span>Amount Paid</span>
                                <span style="color: var(--success);"><?php echo formatCurrency($invoice['amount_paid']); ?></span>
                            </div>
                            <div class="invoice-total-row grand-total">
                                <span>Balance Due</span>
                                <span><?php echo formatCurrency(max(0, $invoice['total_amount'] - $invoice['amount_paid'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($invoice['payment_method']): ?>
                    <div style="margin-top: var(--space-lg); font-size: 0.8125rem; color: var(--text-muted);">
                        Payment Method: <?php echo sanitize($invoice['payment_method']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Modal -->
            <?php if ($invoice['status'] !== 'Paid'): ?>
            <div class="modal-overlay" id="paymentModal">
                <div class="modal">
                    <div class="modal-header">
                        <h3>Record Payment</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <form method="POST" action="<?php echo url('billing', ['action' => 'pay']); ?>">
                        <?php csrfField(); ?>
                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Balance Due</label>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-gold); margin-bottom: var(--space-md);">
                                    <?php echo formatCurrency($invoice['total_amount'] - $invoice['amount_paid']); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="amount" class="form-label">Payment Amount (KES)</label>
                                <input type="number" id="amount" name="amount" class="form-control" 
                                       value="<?php echo $invoice['total_amount'] - $invoice['amount_paid']; ?>" 
                                       min="0.01" step="0.01" max="<?php echo $invoice['total_amount'] - $invoice['amount_paid']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select id="payment_method" name="payment_method" class="form-control" required>
                                    <?php foreach (PAYMENT_METHODS as $m): ?>
                                        <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
