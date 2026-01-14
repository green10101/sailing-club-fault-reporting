<?php
require_once '../vendor/autoload.php';
require_once '../src/config/database.php';

$controller = new \App\Controllers\PublicController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $boatName = $_POST['boat_name'] ?? '';
    $faultDescription = $_POST['fault_description'] ?? '';

    $result = $controller->submitReport($boatName, $faultDescription);

    if ($result) {
        header('Location: /thanks');
        exit;
    } else {
        $error = "There was an error submitting your report. Please try again.";
    }
}

include '../src/Views/public/report_form.php';
?>