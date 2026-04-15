<?php
/**
 * Application Configuration
 * 
 * Centralized configuration constants for the SINEAD Hotel Management System.
 * Defines application-wide settings, paths, and environment configuration.
 * 
 * @package    Sinead
 * @subpackage Config
 * @author     Sinead Development Team
 * @version    1.0.0
 */

// ─── Application Identity ────────────────────────────────────────────────────
define('APP_NAME', 'SINEAD');
define('APP_FULL_NAME', 'SINEAD Integrated Hotel Management System');
define('APP_VERSION', '1.0.0');

// ─── Path Configuration ─────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('VIEWS_PATH', BASE_PATH . '/views');
define('HELPERS_PATH', BASE_PATH . '/helpers');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');

// ─── URL Configuration ──────────────────────────────────────────────────────
// Auto-detect the base URL from server variables
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);

// ─── Session Configuration ──────────────────────────────────────────────────
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'sinead_session');

// ─── Timezone ────────────────────────────────────────────────────────────────
date_default_timezone_set('Africa/Nairobi');

// ─── Security ────────────────────────────────────────────────────────────────
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_COST', 12); // bcrypt cost factor

// ─── Google OAuth ────────────────────────────────────────────────────────
// Get your Client ID from: https://console.cloud.google.com/apis/credentials
// Set Authorized redirect URI to: http://localhost:8000/index.php?page=google-callback
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET');

// ─── Pagination ──────────────────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 15);

// ─── Room Types ──────────────────────────────────────────────────────────────
define('ROOM_TYPES', ['Standard', 'Deluxe', 'Suite']);
define('ROOM_STATUSES', ['Available', 'Occupied', 'Maintenance']);

// ─── User Roles ──────────────────────────────────────────────────────────────
define('ROLE_ADMIN', 'admin');
define('ROLE_RECEPTIONIST', 'receptionist');
define('ROLE_HOUSEKEEPING', 'housekeeping');
define('USER_ROLES', [ROLE_ADMIN, ROLE_RECEPTIONIST, ROLE_HOUSEKEEPING]);

// ─── Reservation Statuses ────────────────────────────────────────────────────
define('RESERVATION_STATUSES', ['Confirmed', 'CheckedIn', 'CheckedOut', 'Cancelled']);

// ─── Invoice Statuses ────────────────────────────────────────────────────────
define('INVOICE_STATUSES', ['Unpaid', 'Partial', 'Paid']);
define('PAYMENT_METHODS', ['Cash', 'Card', 'Bank Transfer']);

// ─── Housekeeping ────────────────────────────────────────────────────────────
define('TASK_TYPES', ['Cleaning', 'Maintenance', 'Restocking']);
define('TASK_PRIORITIES', ['Low', 'Medium', 'High']);
define('TASK_STATUSES', ['Pending', 'InProgress', 'Completed']);

// ─── Hotel Contact Information (Public Listing Page) ─────────────────────────
define('HOTEL_PHONE', '+254 700 123 456');
define('HOTEL_EMAIL', 'reservations@sinead-hotel.com');
define('HOTEL_ADDRESS', 'Berekuso, Eastern Region, Ghana');
define('HOTEL_HOURS', 'Open 24 Hours — Front Desk Always Available');

// ─── Error Reporting (Development) ───────────────────────────────────────────
// Set to false in production
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/error.log');
