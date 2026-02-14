<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load security configuration
require_once '../src/config/security.php';

// Configure secure session settings before starting session
configureSecureSession();
session_start();

// Add security headers
addSecurityHeaders();

// Initialize CSRF token
initializeCsrfToken();

require_once '../vendor/autoload.php';
require_once '../src/config/database.php';

$authController = new \App\Controllers\AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken()) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Check if login is rate limited
        if (isLoginRateLimited($username)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
        } elseif ($authController->login($username, $password)) {
            header('Location: index.php?route=/bosun/dashboard');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/app.css">
    <title>Login - Sailing Club Fault Reporting</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php?route=/login">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="email" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>