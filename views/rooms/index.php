<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" class="search-input" placeholder="Search rooms..." data-table-search="roomsTable" id="roomSearch">
                </div>

                <select class="filter-select" data-filter-status="roomsTable" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="Available"    <?php echo ($_GET['status'] ?? '') === 'Available'    ? 'selected' : ''; ?>>Available</option>
                    <option value="Occupied"     <?php echo ($_GET['status'] ?? '') === 'Occupied'     ? 'selected' : ''; ?>>Occupied</option>
                    <option value="Maintenance"  <?php echo ($_GET['status'] ?? '') === 'Maintenance'  ? 'selected' : ''; ?>>Maintenance</option>
                </select>

                <div class="view-toggle">
                    <button class="active" data-view="grid" id="btnGridView">Grid</button>
                    <button data-view="list" id="btnListView">List</button>
                </div>

                <?php if (hasRole(ROLE_ADMIN)): ?>
                <a href="<?php echo url('rooms', ['action' => 'create']); ?>" class="btn btn-primary" id="btnAddRoom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Room
                </a>
                <?php endif; ?>
            </div>

            <!-- Grid View -->
            <div id="gridView">
                <?php if (empty($rooms)): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                        <h3>No Rooms Found</h3>
                        <p>No rooms match the current filters. Try adjusting your search criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="rooms-grid">
                        <?php foreach ($rooms as $room): ?>
                            <?php
                                $statusClass = strtolower($room['status']);
                                $imageMap = [
                                    'Standard' => 'room-standard.png',
                                    'Deluxe'   => 'room-deluxe.png',
                                    'Suite'    => 'room-suite.png'
                                ];
                                $roomImage = !empty($room['image_path'])
                                    ? asset($room['image_path'])
                                    : asset('images/' . ($imageMap[$room['type']] ?? 'room-standard.png'));
                            ?>
                            <div class="room-card">
                                <img src="<?php echo $roomImage; ?>"
                                     alt="<?php echo sanitize($room['type']); ?> room"
                                     class="room-card-image"
                                     loading="lazy">
                                <div class="room-card-body">
                                    <div class="room-card-header">
                                        <span class="room-card-number">Room <?php echo sanitize($room['room_number']); ?></span>
                                        <span class="badge badge-dot badge-<?php echo $statusClass; ?>"><?php echo sanitize($room['status']); ?></span>
                                    </div>
                                    <div class="room-card-type"><?php echo sanitize($room['type']); ?> &middot; Floor <?php echo $room['floor']; ?></div>
                                    <div class="room-card-price">
                                        <?php echo formatCurrency($room['price_per_night']); ?> <span>/ night</span>
                                    </div>
                                </div>
                                <div class="room-card-footer">
                                    <!-- Status Quick Update -->
                                    <form method="POST" action="<?php echo url('rooms', ['action' => 'status']); ?>" style="display: inline;">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="filter-select" style="padding: 0.3rem 1.75rem 0.3rem 0.5rem; font-size: 0.75rem;">
                                            <?php foreach (ROOM_STATUSES as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo $room['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <?php if (hasRole(ROLE_ADMIN)): ?>
                                    <a href="<?php echo url('rooms', ['action' => 'edit', 'id' => $room['id']]); ?>" class="btn btn-ghost btn-sm">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- List View (hidden by default) -->
            <div id="listView" style="display: none;">
                <div class="table-container">
                    <table class="data-table" id="roomsTable">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Type</th>
                                <th>Floor</th>
                                <th>Price/Night</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--text-primary);"><?php echo sanitize($room['room_number']); ?></td>
                                    <td><?php echo sanitize($room['type']); ?></td>
                                    <td><?php echo $room['floor']; ?></td>
                                    <td><?php echo formatCurrency($room['price_per_night']); ?></td>
                                    <td><span class="badge badge-dot badge-<?php echo strtolower($room['status']); ?>"><?php echo sanitize($room['status']); ?></span></td>
                                    <td class="text-muted truncate" style="max-width: 200px;"><?php echo sanitize($room['description'] ?? ''); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <?php if (hasRole(ROLE_ADMIN)): ?>
                                            <a href="<?php echo url('rooms', ['action' => 'edit', 'id' => $room['id']]); ?>" class="btn btn-ghost btn-sm">Edit</a>
                                            <form method="POST" action="<?php echo url('rooms', ['action' => 'delete']); ?>" style="display: inline;">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                                <button type="submit" class="btn btn-ghost btn-sm text-danger" data-confirm-delete="Are you sure you want to delete Room <?php echo sanitize($room['room_number']); ?>?">Delete</button>
                                            </form>
                                            <?php endif; ?>
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
