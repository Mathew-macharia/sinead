<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | <?php echo APP_NAME ?? 'SINEAD'; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <style>
        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 8rem;
            font-weight: 700;
            color: var(--accent-gold);
            line-height: 1;
            opacity: 0.3;
        }
        .error-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            margin: 1rem 0 0.5rem;
        }
        .error-message {
            color: var(--text-muted);
            max-width: 400px;
            margin: 0 auto 2rem;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div>
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">The page you are looking for does not exist or has been moved.</p>
            <a href="<?php echo BASE_URL ?? ''; ?>/index.php?page=dashboard" class="btn btn-primary btn-lg">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
