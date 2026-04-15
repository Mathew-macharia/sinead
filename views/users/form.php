<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <div style="max-width: 640px;">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo sanitize($pageTitle); ?></h3>
                        <a href="<?php echo url('users'); ?>" class="btn btn-ghost btn-sm">Back to Users</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error"><div><?php foreach ($errors as $e) echo "<div>" . sanitize($e) . "</div>"; ?></div><button class="alert-close" onclick="this.parentElement.remove()">&times;</button></div>
                        <?php endif; ?>

                        <form method="POST" data-validate>
                            <?php csrfField(); ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" 
                                           value="<?php echo sanitize($formData['username'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" 
                                           value="<?php echo sanitize($formData['full_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo sanitize($formData['email'] ?? ''); ?>" placeholder="user@sinead.hotel">
                                </div>
                                <div class="form-group">
                                    <label for="role" class="form-label">Role</label>
                                    <select id="role" name="role" class="form-control" required>
                                        <?php foreach (USER_ROLES as $r): ?>
                                            <option value="<?php echo $r; ?>" <?php echo ($formData['role'] ?? '') === $r ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($r); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    Password <?php echo isset($user) ? '(leave blank to keep current)' : ''; ?>
                                </label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       placeholder="<?php echo isset($user) ? 'Leave blank to keep current' : 'Minimum 6 characters'; ?>"
                                       <?php echo !isset($user) ? 'required' : ''; ?>>
                                <div class="form-text">Minimum 6 characters. Stored securely using bcrypt hashing.</div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-lg);">
                                <a href="<?php echo url('users'); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo isset($user) ? 'Update User' : 'Create User'; ?>
                                </button>
                            </div>
                        </form>
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
