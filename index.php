<?php
/**
 * Front Controller / Router
 * 
 * Implements the Front Controller design pattern.
 * All HTTP requests are routed through index.php, which delegates
 * to the appropriate controller based on the 'page' query parameter.
 * 
 * This approach centralizes request handling, enabling consistent
 * authentication checks, error handling, and session management
 * across the entire application.
 * 
 * Routing Map:
 *   ?page=login         -> AuthController (login form / processing)
 *   ?page=logout        -> AuthController (session destruction)
 *   ?page=forgot        -> AuthController (password reset)
 *   ?page=dashboard     -> DashboardController
 *   ?page=rooms         -> RoomController
 *   ?page=guests        -> GuestController
 *   ?page=reservations  -> ReservationController
 *   ?page=billing       -> BillingController
 *   ?page=reports       -> ReportController
 *   ?page=housekeeping  -> HousekeepingController
 *   ?page=users         -> UserController (admin only)
 * 
 * @package    Sinead
 * @author     Sinead Development Team
 * @version    1.0.0
 */

// ─── Bootstrap ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/middleware/auth.php';

// ─── Models (Factory Pattern) ────────────────────────────────────────────────
require_once __DIR__ . '/models/Room.php';
require_once __DIR__ . '/models/StandardRoom.php';
require_once __DIR__ . '/models/DeluxeRoom.php';
require_once __DIR__ . '/models/SuiteRoom.php';
require_once __DIR__ . '/models/RoomFactory.php';

// ─── Services (Adapter Pattern) ──────────────────────────────────────────────
require_once __DIR__ . '/services/NotificationInterface.php';
require_once __DIR__ . '/services/EmailAdapter.php';
require_once __DIR__ . '/services/SMSAdapter.php';
require_once __DIR__ . '/services/NotificationService.php';

// Initialize secure session
startSecureSession();

// ─── Route Resolution ────────────────────────────────────────────────────────
$page = $_GET['page'] ?? '';

// Redirect root to appropriate page
if ($page === '' || $page === 'home') {
    if (isAuthenticated()) {
        redirect('dashboard');
    } else {
        redirect('listing');
    }
    exit;
}

// ─── Controller Dispatch ─────────────────────────────────────────────────────
// Maps page identifiers to their controller files.
// Each controller is responsible for handling its own actions (CRUD).

$routes = [
    'listing'         => 'controllers/ListingController.php',
    'login'           => 'controllers/AuthController.php',
    'register'        => 'controllers/AuthController.php',
    'google-callback' => 'controllers/AuthController.php',
    'logout'          => 'controllers/AuthController.php',
    'forgot'        => 'controllers/AuthController.php',
    'dashboard'     => 'controllers/DashboardController.php',
    'rooms'         => 'controllers/RoomController.php',
    'guests'        => 'controllers/GuestController.php',
    'reservations'  => 'controllers/ReservationController.php',
    'billing'       => 'controllers/BillingController.php',
    'reports'       => 'controllers/ReportController.php',
    'housekeeping'  => 'controllers/HousekeepingController.php',
    'users'         => 'controllers/UserController.php',
];

if (array_key_exists($page, $routes)) {
    require_once __DIR__ . '/' . $routes[$page];
} else {
    // 404 - Page Not Found
    http_response_code(404);
    require_once __DIR__ . '/views/errors/404.php';
}
