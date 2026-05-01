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

                        <form method="POST" enctype="multipart/form-data" data-validate>
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

                            <div class="form-group">
                                <label for="room_image" class="form-label">Room Photo</label>
                                <?php if (!empty($formData['image_path'])): ?>
                                    <div style="margin-bottom: var(--space-sm);">
                                        <img id="currentRoomImage"
                                             src="<?php echo asset($formData['image_path']); ?>"
                                             alt="Current room photo"
                                             style="width: 100%; max-width: 320px; height: 180px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Current photo — upload a new one to replace it.</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="room_image" name="room_image" class="form-control"
                                       accept=".jpg,.jpeg,.png,.webp"
                                       onchange="previewRoomImage(this)">
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">JPG, PNG or WebP — max 2 MB. Leave empty to keep the current photo.</p>
                                <img id="roomImagePreview" src="" alt="Preview"
                                     style="display:none; margin-top: var(--space-sm); width: 100%; max-width: 320px; height: 180px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                            </div>

                            <div class="card-footer" style="padding: 0; border: none; margin-top: var(--space-lg);">
                                <a href="<?php echo url('rooms'); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="btnSaveRoom">
                                    <?php echo isset($room) ? 'Update Room' : 'Create Room'; ?>
                                </button>
                            </div>
                        </form>

                        <?php if (isset($roomObj)): ?>
                        <!-- Room type metadata from the Factory Pattern — read-only info panel -->
                        <div style="margin-top: var(--space-lg); padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
                            <h4 style="margin: 0 0 var(--space-sm); color: var(--text-primary); font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                                <?php echo sanitize($roomObj->getTypeLabel()); ?> — Type Specifications
                            </h4>
                            <div class="detail-row">
                                <span class="detail-label">Max Occupancy</span>
                                <span class="detail-value"><?php echo $roomObj->getMaxOccupancy(); ?> guest<?php echo $roomObj->getMaxOccupancy() > 1 ? 's' : ''; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Housekeeping Priority</span>
                                <span class="detail-value">
                                    <span class="badge badge-dot badge-<?php echo strtolower($roomObj->getHousekeepingPriority()); ?>">
                                        <?php echo sanitize($roomObj->getHousekeepingPriority()); ?>
                                    </span>
                                </span>
                            </div>
                            <div style="margin-top: var(--space-sm);">
                                <span class="detail-label" style="display: block; margin-bottom: var(--space-xs);">Included Amenities</span>
                                <div style="display: flex; flex-wrap: wrap; gap: var(--space-xs);">
                                    <?php foreach ($roomObj->getAmenities() as $amenity): ?>
                                        <span style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 4px; padding: 2px 8px; font-size: 0.75rem; color: var(--text-secondary);">
                                            <?php echo sanitize($amenity); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>
<script src="<?php echo asset('js/main.js'); ?>"></script>
<script>
function previewRoomImage(input) {
    var preview = document.getElementById('roomImagePreview');
    var current = document.getElementById('currentRoomImage');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (current) current.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
