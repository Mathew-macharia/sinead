<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | SINEAD Hotel Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-hero">
            <img src="<?php echo BASE_URL; ?>/assets/images/hero-login.png" alt="Luxury hotel" class="login-hero-image">
            <div class="login-hero-overlay"></div>
            <div class="login-hero-content">
                <h1>SINEAD</h1>
                <p>Integrated Hotel Management System</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-header">
                <span class="brand-mark">SINEAD</span>
                <h2>Reset Password</h2>
                <p>Enter the email address associated with your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="login-error" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background: rgba(74,140,92,0.15); border-left: 3px solid #4A8C5C; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #4A8C5C;">
                    <?php echo sanitize($success); ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?php echo url('forgot'); ?>">
                <?php csrfField(); ?>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Send Reset Link
                </button>
            </form>

            <div class="login-footer" style="margin-top: 2rem;">
                <a href="<?php echo url('login'); ?>" class="forgot-link" style="font-size: 0.875rem;">Back to Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>
