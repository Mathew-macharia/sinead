<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guests | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="filter-bar">
                <div class="search-input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" class="search-input" placeholder="Search guests by name, email, or phone..." data-table-search="guestsTable" id="guestSearch">
                </div>
                <a href="<?php echo url('guests', ['action' => 'create']); ?>" class="btn btn-primary" id="btnAddGuest">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Register Guest
                </a>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="guestsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>ID Document</th>
                                <th>Total Stays</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($guests)): ?>
                                <tr><td colspan="7" class="text-center p-lg text-muted">No guests found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($guests as $g): ?>
                                    <tr>
                                        <td style="font-weight: 500; color: var(--text-primary);">
                                            <a href="<?php echo url('guests', ['action' => 'view', 'id' => $g['id']]); ?>" style="color: var(--text-primary);">
                                                <?php echo sanitize($g['first_name'] . ' ' . $g['last_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo sanitize($g['email'] ?? '--'); ?></td>
                                        <td><?php echo sanitize($g['phone']); ?></td>
                                        <td class="text-muted"><?php echo sanitize($g['id_document'] ?? '--'); ?></td>
                                        <td><span class="badge badge-gold"><?php echo $g['total_stays']; ?></span></td>
                                        <td class="text-muted"><?php echo $g['last_visit'] ? formatDate($g['last_visit']) : '--'; ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="<?php echo url('guests', ['action' => 'view', 'id' => $g['id']]); ?>" class="btn btn-ghost btn-sm">View</a>
                                                <a href="<?php echo url('guests', ['action' => 'edit', 'id' => $g['id']]); ?>" class="btn btn-ghost btn-sm">Edit</a>
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
