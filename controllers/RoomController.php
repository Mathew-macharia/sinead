<?php
/**
 * Room Controller
 * 
 * Handles CRUD operations for hotel rooms:
 *   - list:   Display all rooms with filtering (GET)
 *   - create: Add a new room (GET/POST)
 *   - edit:   Update room details (GET/POST)
 *   - delete: Remove a room (POST)
 *   - status: Update room status (POST)
 * 
 * Access: Admin and Receptionist roles
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @author     Sinead Development Team
 * @version    1.0.0
 */

requireFrontDesk();

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':
        handleCreateRoom($db);
        break;
    case 'edit':
        handleEditRoom($db);
        break;
    case 'delete':
        handleDeleteRoom($db);
        break;
    case 'status':
        handleStatusUpdate($db);
        break;
    case 'list':
    default:
        handleListRooms($db);
        break;
}

/**
 * Display the room listing with optional filtering.
 */
function handleListRooms(PDO $db): void
{
    $pageTitle = 'Room Management';
    $pageSubtitle = 'Manage hotel rooms and availability';

    // Filtering
    $statusFilter = $_GET['status'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';

    $where = [];
    $params = [];

    if ($statusFilter) {
        $where[] = 'status = :status';
        $params[':status'] = $statusFilter;
    }
    if ($typeFilter) {
        $where[] = 'type = :type';
        $params[':type'] = $typeFilter;
    }
    if ($search) {
        $where[] = '(room_number LIKE :search OR description LIKE :search2)';
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $db->prepare("SELECT * FROM rooms {$whereClause} ORDER BY room_number ASC");
    $stmt->execute($params);
    // Convert each DB row to a typed Room object via the factory.
    // Views can still use $room['room_number'] (ArrayAccess) AND call
    // $room->getAmenities(), $room->getMaxOccupancy(), etc.
    $rooms = array_map(
        fn($row) => RoomFactory::fromDbRow($row),
        $stmt->fetchAll()
    );

    // Room count by status
    $statusCounts = $db->query("
        SELECT status, COUNT(*) as count FROM rooms GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    require_once VIEWS_PATH . '/rooms/index.php';
}

/**
 * Handle room creation (display form and process submission).
 */
function handleCreateRoom(PDO $db): void
{
    requireAdmin(); // Only managers create rooms and set prices

    $pageTitle = 'Add New Room';
    $errors = [];
    $formData = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);

        // Validation
        $errors = validateRequired($formData, ['room_number', 'type', 'floor', 'price_per_night']);

        // Check for duplicate room number
        if (empty($errors)) {
            $check = $db->prepare('SELECT id FROM rooms WHERE room_number = :num');
            $check->execute([':num' => $formData['room_number']]);
            if ($check->fetch()) {
                $errors[] = 'A room with this number already exists.';
            }
        }

        if (empty($errors)) {
            $imagePath = processRoomImageUpload($errors, $formData['room_number']);
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO rooms (room_number, type, floor, price_per_night, status, description, image_path)
                    VALUES (:room_number, :type, :floor, :price, :status, :description, :image_path)
                ");
                $stmt->execute([
                    ':room_number' => $formData['room_number'],
                    ':type'        => $formData['type'],
                    ':floor'       => (int) $formData['floor'],
                    ':price'       => (float) $formData['price_per_night'],
                    ':status'      => $formData['status'] ?? 'Available',
                    ':description' => $formData['description'] ?? '',
                    ':image_path'  => $imagePath ?? null
                ]);

                logActivity('Room Created', "Room {$formData['room_number']} ({$formData['type']}) added.");
                setFlash('success', "Room {$formData['room_number']} has been created successfully.");
                redirect('rooms');
            } catch (PDOException $e) {
                error_log('Room creation error: ' . $e->getMessage());
                $errors[] = 'Failed to create room. Please try again.';
            }
        }
    }

    require_once VIEWS_PATH . '/rooms/form.php';
}

/**
 * Handle room editing.
 */
function handleEditRoom(PDO $db): void
{
    requireAdmin(); // Only managers edit rooms or change rates

    $roomId = (int) ($_GET['id'] ?? 0);
    $pageTitle = 'Edit Room';
    $errors = [];

    // Fetch existing room
    $stmt = $db->prepare('SELECT * FROM rooms WHERE id = :id');
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        setFlash('error', 'Room not found.');
        redirect('rooms');
        return;
    }

    // Build the typed Room object so the view can call $roomObj->getAmenities() etc.
    $roomObj  = RoomFactory::fromDbRow($room);
    $formData = $room;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);

        $errors = validateRequired($formData, ['room_number', 'type', 'floor', 'price_per_night']);

        // Check unique room number (exclude current)
        if (empty($errors)) {
            $check = $db->prepare('SELECT id FROM rooms WHERE room_number = :num AND id != :id');
            $check->execute([':num' => $formData['room_number'], ':id' => $roomId]);
            if ($check->fetch()) {
                $errors[] = 'Another room with this number already exists.';
            }
        }

        if (empty($errors)) {
            $newImagePath = processRoomImageUpload($errors, $formData['room_number']);
        }

        if (empty($errors)) {
            // Use newly uploaded image, or keep the existing one
            $imagePath = $newImagePath ?? ($room['image_path'] ?? null);

            try {
                $stmt = $db->prepare("
                    UPDATE rooms SET
                        room_number = :room_number, type = :type, floor = :floor,
                        price_per_night = :price, status = :status, description = :description,
                        image_path = :image_path
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':room_number' => $formData['room_number'],
                    ':type'        => $formData['type'],
                    ':floor'       => (int) $formData['floor'],
                    ':price'       => (float) $formData['price_per_night'],
                    ':status'      => $formData['status'],
                    ':description' => $formData['description'] ?? '',
                    ':image_path'  => $imagePath,
                    ':id'          => $roomId
                ]);

                // Delete old image file if a new one was uploaded
                if ($newImagePath && !empty($room['image_path'])) {
                    $oldFile = ASSETS_PATH . '/' . $room['image_path'];
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }

                logActivity('Room Updated', "Room {$formData['room_number']} updated.");
                setFlash('success', "Room {$formData['room_number']} has been updated.");
                redirect('rooms');
            } catch (PDOException $e) {
                error_log('Room update error: ' . $e->getMessage());
                $errors[] = 'Failed to update room. Please try again.';
            }
        }
    }

    require_once VIEWS_PATH . '/rooms/form.php';
}

/**
 * Validate and save an uploaded room image.
 * Returns the relative asset path on success, null if no file was uploaded, or sets $errors on failure.
 */
function processRoomImageUpload(array &$errors, string $roomNumber): ?string
{
    if (empty($_FILES['room_image']['name'])) {
        return null; // no file chosen — caller keeps existing value
    }

    $file = $_FILES['room_image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed (error code ' . $file['error'] . ').';
        return null;
    }

    if ($file['size'] > ROOM_IMAGE_MAX_SIZE) {
        $errors[] = 'Image must be 2 MB or smaller.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ROOM_IMAGE_ALLOWED_TYPES, true)) {
        $errors[] = 'Only JPG, PNG, and WebP images are allowed.';
        return null;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'room-' . preg_replace('/[^a-z0-9]/i', '', $roomNumber) . '-' . uniqid() . '.' . strtolower($ext);
    $destPath = ROOM_IMAGES_PATH . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Could not save the uploaded image. Check folder permissions.';
        return null;
    }

    return ROOM_IMAGES_URL . '/' . $filename;
}

/**
 * Handle room deletion (POST only, with validation).
 */
function handleDeleteRoom(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('rooms');
        return;
    }

    verifyCsrf();
    requireAdmin();

    $roomId = (int) ($_POST['room_id'] ?? 0);

    $roomStmt = $db->prepare('SELECT room_number FROM rooms WHERE id = :id');
    $roomStmt->execute([':id' => $roomId]);
    $room = $roomStmt->fetch();

    if (!$room) {
        setFlash('error', 'Room not found.');
        redirect('rooms');
        return;
    }

    try {
        // trg_block_room_delete fires BEFORE DELETE and raises SQLSTATE 45000
        // if active reservations exist — PDO surfaces it as a PDOException
        // whose message is the trigger's SET MESSAGE_TEXT string.
        $db->prepare('DELETE FROM rooms WHERE id = :id')->execute([':id' => $roomId]);
        logActivity('Room Deleted', "Room {$room['room_number']} deleted.");
        setFlash('success', "Room {$room['room_number']} has been deleted.");
    } catch (PDOException $e) {
        error_log('Room deletion error: ' . $e->getMessage());
        setFlash('error', $e->getMessage() ?: 'Failed to delete room.');
    }

    redirect('rooms');
}

/**
 * Handle room status update via POST.
 */
function handleStatusUpdate(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('rooms');
        return;
    }

    verifyCsrf();
    requireAdmin(); // Receptionists cannot manually override room status
                    // Room status changes only via check-in / check-out actions

    $roomId    = (int) ($_POST['room_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, ROOM_STATUSES, true)) {
        setFlash('error', 'Invalid room status.');
        redirect('rooms');
        return;
    }

    try {
        $db->prepare('UPDATE rooms SET status = :status WHERE id = :id')
           ->execute([':status' => $newStatus, ':id' => $roomId]);

        $room = $db->prepare('SELECT room_number FROM rooms WHERE id = :id');
        $room->execute([':id' => $roomId]);
        $roomData = $room->fetch();

        logActivity('Room Status Changed', "Room {$roomData['room_number']} status changed to {$newStatus}.");
        setFlash('success', "Room {$roomData['room_number']} status updated to {$newStatus}.");
    } catch (PDOException $e) {
        error_log('Room status update error: ' . $e->getMessage());
        setFlash('error', 'Failed to update room status.');
    }

    redirect('rooms');
}
