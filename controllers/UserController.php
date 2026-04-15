<?php
/**
 * User Management Controller
 * 
 * Admin-only CRUD for managing system users.
 * Supports creating, editing, deactivating, and password resetting.
 * 
 * Access: Admin only
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @version    1.0.0
 */

requireAdmin();

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':  handleCreateUser($db); break;
    case 'edit':    handleEditUser($db); break;
    case 'toggle':  handleToggleUser($db); break;
    case 'reset':   handleResetPassword($db); break;
    default:        handleListUsers($db); break;
}

function handleListUsers(PDO $db): void
{
    $pageTitle = 'User Management';
    $pageSubtitle = 'Manage staff accounts';

    $users = $db->query("
        SELECT id, username, full_name, email, role, is_active, last_login, created_at
        FROM users ORDER BY created_at DESC
    ")->fetchAll();

    require_once VIEWS_PATH . '/users/index.php';
}

function handleCreateUser(PDO $db): void
{
    $pageTitle = 'Create User';
    $errors = [];
    $formData = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);
        $errors = validateRequired($formData, ['username', 'full_name', 'password', 'role']);

        // Check unique username
        if (empty($errors)) {
            $check = $db->prepare('SELECT id FROM users WHERE username = :u');
            $check->execute([':u' => $formData['username']]);
            if ($check->fetch()) {
                $errors[] = 'Username already exists.';
            }
        }

        // Password strength
        if (empty($errors) && strlen($formData['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO users (username, password_hash, full_name, email, role)
                    VALUES (:user, :pass, :name, :email, :role)
                ");
                $stmt->execute([
                    ':user'  => $formData['username'],
                    ':pass'  => password_hash($formData['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]),
                    ':name'  => $formData['full_name'],
                    ':email' => $formData['email'] ?? null,
                    ':role'  => $formData['role']
                ]);

                logActivity('User Created', "User '{$formData['username']}' created with role '{$formData['role']}'.");
                setFlash('success', "User '{$formData['username']}' created successfully.");
                redirect('users');
            } catch (PDOException $e) {
                error_log('User creation error: ' . $e->getMessage());
                $errors[] = 'Failed to create user.';
            }
        }
    }

    require_once VIEWS_PATH . '/users/form.php';
}

function handleEditUser(PDO $db): void
{
    $userId = (int)($_GET['id'] ?? 0);
    $pageTitle = 'Edit User';
    $errors = [];

    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlash('error', 'User not found.');
        redirect('users');
        return;
    }

    $formData = $user;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $formData = sanitizeArray($_POST);
        $errors   = validateRequired($formData, ['username', 'full_name', 'role']);

        // Block admin from changing their own role
        if ((int)$userId === (int)currentUser('id') && $formData['role'] !== $user['role']) {
            $errors[] = 'You cannot change your own role. Ask another administrator to do this.';
        }

        // Unique username (exclude current)
        if (empty($errors)) {
            $check = $db->prepare('SELECT id FROM users WHERE username = :u AND id != :id');
            $check->execute([':u' => $formData['username'], ':id' => $userId]);
            if ($check->fetch()) {
                $errors[] = 'Username already exists.';
            }
        }

        if (empty($errors)) {
            try {
                $sql = "UPDATE users SET username = :user, full_name = :name, email = :email, role = :role WHERE id = :id";
                $params = [
                    ':user'  => $formData['username'],
                    ':name'  => $formData['full_name'],
                    ':email' => $formData['email'] ?? null,
                    ':role'  => $formData['role'],
                    ':id'    => $userId
                ];

                // Update password only if provided
                if (!empty($formData['password'])) {
                    if (strlen($formData['password']) < 6) {
                        $errors[] = 'Password must be at least 6 characters.';
                    } else {
                        $sql = "UPDATE users SET username = :user, full_name = :name, email = :email, role = :role, password_hash = :pass WHERE id = :id";
                        $params[':pass'] = password_hash($formData['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
                    }
                }

                if (empty($errors)) {
                    $db->prepare($sql)->execute($params);
                    logActivity('User Updated', "User '{$formData['username']}' updated.");
                    setFlash('success', "User '{$formData['username']}' updated.");
                    redirect('users');
                }
            } catch (PDOException $e) {
                error_log('User update error: ' . $e->getMessage());
                $errors[] = 'Failed to update user.';
            }
        }
    }

    require_once VIEWS_PATH . '/users/form.php';
}

function handleToggleUser(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('users'); return; }
    verifyCsrf();

    $userId = (int)($_POST['user_id'] ?? 0);

    // Prevent deactivating self
    if ($userId === (int)currentUser('id')) {
        setFlash('error', 'You cannot deactivate your own account.');
        redirect('users');
        return;
    }

    // Prevent deactivating the last active admin
    $targetUser = $db->prepare('SELECT role, is_active FROM users WHERE id = :id');
    $targetUser->execute([':id' => $userId]);
    $target = $targetUser->fetch();

    if ($target && $target['role'] === 'admin' && $target['is_active']) {
        $activeAdminCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
        if ($activeAdminCount <= 1) {
            setFlash('error', 'Cannot deactivate the last active administrator. Promote another user to admin first.');
            redirect('users');
            return;
        }
    }

    try {
        $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id")->execute([':id' => $userId]);
        logActivity('User Toggled', "User ID {$userId} active status toggled.");
        setFlash('success', 'User status updated.');
    } catch (PDOException $e) {
        setFlash('error', 'Failed to update user status.');
    }
    redirect('users');
}

function handleResetPassword(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('users'); return; }
    verifyCsrf();

    $userId = (int)($_POST['user_id'] ?? 0);
    $defaultPassword = 'sinead2024';

    try {
        $hash = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $db->prepare("UPDATE users SET password_hash = :pass WHERE id = :id")
           ->execute([':pass' => $hash, ':id' => $userId]);

        logActivity('Password Reset', "Password reset for user ID {$userId}.");
        setFlash('success', "Password has been reset to the default: '{$defaultPassword}'.");
    } catch (PDOException $e) {
        setFlash('error', 'Failed to reset password.');
    }
    redirect('users');
}
