<?php
/**
 * Dashboard Controller
 *
 * Renders a role-specific operational dashboard.
 *
 * - Admin       : Full view — revenue, all reservations, activity log, room stats
 * - Receptionist: Operational view — room availability, today's schedule, today's reservations (NO revenue)
 * - Housekeeping : Task view — only their personally assigned pending tasks
 *
 * @package    Sinead
 * @subpackage Controllers
 * @version    2.0.0
 */

requireAuth();

$db = Database::getInstance();
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Operational Overview';

// ─── Always available: Room Statistics ───────────────────────────────────────
$roomStats = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Available'   THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'Occupied'    THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
    FROM rooms
")->fetch();

$occupancyRate = $roomStats['total'] > 0
    ? round(($roomStats['occupied'] / $roomStats['total']) * 100, 1)
    : 0;

$today = date('Y-m-d');

// ─── Admin only: Revenue data ─────────────────────────────────────────────────
$todayRevenue  = 0;
$revenueMonth  = 0;
$recentActivity = [];

if (hasRole(ROLE_ADMIN)) {
    $revenueToday = $db->prepare("
        SELECT COALESCE(SUM(amount_paid), 0) as total FROM invoices
        WHERE DATE(created_at) = :today
    ");
    $revenueToday->execute([':today' => $today]);
    $todayRevenue = $revenueToday->fetch()['total'];

    $revenueMonth = $db->query("
        SELECT COALESCE(SUM(amount_paid), 0) as total FROM invoices
        WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
    ")->fetch()['total'];

    $recentActivity = $db->query("
        SELECT a.*, u.full_name
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 6
    ")->fetchAll();
}

// ─── Admin + Receptionist: Today's schedule and reservations ─────────────────
$checkInsCount   = 0;
$checkOutsCount  = 0;
$recentReservations = [];

if (hasRole([ROLE_ADMIN, ROLE_RECEPTIONIST])) {
    $todayCheckIns = $db->prepare("
        SELECT COUNT(*) as count FROM reservations
        WHERE check_in_date = :today AND status IN ('Confirmed', 'CheckedIn')
    ");
    $todayCheckIns->execute([':today' => $today]);
    $checkInsCount = $todayCheckIns->fetch()['count'];

    $todayCheckOuts = $db->prepare("
        SELECT COUNT(*) as count FROM reservations
        WHERE check_out_date = :today AND status = 'CheckedIn'
    ");
    $todayCheckOuts->execute([':today' => $today]);
    $checkOutsCount = $todayCheckOuts->fetch()['count'];

    // Receptionist sees today's reservations only; Admin sees most recent 8
    if (hasRole(ROLE_ADMIN)) {
        $resQuery = $db->query("
            SELECT r.*,
                   CONCAT(g.first_name, ' ', g.last_name) as guest_name,
                   rm.room_number, rm.type as room_type
            FROM reservations r
            JOIN guests g  ON r.guest_id = g.id
            JOIN rooms rm  ON r.room_id  = rm.id
            ORDER BY r.created_at DESC
            LIMIT 8
        ");
    } else {
        // Receptionist: only today's arrivals and departures
        $resQuery = $db->prepare("
            SELECT r.*,
                   CONCAT(g.first_name, ' ', g.last_name) as guest_name,
                   rm.room_number, rm.type as room_type
            FROM reservations r
            JOIN guests g  ON r.guest_id = g.id
            JOIN rooms rm  ON r.room_id  = rm.id
            WHERE r.check_in_date = :today OR (r.check_out_date = :today2 AND r.status = 'CheckedIn')
            ORDER BY r.check_in_date ASC
            LIMIT 20
        ");
        $resQuery->execute([':today' => $today, ':today2' => $today]);
    }
    $recentReservations = $resQuery->fetchAll();
}

// ─── Housekeeping: pending task count (all roles) and assigned tasks (HK only) ─
$pendingTasks = $db->query("
    SELECT COUNT(*) as count FROM housekeeping_tasks WHERE status != 'Completed'
")->fetch()['count'];

$myTasks = [];
if (hasRole(ROLE_HOUSEKEEPING)) {
    $myTasksStmt = $db->prepare("
        SELECT ht.*, rm.room_number, rm.type as room_type, rm.floor
        FROM housekeeping_tasks ht
        JOIN rooms rm ON ht.room_id = rm.id
        WHERE ht.assigned_to = :uid AND ht.status != 'Completed'
        ORDER BY FIELD(ht.priority, 'High', 'Medium', 'Low'), ht.created_at DESC
    ");
    $myTasksStmt->execute([':uid' => currentUser('id')]);
    $myTasks = $myTasksStmt->fetchAll();
}

// ─── Render ───────────────────────────────────────────────────────────────────
require_once VIEWS_PATH . '/dashboard/index.php';
