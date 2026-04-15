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
                        <a href="<?php echo url('guests'); ?>" class="btn btn-ghost btn-sm">Back to Guests</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error"><div><?php foreach ($errors as $e) echo "<div>" . sanitize($e) . "</div>"; ?></div><button class="alert-close" onclick="this.parentElement.remove()">&times;</button></div>
                        <?php endif; ?>

                        <form method="POST" data-validate>
                            <?php csrfField(); ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo sanitize($formData['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo sanitize($formData['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo sanitize($formData['email'] ?? ''); ?>" placeholder="guest@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo sanitize($formData['phone'] ?? ''); ?>" required placeholder="+254...">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="id_document" class="form-label">ID / Passport Number</label>
                                <input type="text" id="id_document" name="id_document" class="form-control" value="<?php echo sanitize($formData['id_document'] ?? ''); ?>" placeholder="National ID or Passport">
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="2" placeholder="City, Country"><?php echo sanitize($formData['address'] ?? ''); ?></textarea>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-lg);">
                                <a href="<?php echo url('guests'); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?php echo isset($guest) ? 'Update Guest' : 'Register Guest'; ?></button>
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
