<?php
/**
 * Report Controller
 * 
 * Generates operational reports with configurable date ranges:
 *   - Occupancy rates by room type
 *   - Revenue breakdown
 *   - Guest statistics
 *   - Room utilization
 * 
 * Access: Admin only
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @version    1.0.0
 */

requireAdmin();

$db = Database::getInstance();
$pageTitle = 'Reports';
$pageSubtitle = 'Analytics and insights';

$startDate = $_GET['start'] ?? date('Y-m-01'); // First of current month
$endDate = $_GET['end'] ?? date('Y-m-d');

// ─── Revenue by Date ────────────────────────────────────────────────────────
$revenueByDate = $db->prepare("
    SELECT DATE(created_at) as date, SUM(amount_paid) as revenue
    FROM invoices
    WHERE DATE(created_at) BETWEEN :start AND :end
    GROUP BY DATE(created_at)
    ORDER BY date
");
$revenueByDate->execute([':start' => $startDate, ':end' => $endDate]);
$revenueData = $revenueByDate->fetchAll();

// ─── Revenue by Room Type ───────────────────────────────────────────────────
$revenueByType = $db->prepare("
    SELECT rm.type, COUNT(*) as bookings, COALESCE(SUM(i.amount_paid), 0) as revenue
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.id
    LEFT JOIN invoices i ON i.reservation_id = r.id
    WHERE r.check_in_date BETWEEN :start AND :end
    GROUP BY rm.type
");
$revenueByType->execute([':start' => $startDate, ':end' => $endDate]);
$typeData = $revenueByType->fetchAll();

// ─── Room Occupancy ─────────────────────────────────────────────────────────
$roomStats = $db->query("
    SELECT type,
           COUNT(*) as total,
           SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
           SUM(CASE WHEN status = 'Occupied' THEN 1 ELSE 0 END) as occupied,
           SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
    FROM rooms GROUP BY type
")->fetchAll();

// ─── Top Guests ─────────────────────────────────────────────────────────────
$topGuests = $db->prepare("
    SELECT CONCAT(g.first_name, ' ', g.last_name) as name,
           COUNT(r.id) as stays,
           COALESCE(SUM(i.amount_paid), 0) as total_spent
    FROM guests g
    JOIN reservations r ON r.guest_id = g.id
    LEFT JOIN invoices i ON i.reservation_id = r.id
    WHERE r.check_in_date BETWEEN :start AND :end
    GROUP BY g.id, g.first_name, g.last_name
    ORDER BY total_spent DESC
    LIMIT 10
");
$topGuests->execute([':start' => $startDate, ':end' => $endDate]);
$topGuestsData = $topGuests->fetchAll();

// ─── Totals ──────────────────────────────────────────────────────────────────
$totalRevenue = $db->prepare("
    SELECT COALESCE(SUM(amount_paid), 0) as total FROM invoices WHERE DATE(created_at) BETWEEN :start AND :end
");
$totalRevenue->execute([':start' => $startDate, ':end' => $endDate]);
$periodRevenue = $totalRevenue->fetch()['total'];

$totalBookings = $db->prepare("
    SELECT COUNT(*) as total FROM reservations WHERE check_in_date BETWEEN :start AND :end
");
$totalBookings->execute([':start' => $startDate, ':end' => $endDate]);
$periodBookings = $totalBookings->fetch()['total'];

require_once VIEWS_PATH . '/reports/index.php';
