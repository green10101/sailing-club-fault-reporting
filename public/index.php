<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../src/config/database.php';

$controller = new \App\Controllers\PublicController();

$requestUri = $_SERVER['REQUEST_URI'];

switch ($requestUri) {
    case '/':
        $controller->showReportForm();
        break;
    case '/report':
        $controller->submitReport();
        break;
    case '/thanks':
        $controller->showThanks();
        break;
    case '/login':
        $controller->showLogin();
        break;
    case '/bosun/dashboard':
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $bosunController = new \App\Controllers\BosunController();
        $bosunController->dashboard();
        break;
    case '/bosun/update-status':
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportId = $_POST['report_id'];
            $status = $_POST['status'];
            $bosunController = new \App\Controllers\BosunController();
            $bosunController->updateReportStatus($reportId, $status);
        }
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>