<?php
/**
 * Housekeeping Controller
 *
 * Manages housekeeping task assignments and tracking.
 * Implements a Kanban-style workflow: Pending -> InProgress -> Completed.
 * Automatically creates tasks on guest check-out.
 *
 * Access: Admin and Housekeeping roles
 *
 * Role rules:
 *   - Admin       : Sees ALL tasks, can create/update/delete any task
 *   - Housekeeping: Sees ONLY their personally assigned tasks, can update ONLY their own tasks
 *
 * Room availability link:
 *   When a 'Cleaning' task is marked 'Completed', the linked room is automatically
 *   set back to 'Available'. This is the ONLY way a room returns to Available after checkout.
 *
 * @package    Sinead
 * @subpackage Controllers
 * @version    2.0.0
 */

requireRole([ROLE_ADMIN, ROLE_HOUSEKEEPING]);

$db     = Database::getInstance();
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':  handleCreateTask($db);  break;
    case 'update':  handleUpdateTask($db);  break;
    case 'delete':  handleDeleteTask($db);  break;
    default:        handleListTasks($db);   break;
}

function handleListTasks(PDO $db): void
{
    $pageTitle    = 'Housekeeping';
    $pageSubtitle = 'Task management board';
    $userId       = currentUser('id');
    $isAdmin      = hasRole(ROLE_ADMIN);

    // Admin sees ALL tasks; housekeeping staff sees ONLY their assigned tasks
    if ($isAdmin) {
        $allTasks = $db->query("
            SELECT ht.*, rm.room_number, rm.type as room_type, rm.floor,
                   u.full_name as assigned_name
            FROM housekeeping_tasks ht
            JOIN rooms rm  ON ht.room_id    = rm.id
            LEFT JOIN users u ON ht.assigned_to = u.id
            ORDER BY
                FIELD(ht.priority, 'High', 'Medium', 'Low'),
                ht.created_at DESC
        ")->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT ht.*, rm.room_number, rm.type as room_type, rm.floor,
                   u.full_name as assigned_name
            FROM housekeeping_tasks ht
            JOIN rooms rm  ON ht.room_id    = rm.id
            LEFT JOIN users u ON ht.assigned_to = u.id
            WHERE ht.assigned_to = :uid
            ORDER BY
                FIELD(ht.priority, 'High', 'Medium', 'Low'),
                ht.created_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $allTasks = $stmt->fetchAll();
    }

    $tasksByStatus = [
        'Pending'    => [],
        'InProgress' => [],
        'Completed'  => []
    ];
    foreach ($allTasks as $task) {
        $tasksByStatus[$task['status']][] = $task;
    }

    // Load rooms and staff for the create form (admin only)
    $rooms = $isAdmin ? $db->query("SELECT id, room_number, type FROM rooms ORDER BY room_number")->fetchAll() : [];
    $staff = $isAdmin ? $db->query("SELECT id, full_name FROM users WHERE role = 'housekeeping' AND is_active = 1")->fetchAll() : [];

    require_once VIEWS_PATH . '/housekeeping/index.php';
}

function handleCreateTask(PDO $db): void
{
    requireAdmin(); // Only admin can create and assign tasks

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('housekeeping'); return; }
    verifyCsrf();

    $formData = sanitizeArray($_POST);
    $errors   = validateRequired($formData, ['room_id', 'task_type', 'priority']);

    if (!empty($errors)) {
        setFlash('error', implode(' ', $errors));
        redirect('housekeeping');
        return;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO housekeeping_tasks (room_id, assigned_to, task_type, status, priority, notes)
            VALUES (:room, :assigned, :type, 'Pending', :priority, :notes)
        ");
        $stmt->execute([
            ':room'     => $formData['room_id'],
            ':assigned' => !empty($formData['assigned_to']) ? $formData['assigned_to'] : null,
            ':type'     => $formData['task_type'],
            ':priority' => $formData['priority'],
            ':notes'    => $formData['notes'] ?? ''
        ]);

        logActivity('Task Created', "Housekeeping task created for room ID {$formData['room_id']}.");
        setFlash('success', 'Housekeeping task created.');
    } catch (PDOException $e) {
        error_log('Task creation error: ' . $e->getMessage());
        setFlash('error', 'Failed to create task.');
    }

    redirect('housekeeping');
}

function handleUpdateTask(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('housekeeping'); return; }
    verifyCsrf();

    $taskId    = (int)($_POST['task_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (!in_array($newStatus, TASK_STATUSES, true)) {
        setFlash('error', 'Invalid task status.');
        redirect('housekeeping');
        return;
    }

    // Verify the task exists and belongs to the current user (unless admin)
    $taskStmt = $db->prepare("SELECT * FROM housekeeping_tasks WHERE id = :id");
    $taskStmt->execute([':id' => $taskId]);
    $task = $taskStmt->fetch();

    if (!$task) {
        setFlash('error', 'Task not found.');
        redirect('housekeeping');
        return;
    }

    // Housekeeping staff can only update tasks assigned to them
    if (!hasRole(ROLE_ADMIN) && (int)$task['assigned_to'] !== (int)currentUser('id')) {
        setFlash('error', 'You can only update tasks that are assigned to you.');
        redirect('housekeeping');
        return;
    }

    try {
        $completedAt = $newStatus === 'Completed' ? date('Y-m-d H:i:s') : null;
        $db->prepare("
            UPDATE housekeeping_tasks SET status = :status, completed_at = :completed WHERE id = :id
        ")->execute([':status' => $newStatus, ':completed' => $completedAt, ':id' => $taskId]);

        // When a Cleaning task is completed, the room is now ready — set it Available
        if ($newStatus === 'Completed' && $task['task_type'] === 'Cleaning') {
            $db->prepare("UPDATE rooms SET status = 'Available' WHERE id = :rid")
               ->execute([':rid' => $task['room_id']]);
            logActivity('Room Available', "Room ID {$task['room_id']} set to Available after cleaning task completed.");
        }

        logActivity('Task Updated', "Housekeeping task #{$taskId} status changed to {$newStatus}.");
        setFlash('success', 'Task status updated.');
    } catch (PDOException $e) {
        setFlash('error', 'Failed to update task.');
    }

    redirect('housekeeping');
}

function handleDeleteTask(PDO $db): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('housekeeping'); return; }
    verifyCsrf();
    requireAdmin();

    $taskId = (int)($_POST['task_id'] ?? 0);
    try {
        $db->prepare('DELETE FROM housekeeping_tasks WHERE id = :id')->execute([':id' => $taskId]);
        logActivity('Task Deleted', "Housekeeping task #{$taskId} deleted.");
        setFlash('success', 'Task deleted.');
    } catch (PDOException $e) {
        setFlash('error', 'Failed to delete task.');
    }
    redirect('housekeeping');
}
