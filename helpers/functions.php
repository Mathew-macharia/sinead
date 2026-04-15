<?php
/**
 * Helper Functions
 * 
 * Provides utility functions used throughout the SINEAD application.
 * Follows the DRY (Don't Repeat Yourself) principle by centralizing
 * common operations: input sanitization, CSRF protection, session
 * management, formatting, and navigation.
 * 
 * @package    Sinead
 * @subpackage Helpers
 * @author     Sinead Development Team
 * @version    1.0.0
 */

// ─── Input Sanitization ─────────────────────────────────────────────────────

/**
 * Sanitize a string input to prevent XSS attacks.
 * 
 * Applies trimming, backslash stripping, and HTML entity encoding.
 * Should be used on ALL user-supplied input before display.
 * 
 * @param  string $data Raw user input
 * @return string       Sanitized string safe for HTML output
 */
function sanitize(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Sanitize an array of inputs recursively.
 * 
 * @param  array $data Associative array of user inputs
 * @return array       Sanitized array
 */
function sanitizeArray(array $data): array
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitizeArray($value);
        } else {
            $sanitized[$key] = sanitize((string) $value);
        }
    }
    return $sanitized;
}

// ─── CSRF Protection ────────────────────────────────────────────────────────

/**
 * Generate a CSRF token and store it in the session.
 * 
 * Uses cryptographically secure random bytes to prevent
 * Cross-Site Request Forgery attacks.
 * 
 * @return string The generated CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Render a hidden CSRF token input field for forms.
 * 
 * Usage: <?php csrfField(); ?> inside any <form> element.
 * 
 * @return void Outputs HTML directly
 */
function csrfField(): void
{
    echo '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

/**
 * Validate a submitted CSRF token against the session token.
 * 
 * @param  string $token The token submitted with the form
 * @return bool          True if the token is valid
 */
function validateCsrfToken(string $token): bool
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    // Regenerate token after validation to prevent reuse
    unset($_SESSION[CSRF_TOKEN_NAME]);
    return $valid;
}

/**
 * Verify the CSRF token from POST data. Terminates with 403 if invalid.
 * 
 * @return void
 */
function verifyCsrf(): void
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}

// ─── Session & Authentication ────────────────────────────────────────────────

/**
 * Start or resume a secure session.
 * Configures session parameters for security best practices.
 * 
 * @return void
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly'  => true,
            'samesite'  => 'Strict'
        ]);
        session_start();
    }
}

/**
 * Check if a user is currently authenticated.
 * 
 * @return bool True if the user has an active session
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently authenticated user's data from the session.
 * 
 * @param  string|null $key Specific session key to retrieve (e.g., 'role', 'username')
 * @return mixed            The requested value, full user array, or null
 */
function currentUser(?string $key = null)
{
    if (!isAuthenticated()) {
        return null;
    }

    if ($key !== null) {
        return $_SESSION[$key] ?? null;
    }

    return [
        'id'        => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role'      => $_SESSION['role']
    ];
}

/**
 * Check if the current user has a specific role.
 * 
 * @param  string|array $roles Role name or array of role names
 * @return bool                True if the user has the specified role
 */
function hasRole($roles): bool
{
    $userRole = currentUser('role');
    if ($userRole === null) {
        return false;
    }

    if (is_array($roles)) {
        return in_array($userRole, $roles, true);
    }

    return $userRole === $roles;
}

// ─── Flash Messages ─────────────────────────────────────────────────────────

/**
 * Set a flash message to be displayed on the next page load.
 * 
 * Flash messages are one-time notifications stored in the session
 * and automatically cleared after being displayed.
 * 
 * @param string $type    Message type: 'success', 'error', 'warning', 'info'
 * @param string $message The message content
 * @return void
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear all flash messages.
 * 
 * @return array Array of flash message arrays with 'type' and 'message' keys
 */
function getFlashMessages(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Render flash messages as styled HTML alert elements.
 * 
 * @return void Outputs HTML directly
 */
function renderFlashMessages(): void
{
    $messages = getFlashMessages();
    foreach ($messages as $msg) {
        $typeClass = sanitize($msg['type']);
        $message = sanitize($msg['message']);
        echo "<div class=\"alert alert-{$typeClass}\" role=\"alert\">";
        echo "<span class=\"alert-message\">{$message}</span>";
        echo "<button class=\"alert-close\" onclick=\"this.parentElement.remove()\" aria-label=\"Dismiss\">&times;</button>";
        echo "</div>";
    }
}

// ─── Navigation & Redirects ─────────────────────────────────────────────────

/**
 * Redirect to a specified URL path within the application.
 * 
 * @param string $path Relative path (e.g., 'dashboard', 'rooms')
 * @return void
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/index.php?page=' . $path);
    exit;
}

/**
 * Redirect to an absolute URL.
 * 
 * @param string $url Full URL to redirect to
 * @return void
 */
function redirectTo(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Generate the full URL for an application page.
 * 
 * @param  string $page   Page identifier
 * @param  array  $params Additional query parameters
 * @return string         Complete URL
 */
function url(string $page, array $params = []): string
{
    $url = BASE_URL . '/index.php?page=' . urlencode($page);
    foreach ($params as $key => $value) {
        $url .= '&' . urlencode($key) . '=' . urlencode($value);
    }
    return $url;
}

/**
 * Generate the URL path for a static asset.
 * 
 * @param  string $path Relative path within the assets directory
 * @return string       Full URL to the asset
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

// ─── Formatting ──────────────────────────────────────────────────────────────

/**
 * Format a number as currency (KES - Kenyan Shilling).
 * 
 * @param  float  $amount The amount to format
 * @return string         Formatted currency string (e.g., "KES 5,000.00")
 */
function formatCurrency(float $amount): string
{
    return 'KES ' . number_format($amount, 2);
}

/**
 * Format a date string for display.
 * 
 * @param  string $date   Date string in any parseable format
 * @param  string $format PHP date format (default: 'd M Y')
 * @return string         Formatted date string
 */
function formatDate(string $date, string $format = 'd M Y'): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : $date;
}

/**
 * Format a datetime string for display.
 * 
 * @param  string $datetime Datetime string
 * @param  string $format   PHP date format (default: 'd M Y, H:i')
 * @return string           Formatted datetime string
 */
function formatDateTime(string $datetime, string $format = 'd M Y, H:i'): string
{
    $timestamp = strtotime($datetime);
    return $timestamp ? date($format, $timestamp) : $datetime;
}

/**
 * Calculate the number of nights between two dates.
 * 
 * @param  string $checkIn  Check-in date string
 * @param  string $checkOut Check-out date string
 * @return int              Number of nights (minimum 1)
 */
function calculateNights(string $checkIn, string $checkOut): int
{
    $in = new DateTime($checkIn);
    $out = new DateTime($checkOut);
    $diff = $in->diff($out)->days;
    return max(1, $diff);
}

// ─── Activity Logging ────────────────────────────────────────────────────────

/**
 * Log a user action to the activity_log table.
 * 
 * Provides an audit trail of all significant operations
 * performed within the system.
 * 
 * @param string $action  Short action description (e.g., 'Created Room')
 * @param string $details Detailed description of what was done
 * @return void
 */
function logActivity(string $action, string $details = ''): void
{
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO activity_log (user_id, action, details, created_at) 
             VALUES (:user_id, :action, :details, NOW())'
        );
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'] ?? null,
            ':action'  => $action,
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        error_log('Activity log error: ' . $e->getMessage());
    }
}

// ─── Validation ──────────────────────────────────────────────────────────────

/**
 * Validate that required fields are present and non-empty.
 * 
 * @param  array $data     Associative array of form data
 * @param  array $required Array of required field names
 * @return array           Array of error messages (empty if valid)
 */
function validateRequired(array $data, array $required): array
{
    $errors = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $label = ucwords(str_replace('_', ' ', $field));
            $errors[] = "{$label} is required.";
        }
    }
    return $errors;
}

/**
 * Validate an email address format.
 * 
 * @param  string $email The email to validate
 * @return bool          True if valid
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ─── Pagination ──────────────────────────────────────────────────────────────

/**
 * Calculate pagination parameters.
 * 
 * @param  int $totalItems Total number of items
 * @param  int $currentPage Current page number (1-indexed)
 * @param  int $perPage    Items per page
 * @return array           Array with 'offset', 'limit', 'total_pages', 'current_page'
 */
function paginate(int $totalItems, int $currentPage = 1, int $perPage = ITEMS_PER_PAGE): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'offset'       => $offset,
        'limit'        => $perPage,
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'total_items'  => $totalItems
    ];
}
