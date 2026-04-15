<?php
/**
 * Guest Controller
 * 
 * Manages hotel guest records with CRUD operations.
 * Includes guest search, profile viewing, and stay history.
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
    case 'create':
        handleCreateGuest($db);
        break;
    case 'edit':
        handleEditGuest($db);
        break;
    case 'view':
        handleViewGuest($db);
        break;
    case 'delete':
        handleDeleteGuest($db);
        break;
    default:
        handleListGuests($db);
        break;
}

function handleListGuests(PDO $db): void
{
    $pageTitle = 'Guest Management';
    $pageSubtitle = 'Manage guest records';
    $search = $_GET['search'] ?? '';

    $params = [];
    $where = '';
    if ($search) {
        $where = "WHERE first_name LIKE :s1 OR last_name LIKE :s2 OR email LIKE :s3 OR phone LIKE :s4";
        $params = [':s1' => "%$search%", ':s2' => "%$search%", ':s3' => "%$search%", ':s4' => "%$search%"];
    }

    $stmt = $db->prepare("
        SELECT g.*, 
               (SELECT COUNT(*) FROM reservations WHERE guest_id = g.id) as total_stays,
               (SELECT MAX(check_out_date) FROM reservations WHERE guest_id = g.id) as last_visit
        FROM guests g {$where}
        ORDER BY g.created_at DESC
    ");
    $stmt->execute($params);
    $guests = $stmt->fetchAll();

    require_once VIEWS_PATH . '/guests/index.php';
}

function handleCreateGuest(PDO $db): void
{
    $pageTitle = 'Register New Guest';
    $errors = [];
    $formData = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);
        $errors = validateRequired($formData, ['first_name', 'last_name', 'phone']);

        if (!empty($formData['email']) && !validateEmail($formData['email'])) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO guests (first_name, last_name, email, phone, id_document, address)
                    VALUES (:first, :last, :email, :phone, :doc, :address)
                ");
                $stmt->execute([
                    ':first'   => $formData['first_name'],
                    ':last'    => $formData['last_name'],
                    ':email'   => $formData['email'] ?? null,
                    ':phone'   => $formData['phone'],
                    ':doc'     => $formData['id_document'] ?? null,
                    ':address' => $formData['address'] ?? null
                ]);

                $guestId = $db->lastInsertId();
                logActivity('Guest Registered', "Guest '{$formData['first_name']} {$formData['last_name']}' registered.");
                setFlash('success', 'Guest registered successfully.');

                // If coming from reservation flow, redirect back
                if (!empty($_GET['return']) && $_GET['return'] === 'reservation') {
                    header('Location: ' . url('reservations', ['action' => 'create', 'guest_id' => $guestId]));
                    exit;
                }
                redirect('guests');
            } catch (PDOException $e) {
                error_log('Guest creation error: ' . $e->getMessage());
                $errors[] = 'Failed to register guest.';
            }
        }
    }

    require_once VIEWS_PATH . '/guests/form.php';
}

function handleEditGuest(PDO $db): void
{
    $guestId = (int) ($_GET['id'] ?? 0);
    $pageTitle = 'Edit Guest';
    $errors = [];

    $stmt = $db->prepare('SELECT * FROM guests WHERE id = :id');
    $stmt->execute([':id' => $guestId]);
    $guest = $stmt->fetch();

    if (!$guest) {
        setFlash('error', 'Guest not found.');
        redirect('guests');
        return;
    }

    $formData = $guest;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);
        $errors = validateRequired($formData, ['first_name', 'last_name', 'phone']);

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    UPDATE guests SET 
                        first_name = :first, last_name = :last, email = :email,
                        phone = :phone, id_document = :doc, address = :address
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':first'   => $formData['first_name'],
                    ':last'    => $formData['last_name'],
                    ':email'   => $formData['email'] ?? null,
                    ':phone'   => $formData['phone'],
                    ':doc'     => $formData['id_document'] ?? null,
                    ':address' => $formData['address'] ?? null,
                    ':id'      => $guestId
                ]);

                logActivity('Guest Updated', "Guest '{$formData['first_name']} {$formData['last_name']}' updated.");
                setFlash('success', 'Guest information updated.');
                redirect('guests');
            } catch (PDOException $e) {
                error_log('Guest update error: ' . $e->getMessage());
                $errors[] = 'Failed to update guest.';
            }
        }
    }

    require_once VIEWS_PATH . '/guests/form.php';
}

function handleViewGuest(PDO $db): void
{
    $guestId = (int) ($_GET['id'] ?? 0);
    $pageTitle = 'Guest Profile';

    $stmt = $db->prepare('SELECT * FROM guests WHERE id = :id');
    $stmt->execute([':id' => $guestId]);
    $guest = $stmt->fetch();

    if (!$guest) {
        setFlash('error', 'Guest not found.');
        redirect('guests');
        return;
    }

    // Get stay history
    $stayHistory = $db->prepare("
        SELECT r.*, rm.room_number, rm.type as room_type, rm.price_per_night,
               DATEDIFF(r.check_out_date, r.check_in_date) as nights
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.guest_id = :id
        ORDER BY r.check_in_date DESC
    ");
    $stayHistory->execute([':id' => $guestId]);
    $stays = $stayHistory->fetchAll();

    // Total spend — financial data visible to admin only
    $totalSpent = null;
    if (hasRole(ROLE_ADMIN)) {
        $totalSpend = $db->prepare("
            SELECT COALESCE(SUM(i.amount_paid), 0) as total
            FROM invoices i
            JOIN reservations r ON i.reservation_id = r.id
            WHERE r.guest_id = :id
        ");
        $totalSpend->execute([':id' => $guestId]);
        $totalSpent = $totalSpend->fetch()['total'];
    }

    require_once VIEWS_PATH . '/guests/detail.php';
}

function handleDeleteGuest(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('guests'); return; }
    verifyCsrf();
    requireAdmin();

    $guestId = (int) ($_POST['guest_id'] ?? 0);

    $check = $db->prepare("SELECT COUNT(*) as c FROM reservations WHERE guest_id = :id AND status IN ('Confirmed','CheckedIn')");
    $check->execute([':id' => $guestId]);
    if ($check->fetch()['c'] > 0) {
        setFlash('error', 'Cannot delete a guest with active reservations.');
        redirect('guests');
        return;
    }

    try {
        $db->prepare('DELETE FROM guests WHERE id = :id')->execute([':id' => $guestId]);
        logActivity('Guest Deleted', "Guest ID {$guestId} deleted.");
        setFlash('success', 'Guest record deleted.');
    } catch (PDOException $e) {
        setFlash('error', 'Failed to delete guest. They may have existing reservations.');
    }
    redirect('guests');
}
