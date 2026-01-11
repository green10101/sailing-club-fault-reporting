<?php
require_once '../src/config/database.php';
require_once '../src/Controllers/PublicController.php';

$controller = new PublicController();

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
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>