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
               fn_nights(r.check_in_date, r.check_out_date) as nights
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

        // Validate room availability using stored function fn_is_room_available()
        if (empty($errors)) {
            $avail = $db->prepare(
                "SELECT fn_is_room_available(:room, :in, :out) AS available"
            );
            $avail->execute([
                ':room' => $formData['room_id'],
                ':in'   => $formData['check_in_date'],
                ':out'  => $formData['check_out_date'],
            ]);
            if (!$avail->fetch()['available']) {
                $errors[] = 'This room is already booked for the selected dates.';
            }
        }

        // Validate guest count against room type's max occupancy (Factory Pattern)
        if (empty($errors)) {
            $roomRow = $db->prepare("SELECT * FROM rooms WHERE id = :id");
            $roomRow->execute([':id' => $formData['room_id']]);
            $roomData = $roomRow->fetch();
            if ($roomData) {
                $roomObj   = RoomFactory::fromDbRow($roomData);
                $numGuests = (int) ($formData['num_guests'] ?? 1);
                if ($numGuests > $roomObj->getMaxOccupancy()) {
                    $errors[] = "This {$roomObj->getTypeLabel()} has a maximum occupancy of "
                        . "{$roomObj->getMaxOccupancy()} guest(s). You entered {$numGuests}.";
                }
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

                // Notify guest about their booking confirmation (Adapter Pattern)
                $guestStmt = $db->prepare(
                    "SELECT first_name, last_name, email, phone FROM guests WHERE id = :id"
                );
                $guestStmt->execute([':id' => $formData['guest_id']]);
                $guestInfo = $guestStmt->fetch();
                if ($guestInfo) {
                    $subject = 'Reservation Confirmed – ' . APP_NAME;
                    $message = "Dear {$guestInfo['first_name']},<br><br>"
                        . "Your reservation <strong>#{$resId}</strong> has been confirmed.<br>"
                        . "Check-in: <strong>{$formData['check_in_date']}</strong> &nbsp;|&nbsp; "
                        . "Check-out: <strong>{$formData['check_out_date']}</strong><br><br>"
                        . "We look forward to welcoming you.<br>" . APP_FULL_NAME;
                    makeNotifier()->notifyGuest($guestInfo, $subject, $message);
                }

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
               fn_nights(r.check_in_date, r.check_out_date) as nights,
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
        // sp_check_in validates the reservation state, updates reservations + rooms,
        // and returns an error string in @err (NULL on success).
        // The trg_reservation_status_change trigger also fires as an extra safety net.
        $db->prepare("CALL sp_check_in(:res_id, @err)")->execute([':res_id' => $resId]);
        $result = $db->query("SELECT @err AS error")->fetch();

        if ($result['error']) {
            setFlash('error', $result['error']);
        } else {
            logActivity('Guest Checked In', "Reservation #{$resId} checked in.");

            // Notify guest of successful check-in (Adapter Pattern)
            $guestRow = $db->prepare("
                SELECT g.first_name, g.last_name, g.email, g.phone, rm.room_number
                FROM reservations r
                JOIN guests g  ON r.guest_id = g.id
                JOIN rooms  rm ON r.room_id  = rm.id
                WHERE r.id = :id
            ");
            $guestRow->execute([':id' => $resId]);
            $guestInfo = $guestRow->fetch();
            if ($guestInfo) {
                $subject = 'Welcome to ' . APP_NAME . ' – Room ' . $guestInfo['room_number'];
                $message = "Dear {$guestInfo['first_name']},<br><br>"
                    . "You have been successfully checked in to "
                    . "<strong>Room {$guestInfo['room_number']}</strong>.<br>"
                    . "We hope you enjoy your stay. Our front desk is available 24 hours.<br><br>"
                    . APP_FULL_NAME;
                makeNotifier()->notifyGuest($guestInfo, $subject, $message);
            }

            setFlash('success', 'Guest has been checked in successfully.');
        }
    } catch (PDOException $e) {
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
        // sp_check_out handles all DB work atomically: reservation → CheckedOut,
        // room → Maintenance, invoice + line item creation, and housekeeping task
        // with room-type priority (mirrors the Factory Pattern's getHousekeepingPriority).
        // OUT parameters @invoice and @total are read back in a second query.
        $db->prepare("CALL sp_check_out(:res_id, @invoice, @total, @err)")
           ->execute([':res_id' => $resId]);

        $result = $db->query("SELECT @invoice AS invoice, @total AS total, @err AS error")->fetch();

        if ($result['error']) {
            setFlash('error', $result['error']);
        } else {
            $invoiceNum = $result['invoice'];
            $total      = $result['total'];

            logActivity('Guest Checked Out', "Reservation #{$resId} checked out. Invoice {$invoiceNum} generated.");

            // Notify guest of checkout and invoice (Adapter Pattern)
            $guestRow = $db->prepare("
                SELECT g.first_name, g.last_name, g.email, g.phone
                FROM reservations r JOIN guests g ON r.guest_id = g.id
                WHERE r.id = :id
            ");
            $guestRow->execute([':id' => $resId]);
            $guestInfo = $guestRow->fetch();
            if ($guestInfo) {
                $subject = 'Thank You for Your Stay – Invoice ' . $invoiceNum;
                $message = "Dear {$guestInfo['first_name']},<br><br>"
                    . "Thank you for choosing <strong>" . APP_NAME . "</strong>. "
                    . "We hope you had a wonderful stay.<br><br>"
                    . "Invoice <strong>{$invoiceNum}</strong> for "
                    . "<strong>KES " . number_format($total, 2) . "</strong> "
                    . "has been generated and is available at the front desk.<br><br>"
                    . "We look forward to seeing you again.<br>" . APP_FULL_NAME;
                makeNotifier()->notifyGuest($guestInfo, $subject, $message);
            }

            setFlash('success', "Guest checked out. Invoice {$invoiceNum} has been generated.");
        }
    } catch (PDOException $e) {
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

    try {
        // sp_cancel_reservation validates the status and cancels in one call.
        // The trg_reservation_status_change trigger sets the room back to Available.
        $db->prepare("CALL sp_cancel_reservation(:res_id, @err)")->execute([':res_id' => $resId]);
        $result = $db->query("SELECT @err AS error")->fetch();

        if ($result['error']) {
            setFlash('error', $result['error']);
            redirect('reservations&action=view&id=' . $resId);
            return;
        }

        logActivity('Reservation Cancelled', "Reservation #{$resId} cancelled.");

        // Notify guest of cancellation (Adapter Pattern)
        $guestRow = $db->prepare("
            SELECT g.first_name, g.last_name, g.email, g.phone
            FROM reservations r JOIN guests g ON r.guest_id = g.id
            WHERE r.id = :id
        ");
        $guestRow->execute([':id' => $resId]);
        $guestInfo = $guestRow->fetch();
        if ($guestInfo) {
            $subject = 'Reservation Cancelled – ' . APP_NAME;
            $message = "Dear {$guestInfo['first_name']},<br><br>"
                . "Your reservation <strong>#{$resId}</strong> has been cancelled.<br>"
                . "If you did not request this cancellation or need assistance, "
                . "please contact our front desk at <strong>" . HOTEL_PHONE . "</strong>.<br><br>"
                . APP_FULL_NAME;
            makeNotifier()->notifyGuest($guestInfo, $subject, $message);
        }

        setFlash('success', 'Reservation has been cancelled.');
    } catch (PDOException $e) {
        error_log('Cancel error: ' . $e->getMessage());
        setFlash('error', 'Failed to cancel reservation.');
    }

    redirect('reservations');
}
