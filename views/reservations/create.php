<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reservation | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <div style="max-width: 700px;">
                <div class="card">
                    <div class="card-header">
                        <h3>New Reservation</h3>
                        <a href="<?php echo url('reservations'); ?>" class="btn btn-ghost btn-sm">Back</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error"><div><?php foreach ($errors as $e) echo "<div>" . sanitize($e) . "</div>"; ?></div><button class="alert-close" onclick="this.parentElement.remove()">&times;</button></div>
                        <?php endif; ?>

                        <form method="POST" data-validate>
                            <?php csrfField(); ?>

                            <div class="form-group">
                                <label for="guest_id" class="form-label">Guest</label>
                                <div style="display: flex; gap: var(--space-sm);">
                                    <select id="guest_id" name="guest_id" class="form-control" required style="flex: 1;">
                                        <option value="">-- Select Guest --</option>
                                        <?php foreach ($guests as $g): ?>
                                            <option value="<?php echo $g['id']; ?>" <?php echo ($selectedGuest == $g['id']) ? 'selected' : ''; ?>>
                                                <?php echo sanitize($g['name']); ?> (<?php echo sanitize($g['phone']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="<?php echo url('guests', ['action' => 'create', 'return' => 'reservation']); ?>" class="btn btn-secondary" data-tooltip="Register new guest">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="room_id" class="form-label">Room</label>
                                <select id="room_id" name="room_id" class="form-control" required>
                                    <option value="">-- Select Available Room --</option>
                                    <?php foreach ($availableRooms as $rm): ?>
                                        <option value="<?php echo $rm['id']; ?>" <?php echo (($formData['room_id'] ?? '') == $rm['id']) ? 'selected' : ''; ?>>
                                            Room <?php echo sanitize($rm['room_number']); ?> - <?php echo sanitize($rm['type']); ?> (Floor <?php echo $rm['floor']; ?>) - <?php echo formatCurrency($rm['price_per_night']); ?>/night
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($availableRooms)): ?>
                                    <div class="form-text" style="color: var(--danger);">No rooms are currently available.</div>
                                <?php endif; ?>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="check_in_date" class="form-label">Check-in Date</label>
                                    <input type="date" id="check_in_date" name="check_in_date" class="form-control" 
                                           value="<?php echo $formData['check_in_date'] ?? date('Y-m-d'); ?>" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="check_out_date" class="form-label">Check-out Date</label>
                                    <input type="date" id="check_out_date" name="check_out_date" class="form-control" 
                                           value="<?php echo $formData['check_out_date'] ?? date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="num_guests" class="form-label">Number of Guests</label>
                                <input type="number" id="num_guests" name="num_guests" class="form-control" 
                                       value="<?php echo $formData['num_guests'] ?? 1; ?>" min="1" max="10" required>
                            </div>

                            <div class="form-group">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Special requests, preferences..."><?php echo sanitize($formData['notes'] ?? ''); ?></textarea>
                            </div>

                            <!-- Billing Preview -->
                            <div id="billingPreview" style="background: var(--bg-surface); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg);">
                                <h6 style="margin-bottom: var(--space-sm);">Billing Estimate</h6>
                                <div class="detail-row">
                                    <span class="detail-label">Nights</span>
                                    <span class="detail-value" id="estNights">1</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Estimated Total</span>
                                    <span class="detail-value" id="estTotal" style="color: var(--accent-gold);">--</span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm);">
                                <a href="<?php echo url('reservations'); ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Reservation</button>
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
<script>
    // Live billing estimate
    var roomPrices = {};
    <?php foreach ($availableRooms as $rm): ?>
    roomPrices[<?php echo $rm['id']; ?>] = <?php echo $rm['price_per_night']; ?>;
    <?php endforeach; ?>

    function updateEstimate() {
        var roomId = document.getElementById('room_id').value;
        var checkIn = document.getElementById('check_in_date').value;
        var checkOut = document.getElementById('check_out_date').value;

        if (roomId && checkIn && checkOut) {
            var nights = Math.max(1, Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24)));
            var price = roomPrices[roomId] || 0;
            var total = nights * price;

            document.getElementById('estNights').textContent = nights;
            document.getElementById('estTotal').textContent = formatCurrency(total);
        }
    }

    document.getElementById('room_id').addEventListener('change', updateEstimate);
    document.getElementById('check_in_date').addEventListener('change', updateEstimate);
    document.getElementById('check_out_date').addEventListener('change', updateEstimate);
    updateEstimate();
</script>
</body>
</html>
