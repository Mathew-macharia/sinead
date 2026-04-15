<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | <?php echo APP_NAME; ?></title>
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
                    <input type="text" class="search-input" placeholder="Search users..." data-table-search="usersTable">
                </div>
                <a href="<?php echo url('users', ['action' => 'create']); ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Create User
                </a>
            </div>

            <div class="card">
                <div class="table-container">
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td style="font-weight: 500; color: var(--text-primary);"><?php echo sanitize($u['username']); ?></td>
                                    <td><?php echo sanitize($u['full_name']); ?></td>
                                    <td class="text-muted"><?php echo sanitize($u['email'] ?? '--'); ?></td>
                                    <td><span class="badge badge-gold" style="text-transform: capitalize;"><?php echo sanitize($u['role']); ?></span></td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                            <span class="badge badge-dot badge-available">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-dot badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-sm"><?php echo $u['last_login'] ? formatDateTime($u['last_login']) : 'Never'; ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?php echo url('users', ['action' => 'edit', 'id' => $u['id']]); ?>" class="btn btn-ghost btn-sm">Edit</a>

                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle">More</button>
                                                <div class="dropdown-menu">
                                                    <form method="POST" action="<?php echo url('users', ['action' => 'toggle']); ?>">
                                                        <?php csrfField(); ?>
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="dropdown-item">
                                                            <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                        </button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <form method="POST" action="<?php echo url('users', ['action' => 'reset']); ?>">
                                                        <?php csrfField(); ?>
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="dropdown-item" data-confirm-delete="Reset password to default?">
                                                            Reset Password
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
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
</body>
</html>
