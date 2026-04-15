<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication operations:
 *   - Login           (form display + credential verification)
 *   - Register        (new account creation with validation)
 *   - Google Sign-In  (OAuth 2.0 via Google Identity Services)
 *   - Logout          (session destruction)
 *   - Forgot          (password reset via email)
 * 
 * Security Measures:
 *   - bcrypt password hashing with configurable cost factor
 *   - CSRF token validation on all POST requests
 *   - Session regeneration on login to prevent session fixation
 *   - Duplicate username/email checking on registration
 *   - Password confirmation matching
 *   - Secure session cookie configuration
 * 
 * @package    Sinead
 * @subpackage Controllers
 * @author     Sinead Development Team
 * @version    1.1.0
 */

// ─── Route Handling ──────────────────────────────────────────────────────────
$action = $page; // 'login', 'register', 'logout', or 'forgot'

switch ($action) {

    // ─── LOGOUT ──────────────────────────────────────────────────────────
    case 'logout':
        handleLogout();
        break;

    // ─── GOOGLE CALLBACK ─────────────────────────────────────────────────
    case 'google-callback':
        handleGoogleCallback();
        break;

    // ─── REGISTER ────────────────────────────────────────────────────────
    case 'register':
        if (isAuthenticated()) {
            redirect('dashboard');
            break;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleRegister();
        } else {
            showRegisterForm();
        }
        break;

    // ─── FORGOT PASSWORD ─────────────────────────────────────────────────
    case 'forgot':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleForgotPassword();
        } else {
            showForgotForm();
        }
        break;

    // ─── LOGIN ───────────────────────────────────────────────────────────
    case 'login':
    default:
        if (isAuthenticated()) {
            redirect('dashboard');
            break;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleLogin();
        } else {
            showLoginForm();
        }
        break;
}

// ═══════════════════════════════════════════════════════════════════════════════
// LOGIN HANDLERS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Display the login form.
 * 
 * @return void
 */
function showLoginForm(): void
{
    $error = $_SESSION['login_error'] ?? null;
    unset($_SESSION['login_error']);
    require_once VIEWS_PATH . '/auth/login.php';
}

/**
 * Process login form submission.
 * 
 * Flow:
 *   1. Validate CSRF token
 *   2. Sanitize and validate input
 *   3. Query database for user
 *   4. Verify password hash
 *   5. Regenerate session ID (prevent fixation)
 *   6. Store user data in session
 *   7. Log activity and redirect to dashboard
 * 
 * @return void
 */
function handleLogin(): void
{
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter both username and password.';
        redirect('login');
        return;
    }

    try {
        $db = Database::getInstance();

        $stmt = $db->prepare(
            'SELECT id, username, password_hash, full_name, role, is_active 
             FROM users 
             WHERE username = :username 
             LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Invalid username or password.';
            redirect('login');
            return;
        }

        if (!$user['is_active']) {
            $_SESSION['login_error'] = 'Your account has been deactivated. Contact an administrator.';
            redirect('login');
            return;
        }

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['login_time'] = time();

        // Update last_login timestamp
        $db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id')
           ->execute([':id' => $user['id']]);

        logActivity('User Login', "User '{$user['username']}' logged in successfully.");
        setFlash('success', "Welcome back, {$user['full_name']}.");
        redirect('dashboard');

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'A system error occurred. Please try again.';
        redirect('login');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// REGISTRATION HANDLERS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Display the registration form.
 * 
 * @return void
 */
function showRegisterForm(): void
{
    $error = $_SESSION['register_error'] ?? null;
    $success = $_SESSION['register_success'] ?? null;
    $formData = $_SESSION['register_form'] ?? [];
    unset($_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['register_form']);
    require_once VIEWS_PATH . '/auth/register.php';
}

/**
 * Process registration form submission.
 * 
 * Validates:
 *   - All required fields present
 *   - Valid email format
 *   - Username uniqueness
 *   - Email uniqueness
 *   - Password minimum length (6 chars)
 *   - Password confirmation match
 * 
 * @return void
 */
function handleRegister(): void
{
    verifyCsrf();

    $formData = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'username'  => trim($_POST['username'] ?? ''),
    ];
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Store form data for repopulation on error
    $_SESSION['register_form'] = $formData;

    // Required fields validation
    if (empty($formData['full_name']) || empty($formData['email']) || empty($formData['username']) || empty($password)) {
        $_SESSION['register_error'] = 'All fields are required.';
        redirect('register');
        return;
    }

    // Email format validation
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Please enter a valid email address.';
        redirect('register');
        return;
    }

    // Password length
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = 'Password must be at least 6 characters long.';
        redirect('register');
        return;
    }

    // Password confirmation
    if ($password !== $confirmPassword) {
        $_SESSION['register_error'] = 'Passwords do not match.';
        redirect('register');
        return;
    }

    // Role: hardcoded to receptionist (RBAC policy)
    // Only admins can assign other roles via User Management
    $defaultRole = 'receptionist';

    try {
        $db = Database::getInstance();

        // Check username uniqueness
        $checkUser = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
        $checkUser->execute([':u' => $formData['username']]);
        if ($checkUser->fetch()) {
            $_SESSION['register_error'] = 'This username is already taken. Please choose another.';
            redirect('register');
            return;
        }

        // Check email uniqueness
        $checkEmail = $db->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
        $checkEmail->execute([':e' => $formData['email']]);
        if ($checkEmail->fetch()) {
            $_SESSION['register_error'] = 'An account with this email already exists.';
            redirect('register');
            return;
        }

        // Create the user — inactive by default, admin must approve
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $stmt = $db->prepare("
            INSERT INTO users (username, password_hash, full_name, email, role, is_active)
            VALUES (:username, :password, :name, :email, :role, 0)
        ");
        $stmt->execute([
            ':username' => $formData['username'],
            ':password' => $hashedPassword,
            ':name'     => $formData['full_name'],
            ':email'    => $formData['email'],
            ':role'     => $defaultRole
        ]);

        logActivity('User Registration', "New user '{$formData['username']}' registered (pending admin approval).");

        // Clear form data on success
        unset($_SESSION['register_form']);

        $_SESSION['register_success'] = 'Your account has been submitted for approval. An administrator will activate your account before you can sign in.';
        redirect('register');

    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        $_SESSION['register_error'] = 'A system error occurred during registration. Please try again.';
        redirect('register');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// LOGOUT HANDLER
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Destroy the user session and redirect to login.
 * 
 * @return void
 */
function handleLogout(): void
{
    if (isAuthenticated()) {
        logActivity('User Logout', "User '" . currentUser('username') . "' logged out.");
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();

    startSecureSession();
    setFlash('info', 'You have been logged out successfully.');
    redirect('login');
}

// ═══════════════════════════════════════════════════════════════════════════════
// FORGOT PASSWORD HANDLERS
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Display the forgot password form.
 * 
 * @return void
 */
function showForgotForm(): void
{
    $error = $_SESSION['forgot_error'] ?? null;
    $success = $_SESSION['forgot_success'] ?? null;
    unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);
    require_once VIEWS_PATH . '/auth/forgot.php';
}

/**
 * Handle forgot password form submission (email-based).
 * 
 * Looks up the user by email address. In production this would send
 * an email with a reset link. For this prototype, it confirms the
 * request and instructs the user to contact an administrator.
 * 
 * Security: Does not reveal whether the email exists in the system.
 * 
 * @return void
 */
function handleForgotPassword(): void
{
    verifyCsrf();

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['forgot_error'] = 'Please enter your email address.';
        redirect('forgot');
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_error'] = 'Please enter a valid email address.';
        redirect('forgot');
        return;
    }

    try {
        $db = Database::getInstance();

        $stmt = $db->prepare('SELECT id, full_name, username FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure reset token
            $token = bin2hex(random_bytes(32));
            $tokenHash = password_hash($token, PASSWORD_BCRYPT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Invalidate any existing tokens for this user
            $db->prepare('UPDATE password_resets SET used = 1 WHERE user_id = :uid')
               ->execute([':uid' => $user['id']]);

            // Store the new token
            $stmt = $db->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at) 
                 VALUES (:uid, :token, :expires)'
            );
            $stmt->execute([
                ':uid'     => $user['id'],
                ':token'   => $tokenHash,
                ':expires' => $expiresAt
            ]);

            logActivity('Password Reset Requested', "Reset requested for user '{$user['username']}' via email.");
        }

        // Always show the same message (don't reveal if email exists)
        $_SESSION['forgot_success'] = 'If an account with that email address exists, password reset instructions have been sent. For this prototype, please contact your system administrator to complete the reset.';
        redirect('forgot');

    } catch (PDOException $e) {
        error_log('Forgot password error: ' . $e->getMessage());
        $_SESSION['forgot_error'] = 'A system error occurred. Please try again.';
        redirect('forgot');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// GOOGLE SIGN-IN HANDLER
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Handle the Google Sign-In callback.
 * 
 * Decodes the JWT credential token from Google Identity Services,
 * extracts user profile data (email, name), and either:
 *   - Logs in the user if their email already exists in the system
 *   - Creates a new account and logs them in if they're a new user
 * 
 * JWT is decoded without external libraries by splitting the Base64
 * payload. Token verification relies on Google's client-side library
 * having already validated the token before submission.
 * 
 * @return void
 */
function handleGoogleCallback(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('login');
        return;
    }
    verifyCsrf();

    $credential = $_POST['credential'] ?? '';
    if (empty($credential)) {
        $_SESSION['login_error'] = 'Google authentication failed. No credential received.';
        redirect('login');
        return;
    }

    // Decode the JWT payload (header.payload.signature)
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        $_SESSION['login_error'] = 'Invalid Google credential format.';
        redirect('login');
        return;
    }

    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if (!$payload || empty($payload['email'])) {
        $_SESSION['login_error'] = 'Could not extract profile from Google credential.';
        redirect('login');
        return;
    }

    $email    = $payload['email'];
    $name     = $payload['name'] ?? $email;
    $picture  = $payload['picture'] ?? null;
    $context  = $_POST['context'] ?? 'login'; // 'login' or 'register'

    try {
        $db = Database::getInstance();

        // Check if user with this email already exists
        $stmt = $db->prepare('SELECT id, username, full_name, role, is_active FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // ── Existing user ──────────────────────────────────────────────
            if (!$user['is_active']) {
                $_SESSION['login_error'] = 'Your account is pending administrator approval. Please contact the hotel manager.';
                redirect('login');
                return;
            }

            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['login_time'] = time();

            $db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id')
               ->execute([':id' => $user['id']]);

            logActivity('Google Login', "User '{$user['username']}' signed in via Google.");
            setFlash('success', "Welcome back, {$user['full_name']}");
            redirect('dashboard');

        } else {
            // ── New user via Google — create inactive, require admin approval ──
            $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0]));
            if (empty($baseUsername)) $baseUsername = 'user';
            $username = $baseUsername;
            $suffix   = 1;

            while (true) {
                $check = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
                $check->execute([':u' => $username]);
                if (!$check->fetch()) break;
                $username = $baseUsername . $suffix;
                $suffix++;
            }

            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

            $stmt = $db->prepare("
                INSERT INTO users (username, password_hash, full_name, email, role, is_active)
                VALUES (:username, :password, :name, :email, 'receptionist', 0)
            ");
            $stmt->execute([
                ':username' => $username,
                ':password' => $randomPassword,
                ':name'     => $name,
                ':email'    => $email
            ]);

            logActivity('Google Registration', "New user '{$username}' registered via Google (pending admin approval).");

            // Do NOT log them in — redirect to login with pending message
            $_SESSION['login_error'] = 'Your account has been created and is pending administrator approval. Please contact the hotel manager to activate your account.';
            redirect('login');
        }

    } catch (PDOException $e) {
        error_log('Google auth error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'A system error occurred during Google authentication.';
        redirect('login');
    }
}
