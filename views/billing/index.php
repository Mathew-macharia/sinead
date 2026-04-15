<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="stats-grid mb-lg">
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Total Collected</div>
                        <div class="stat-value" style="font-size: 1.5rem;"><?php echo formatCurrency($totalPaid); ?></div>
                    </div>
                    <div class="stat-icon success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Outstanding</div>
                        <div class="stat-value" style="font-size: 1.5rem;"><?php echo formatCurrency($summary['outstanding']); ?></div>
                    </div>
                    <div class="stat-icon danger"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">Total Invoices</div>
                        <div class="stat-value"><?php echo count($invoices); ?></div>
                    </div>
                    <div class="stat-icon gold"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></div>
                </div>
            </div>

            <div class="filter-bar">
                <div class="search-input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" class="search-input" placeholder="Search invoices..." data-table-search="invoiceTable">
                </div>
                <select class="filter-select" data-filter-status="invoiceTable">
                    <option value="">All Statuses</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                </select>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="invoiceTable">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Stay Dates</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr><td colspan="8" class="text-center p-lg text-muted">No invoices found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($inv['invoice_number']); ?></td>
                                        <td><?php echo sanitize($inv['guest_name']); ?></td>
                                        <td><?php echo sanitize($inv['room_number']); ?></td>
                                        <td class="text-sm"><?php echo formatDate($inv['check_in_date']) . ' - ' . formatDate($inv['check_out_date']); ?></td>
                                        <td><?php echo formatCurrency($inv['total_amount']); ?></td>
                                        <td><?php echo formatCurrency($inv['amount_paid']); ?></td>
                                        <td><span class="badge badge-<?php echo strtolower($inv['status']); ?>"><?php echo sanitize($inv['status']); ?></span></td>
                                        <td>
                                            <a href="<?php echo url('billing', ['action' => 'view', 'id' => $inv['id']]); ?>" class="btn btn-ghost btn-sm">View</a>
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
