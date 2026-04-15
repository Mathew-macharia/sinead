<?php
/**
 * Listing Controller (Public)
 * 
 * Displays available rooms to unauthenticated visitors.
 * This is the ONLY controller that does not require authentication.
 * Visitors can browse rooms and call to make a reservation.
 * 
 * Reuses:
 *   - Database::getInstance() singleton
 *   - rooms table schema
 *   - sanitize(), formatCurrency(), asset(), url() helpers
 *   - Existing room type images (room-standard.png, etc.)
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @author     Sinead Development Team
 * @version    1.0.0
 */

// ──────────────────────────────────────────────────────────────────────────────
// NO requireAuth() — this is a public-facing page
// ──────────────────────────────────────────────────────────────────────────────

$db = Database::getInstance();

// ─── Optional Type Filter ────────────────────────────────────────────────────
$typeFilter = $_GET['type'] ?? '';
$where = "WHERE status = 'Available'";
$params = [];

if ($typeFilter && in_array($typeFilter, ROOM_TYPES, true)) {
    $where .= ' AND type = :type';
    $params[':type'] = $typeFilter;
}

// ─── Fetch Available Rooms ───────────────────────────────────────────────────
$stmt = $db->prepare("SELECT * FROM rooms {$where} ORDER BY type ASC, room_number ASC");
$stmt->execute($params);
$rooms = $stmt->fetchAll();

// ─── Room Type Summary (for filter tabs) ─────────────────────────────────────
$typeCounts = $db->query("
    SELECT type, COUNT(*) as count 
    FROM rooms 
    WHERE status = 'Available' 
    GROUP BY type
")->fetchAll(PDO::FETCH_KEY_PAIR);

$totalAvailable = array_sum($typeCounts);

// ─── Price Range per Type ────────────────────────────────────────────────────
$priceRanges = $db->query("
    SELECT type, MIN(price_per_night) as min_price, MAX(price_per_night) as max_price
    FROM rooms 
    WHERE status = 'Available'
    GROUP BY type
")->fetchAll(PDO::FETCH_ASSOC);

$priceByType = [];
foreach ($priceRanges as $pr) {
    $priceByType[$pr['type']] = $pr;
}

// ─── Render View ─────────────────────────────────────────────────────────────
require_once VIEWS_PATH . '/listing/index.php';
