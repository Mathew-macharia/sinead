<?php
/**
 * Reservation Controller
 * 
 * Manages the reservation lifecycle:
 *   - Create new reservation (walk-in / pre-booked)
 *   - View reservation details
 *   - Check-in (Confirmed -> CheckedIn, room -> Occupied)
 *   - Check-out (CheckedIn -> CheckedOut, room -> Available, generate invoice)
 *   - Cancel reservation
 * 
 * Implements real-time room availability validation.
 * Automatically updates room status on check-in/check-out.
 * 
 * Access: Admin and Receptionist roles
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @version    1.0.0
 */

requireFrontDesk();

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':  handleCreateReservation($db); break;
    case 'view':    handleViewReservation($db); break;
    case 'checkin': handleCheckIn($db); break;
    case 'checkout':handleCheckOut($db); break;
    case 'cancel':  handleCancel($db); break;
    default:        handleListReservations($db); break;
}

function handleListReservations(PDO $db): void
{
    $pageTitle = 'Reservations';
    $pageSubtitle = 'Manage bookings and check-ins';
    $statusFilter = $_GET['status'] ?? '';

    $where = '';
    $params = [];
    if ($statusFilter) {
        $where = 'WHERE r.status = :status';
        $params[':status'] = $statusFilter;
    }

    $stmt = $db->prepare("
        SELECT r.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name, g.phone as guest_phone,
               rm.room_number, rm.type as room_type, rm.price_per_night,
               DATEDIFF(r.check_out_date, r.check_in_date) as nights
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        {$where}
        ORDER BY r.created_at DESC
    ");
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();

    // Status counts
    $statusCounts = $db->query("SELECT status, COUNT(*) as c FROM reservations GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

    require_once VIEWS_PATH . '/reservations/index.php';
}

function handleCreateReservation(PDO $db): void
{
    $pageTitle = 'New Reservation';
    $errors = [];
    $formData = $_POST ?: [];

    // Load guests for dropdown
    $guests = $db->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, phone FROM guests ORDER BY first_name")->fetchAll();

    // Load available rooms
    $availableRooms = $db->query("SELECT * FROM rooms WHERE status = 'Available' ORDER BY room_number")->fetchAll();

    // Pre-select guest if passed
    $selectedGuest = $_GET['guest_id'] ?? ($formData['guest_id'] ?? '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);
        $errors = validateRequired($formData, ['guest_id', 'room_id', 'check_in_date', 'check_out_date']);

        // Validate dates
        if (empty($errors)) {
            $checkIn = $formData['check_in_date'];
            $checkOut = $formData['check_out_date'];
            if ($checkOut <= $checkIn) {
                $errors[] = 'Check-out date must be after check-in date.';
            }
        }

        // Validate room availability
        if (empty($errors)) {
            $roomCheck = $db->prepare("
                SELECT COUNT(*) as c FROM reservations 
                WHERE room_id = :room AND status IN ('Confirmed','CheckedIn')
                AND check_in_date < :out AND check_out_date > :in
            ");
            $roomCheck->execute([
                ':room' => $formData['room_id'],
                ':in'   => $formData['check_in_date'],
                ':out'  => $formData['check_out_date']
            ]);
            if ($roomCheck->fetch()['c'] > 0) {
                $errors[] = 'This room is already booked for the selected dates.';
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO reservations (guest_id, room_id, created_by, check_in_date, check_out_date, num_guests, status, notes)
                    VALUES (:guest, :room, :user, :in, :out, :num, 'Confirmed', :notes)
                ");
                $stmt->execute([
                    ':guest' => $formData['guest_id'],
                    ':room'  => $formData['room_id'],
                    ':user'  => currentUser('id'),
                    ':in'    => $formData['check_in_date'],
                    ':out'   => $formData['check_out_date'],
                    ':num'   => (int)($formData['num_guests'] ?? 1),
                    ':notes' => $formData['notes'] ?? ''
                ]);

                $resId = $db->lastInsertId();
                logActivity('Reservation Created', "Reservation #{$resId} created.");
                setFlash('success', 'Reservation created successfully.');
                redirect('reservations');
            } catch (PDOException $e) {
                error_log('Reservation error: ' . $e->getMessage());
                $errors[] = 'Failed to create reservation.';
            }
        }
    }

    require_once VIEWS_PATH . '/reservations/create.php';
}

function handleViewReservation(PDO $db): void
{
    $resId = (int)($_GET['id'] ?? 0);
    $pageTitle = 'Reservation Details';

    $stmt = $db->prepare("
        SELECT r.*, 
               CONCAT(g.first_name, ' ', g.last_name) as guest_name, g.phone, g.email, g.id_document,
               rm.room_number, rm.type as room_type, rm.price_per_night, rm.floor,
               DATEDIFF(r.check_out_date, r.check_in_date) as nights,
               u.full_name as created_by_name
        FROM reservations r
        JOIN guests g ON r.guest_id = g.id
        JOIN rooms rm ON r.room_id = rm.id
        LEFT JOIN users u ON r.created_by = u.id
        WHERE r.id = :id
    ");
    $stmt->execute([':id' => $resId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('error', 'Reservation not found.');
        redirect('reservations');
        return;
    }

    // Check for existing invoice
    $invoiceStmt = $db->prepare("SELECT * FROM invoices WHERE reservation_id = :id");
    $invoiceStmt->execute([':id' => $resId]);
    $invoice = $invoiceStmt->fetch();

    require_once VIEWS_PATH . '/reservations/detail.php';
}

function handleCheckIn(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('reservations'); return; }
    verifyCsrf();

    $resId = (int)($_POST['reservation_id'] ?? 0);

    try {
        $db->beginTransaction();

        // Update reservation status
        $db->prepare("UPDATE reservations SET status = 'CheckedIn' WHERE id = :id AND status = 'Confirmed'")
           ->execute([':id' => $resId]);

        // Update room status to Occupied
        $db->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = (SELECT room_id FROM reservations WHERE id = :id)")
           ->execute([':id' => $resId]);

        $db->commit();
        logActivity('Guest Checked In', "Reservation #{$resId} checked in.");
        setFlash('success', 'Guest has been checked in successfully.');
    } catch (PDOException $e) {
        $db->rollBack();
        error_log('Check-in error: ' . $e->getMessage());
        setFlash('error', 'Failed to process check-in.');
    }

    redirect('reservations&action=view&id=' . $resId);
}

function handleCheckOut(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('reservations'); return; }
    verifyCsrf();

    $resId = (int)($_POST['reservation_id'] ?? 0);

    try {
        $db->beginTransaction();

        // Get reservation details for invoice
        $res = $db->prepare("
            SELECT r.*, rm.price_per_night, rm.room_number,
                   DATEDIFF(r.check_out_date, r.check_in_date) as nights
            FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.id = :id
        ");
        $res->execute([':id' => $resId]);
        $reservation = $res->fetch();

        // Update reservation
        $db->prepare("UPDATE reservations SET status = 'CheckedOut' WHERE id = :id AND status = 'CheckedIn'")
           ->execute([':id' => $resId]);

        // Update room to Maintenance (not Available) — room must be cleaned first.
        // HousekeepingController will set it back to Available when the cleaning task is completed.
        $db->prepare("UPDATE rooms SET status = 'Maintenance' WHERE id = :rid")
           ->execute([':rid' => $reservation['room_id']]);

        // Generate invoice
        $nights = max(1, $reservation['nights']);
        $total = $nights * $reservation['price_per_night'];
        $invoiceNum = 'INV-' . date('Ymd') . '-' . str_pad($resId, 4, '0', STR_PAD_LEFT);

        $db->prepare("
            INSERT INTO invoices (reservation_id, invoice_number, total_amount, status)
            VALUES (:res_id, :num, :total, 'Unpaid')
        ")->execute([':res_id' => $resId, ':num' => $invoiceNum, ':total' => $total]);

        $invoiceId = $db->lastInsertId();

        // Add invoice line items
        $db->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total)
            VALUES (:inv_id, :desc, :qty, :price, :total)
        ")->execute([
            ':inv_id' => $invoiceId,
            ':desc'   => "Room {$reservation['room_number']} - Accommodation",
            ':qty'    => $nights,
            ':price'  => $reservation['price_per_night'],
            ':total'  => $total
        ]);

        // Create housekeeping task for the room
        $db->prepare("
            INSERT INTO housekeeping_tasks (room_id, task_type, status, priority, notes)
            VALUES (:room, 'Cleaning', 'Pending', 'High', 'Post-checkout cleaning required')
        ")->execute([':room' => $reservation['room_id']]);

        $db->commit();
        logActivity('Guest Checked Out', "Reservation #{$resId} checked out. Invoice {$invoiceNum} generated.");
        setFlash('success', "Guest checked out. Invoice {$invoiceNum} has been generated.");
    } catch (PDOException $e) {
        $db->rollBack();
        error_log('Check-out error: ' . $e->getMessage());
        setFlash('error', 'Failed to process check-out.');
    }

    redirect('reservations&action=view&id=' . $resId);
}

function handleCancel(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('reservations'); return; }
    verifyCsrf();

    $resId = (int)($_POST['reservation_id'] ?? 0);

    // Fetch current status before doing anything
    $res = $db->prepare("SELECT room_id, status FROM reservations WHERE id = :id");
    $res->execute([':id' => $resId]);
    $reservation = $res->fetch();

    if (!$reservation) {
        setFlash('error', 'Reservation not found.');
        redirect('reservations');
        return;
    }

    // Block cancellation of a checked-in reservation — must be checked out first
    if ($reservation['status'] === 'CheckedIn') {
        setFlash('error', 'This guest is currently checked in. Please complete the checkout process to generate their invoice before closing this reservation.');
        redirect('reservations&action=view&id=' . $resId);
        return;
    }

    if ($reservation['status'] !== 'Confirmed') {
        setFlash('error', 'Only confirmed (upcoming) reservations can be cancelled.');
        redirect('reservations&action=view&id=' . $resId);
        return;
    }

    try {
        $db->beginTransaction();

        $db->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = :id AND status = 'Confirmed'")
           ->execute([':id' => $resId]);

        $db->commit();
        logActivity('Reservation Cancelled', "Reservation #{$resId} cancelled (was Confirmed).");
        setFlash('success', 'Reservation has been cancelled.');
    } catch (PDOException $e) {
        $db->rollBack();
        setFlash('error', 'Failed to cancel reservation.');
    }

    redirect('reservations');
}
