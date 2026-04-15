<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINEAD Integrated Hotel Management System - Secure Staff Login">
    <title>Login | SINEAD Hotel Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">
    <?php if (!empty(GOOGLE_CLIENT_ID)): ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php endif; ?>
</head>
<body>
    <div class="login-container">
        <div class="login-hero">
            <img src="<?php echo BASE_URL; ?>/assets/images/hero-login.png" alt="Luxury hotel corridor" class="login-hero-image">
            <div class="login-hero-overlay"></div>
            <div class="login-hero-content">
                <h1>SINEAD</h1>
                <p>Integrated Hotel Management System</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-header">
                <span class="brand-mark">SINEAD</span>
                <h2>Welcome Back</h2>
                <p>Sign in to access the management dashboard</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="login-error" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <!-- Google Sign-In (rendered by Google's SDK) -->
            <?php if (!empty(GOOGLE_CLIENT_ID)): ?>
            <div id="g_id_onload"
                 data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-callback="handleGoogleResponse"
                 data-auto_prompt="false">
            </div>
            <form method="POST" action="<?php echo url('google-callback'); ?>" id="googleForm" style="display:none;">
                <?php csrfField(); ?>
                <input type="hidden" name="credential" id="google_credential">
            </form>
            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="filled_black"
                 data-text="signin_with"
                 data-size="large"
                 data-logo_alignment="left"
                 data-width="400">
            </div>
            <div class="auth-divider"><span>or sign in with credentials</span></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?php echo url('login'); ?>" id="loginForm">
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" autocomplete="username" autofocus required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="<?php echo url('forgot'); ?>" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">Sign In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="<?php echo url('register'); ?>" class="forgot-link">Sign Up</a></p>
                <p style="margin-top: 0.5rem;">&copy; <?php echo date('Y'); ?> SINEAD Hotel Management System.</p>
            </div>
        </div>
    </div>

    <script>
        // Google callback
        function handleGoogleResponse(response) {
            document.getElementById('google_credential').value = response.credential;
            document.getElementById('googleForm').submit();
        }

        // Password toggle
        document.addEventListener('DOMContentLoaded', function() {
            var p = document.getElementById('password'), t = document.getElementById('passwordToggle');
            if (t && p) {
                t.addEventListener('click', function() {
                    var type = p.type === 'password' ? 'text' : 'password';
                    p.type = type;
                    this.innerHTML = type === 'password'
                        ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
                        : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                });
            }
        });
    </script>
</body>
</html>
