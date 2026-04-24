<?php
/**
 * Security Configuration and Helper Functions
 */

// Configure secure session settings
function configureSecureSession() {
    // Set secure session cookie parameters
    $sessionOptions = [
        'lifetime' => 3600,           // Session timeout in seconds (1 hour)
        'path' => '/',
        'domain' => '',               // Uses default domain
        'secure' => !empty($_SERVER['HTTPS']),  // Only send over HTTPS in production
        'httponly' => true,           // Prevent JavaScript access to session cookie
        'samesite' => 'Strict'        // CSRF protection - only send with same-site requests
    ];
    
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($sessionOptions);
    } else {
        // Fallback for older PHP versions
        session_set_cookie_params(
            $sessionOptions['lifetime'],
            $sessionOptions['path'],
            $sessionOptions['domain'],
            $sessionOptions['secure'],
            $sessionOptions['httponly']
        );
    }
}

// Initialize CSRF token in session
function initializeCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Get current CSRF token
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token from POST request
function verifyCsrfToken($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }
    
    // Token must exist in session and match provided token
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    
    // Regenerate token after verification
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

// Get CSRF token as hidden form field
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken()) . '">';
}

// Add security headers
function addSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection in older browsers
    header('X-XSS-Protection: 1; mode=block');
    
    // Strict-Transport-Security (if using HTTPS)
    if (!empty($_SERVER['HTTPS'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Content Security Policy - restrict resource loading
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'");
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Validate email format (server-side)
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Track login attempts for rate limiting
function recordLoginAttempt($email) {
    $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email) . '.json';
    $attempts = [];
    
    if (file_exists($attemptsFile)) {
        $data = json_decode(file_get_contents($attemptsFile), true);
        if (is_array($data)) {
            $attempts = $data;
        }
    }
    
    // Remove attempts older than 15 minutes
    $now = time();
    $attempts = array_filter($attempts, function($time) use ($now) {
        return ($now - $time) < (15 * 60);
    });
    
    // Add new attempt
    $attempts[] = $now;
    
    file_put_contents($attemptsFile, json_encode($attempts), LOCK_EX);
    
    return count($attempts);
}

// Check if login is rate limited
function isLoginRateLimited($email) {
    $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email) . '.json';
    
    if (!file_exists($attemptsFile)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($attemptsFile), true);
    $attempts = is_array($data) ? $data : [];
    
    // Remove attempts older than 15 minutes
    $now = time();
    $attempts = array_filter($attempts, function($time) use ($now) {
        return ($now - $time) < (15 * 60);
    });
    
    // Block after 5 failed attempts in 15 minutes
    return count($attempts) >= 5;
}

// Clear login attempts on successful login
function clearLoginAttempts($email) {
    $attemptsFile = sys_get_temp_dir() . '/login_attempts_' . md5($email) . '.json';
    if (file_exists($attemptsFile)) {
        unlink($attemptsFile);
    }
}

// Determine whether the current session user should be treated as an admin.
function userHasAdminRole(): bool {
    $role = isset($_SESSION['user']['role']) ? trim((string) $_SESSION['user']['role']) : '';
    if ($role === '') {
        return false;
    }

    return preg_match('/\badmin(istrator)?\b/i', $role) === 1;
}
?>
