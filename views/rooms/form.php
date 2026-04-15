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
                        <a href="<?php echo url('rooms'); ?>" class="btn btn-ghost btn-sm">Back to Rooms</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error" role="alert">
                                <div>
                                    <?php foreach ($errors as $err): ?>
                                        <div><?php echo sanitize($err); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" data-validate>
                            <?php csrfField(); ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="room_number" class="form-label">Room Number</label>
                                    <input type="text" id="room_number" name="room_number" class="form-control" 
                                           value="<?php echo sanitize($formData['room_number'] ?? ''); ?>" required placeholder="e.g., 101">
                                </div>
                                <div class="form-group">
                                    <label for="floor" class="form-label">Floor</label>
                                    <input type="number" id="floor" name="floor" class="form-control" 
                                           value="<?php echo (int)($formData['floor'] ?? 1); ?>" required min="1" max="50">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="type" class="form-label">Room Type</label>
                                    <select id="type" name="type" class="form-control" required>
                                        <?php foreach (ROOM_TYPES as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo ($formData['type'] ?? '') === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="price_per_night" class="form-label">Price per Night (KES)</label>
                                    <input type="number" id="price_per_night" name="price_per_night" class="form-control" 
                                           value="<?php echo (float)($formData['price_per_night'] ?? 0); ?>" required min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <?php foreach (ROOM_STATUSES as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo ($formData['status'] ?? 'Available') === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3" 
                                          placeholder="Room features and amenities..."><?php echo sanitize($formData['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="card-footer" style="padding: 0; border: none; margin-top: var(--space-lg);">
                                <a href="<?php echo url('rooms'); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="btnSaveRoom">
                                    <?php echo isset($room) ? 'Update Room' : 'Create Room'; ?>
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
