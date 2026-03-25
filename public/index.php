<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$controller = new \src\Controllers\PublicController();

// Parse the request URI to remove query string for routing
// Support both URL path and ?route= query parameter
$requestUri = $_GET['route'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /bosun prefix if present
$requestUri = preg_replace('#^/bosun#', '', $requestUri);


// Normalize the route - treat /index.php and empty as /
if ($requestUri === '/index.php' || $requestUri === '' || $requestUri === '/bosun/public/index.php') {
    $requestUri = '/';
}

switch ($requestUri) {
    case '/':
        // If user is logged in, redirect to boat status
        if (isset($_SESSION['user'])) {
            header('Location: /bosun/boats');
            exit;
        }
        // Otherwise show public report form
        $controller->showReportForm();
        break;
    case '/report-form':
        // Show report form for both logged-in staff and public users
        $controller->showReportForm();
        break;
    case '/report':
        $controller->submitReport();
        break;
    case '/thanks':
        $controller->showThanks();
        break;
    case '/login':
        $authController = new \src\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($authController->login($username, $password)) {
                header('Location: /bosun/boats');
                exit;
            } else {
                $error = 'Invalid username or password.';
                include '../src/Views/public/login.php';
            }
        } else {
            include '../src/Views/public/login.php';
        }
        break;
    case '/admin/users':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $adminController = new \src\Controllers\AdminController();
        $adminController->users();
        break;
    case '/admin/user/new':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $adminController = new \src\Controllers\AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->createUser();
        } else {
            $adminController->newUser();
        }
        break;
    case (preg_match('/^\/admin\/user\/edit\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userId = $matches[1];
        $adminController = new \src\Controllers\AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->updateUser($userId);
        } else {
            $adminController->editUser($userId);
        }
        break;
    case (preg_match('/^\/admin\/user\/delete\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userId = $matches[1];
        $adminController = new \src\Controllers\AdminController();
        $adminController->deleteUser($userId);
        break;
    case (preg_match('/^\/admin\/delete-report\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $reportId = $matches[1];
        $adminController = new \src\Controllers\AdminController();
        $adminController->deleteReport($reportId);
        break;
    case (preg_match('/^\/admin\/user\/reset-password\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userId = $matches[1];
        $adminController = new \src\Controllers\AdminController();
        $adminController->resetPassword($userId);
        break;
    case '/bosun/dashboard':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $bosunController = new \src\Controllers\BosunController();
        $bosunController->dashboard();
        break;
    case '/bosun/boats':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $bosunController = new \src\Controllers\BosunController();
        $bosunController->boats();
        break;
    case '/bosun/print-report':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $bosunController = new \src\Controllers\BosunController();
        $bosunController->printReport();
        break;
    case '/bosun/boat/new':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $bosunController = new \src\Controllers\BosunController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bosunController->createBoat();
        } else {
            $bosunController->newBoat();
        }
        break;
    case (preg_match('/^\/bosun\/boat\/edit\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $boatId = $matches[1];
        $bosunController = new \src\Controllers\BosunController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bosunController->updateBoat($boatId);
        } else {
            $bosunController->editBoat($boatId);
        }
        break;
    case '/bosun/update-boat-status':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $boatId = $_POST['boat_id'];
            $status = $_POST['status'];
            $bosunController = new \src\Controllers\BosunController();
            $bosunController->updateBoatStatus($boatId, $status);
        }
        break;
    case '/bosun/update-status':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportId = $_POST['report_id'];
            $status = $_POST['status'];
            $bosunController = new \src\Controllers\BosunController();
            $bosunController->updateReportStatus($reportId, $status);
        }
        break;
    case '/bosun/update-notes':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportId = $_POST['report_id'];
            $notes = $_POST['notes'];
            $bosunController = new \src\Controllers\BosunController();
            $bosunController->updateReportNotes($reportId, $notes);
        }
        break;
    case (preg_match('/^\/bosun\/edit\/(\d+)$/', $requestUri, $matches) ? $requestUri : null):
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $reportId = $matches[1];
        $bosunController = new \src\Controllers\BosunController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bosunController->updateReport($reportId);
        } else {
            $bosunController->editReport($reportId);
        }
        break;
    case '/profile':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userProfileController = new \src\Controllers\UserProfileController();
        $userProfileController->profile();
        break;
    case '/profile/edit':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userProfileController = new \src\Controllers\UserProfileController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userProfileController->updateProfile();
        } else {
            $userProfileController->editProfile();
        }
        break;
    case '/profile/update':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userProfileController = new \src\Controllers\UserProfileController();
        $userProfileController->updateProfile();
        break;
    case '/profile/change-password':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userProfileController = new \src\Controllers\UserProfileController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userProfileController->updatePassword();
        } else {
            $userProfileController->editPassword();
        }
        break;
    case '/profile/update-password':
        if (!isset($_SESSION['user'])) {
            header('Location: /bosun/login');
            exit;
        }
        $userProfileController = new \src\Controllers\UserProfileController();
        $userProfileController->updatePassword();
        break;
    case '/logout':
        session_destroy();
        header('Location: index.php');
        exit;
        break;
    default:
        http_response_code(404);
        echo "404 Not Found - Route: " . htmlspecialchars($requestUri);
        break;
}
?>