<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINEAD Hotel Management System - Create Account">
    <title>Sign Up | SINEAD Hotel Management</title>
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

        <div class="login-form-panel" style="justify-content: flex-start; padding-top: 2.5rem; padding-bottom: 2.5rem;">
            <div class="login-header" style="margin-bottom: 1.5rem;">
                <span class="brand-mark">SINEAD</span>
                <h2>Create Account</h2>
                <p>Register for a new staff account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="login-error" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background: rgba(74,140,92,0.15); border-left: 3px solid #4A8C5C; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.8125rem; color: #4A8C5C;">
                    <?php echo sanitize($success); ?>
                </div>
            <?php endif; ?>

            <!-- Google Sign-Up (official SDK button) -->
            <?php if (!empty(GOOGLE_CLIENT_ID)): ?>
            <div id="g_id_onload"
                 data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
                 data-context="signup"
                 data-ux_mode="popup"
                 data-callback="handleGoogleResponse"
                 data-auto_prompt="false">
            </div>
            <form method="POST" action="<?php echo url('google-callback'); ?>" id="googleForm" style="display:none;">
                <?php csrfField(); ?>
                <input type="hidden" name="credential" id="google_credential">
                <input type="hidden" name="context" value="register">
            </form>
            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="filled_black"
                 data-text="signup_with"
                 data-size="large"
                 data-logo_alignment="left"
                 data-width="400">
            </div>
            <div class="auth-divider"><span>or register with email</span></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?php echo url('register'); ?>" id="registerForm">
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter your full name" value="<?php echo sanitize($formData['full_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" value="<?php echo sanitize($formData['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path></svg>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" value="<?php echo sanitize($formData['username'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter your password" autocomplete="new-password" required>
                    </div>
                </div>

                <div style="background: var(--bg-primary); border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.75rem; color: var(--text-muted); border: 1px solid var(--border-color);">
                    New accounts are assigned the <strong style="color: var(--accent-gold);">Receptionist</strong> role by default. An administrator can update your role after registration.
                </div>

                <button type="submit" class="btn-login">Create Account</button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="<?php echo url('login'); ?>" class="forgot-link">Sign In</a></p>
                <p style="margin-top: 0.5rem;">&copy; <?php echo date('Y'); ?> SINEAD Hotel Management System.</p>
            </div>
        </div>
    </div>

    <script>
        function handleGoogleResponse(response) {
            document.getElementById('google_credential').value = response.credential;
            document.getElementById('googleForm').submit();
        }

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
