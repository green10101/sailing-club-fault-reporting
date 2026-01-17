<?php

namespace App\Controllers;

use App\Models\Report;
use App\Models\Boat;
use App\Services\MailService;

class PublicController
{
    protected $reportModel;
    protected $boatModel;
    protected $mailService;

    public function __construct()
    {
        $this->reportModel = new Report();
        $this->boatModel = new Boat();
        $this->mailService = new MailService();
    }

    public function showReportForm()
    {
        $boats = $this->boatModel->getAllBoats();
        include '../src/Views/public/report_form.php';
    }

    public function submitReport()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $boatId = $_POST['boat_id'] ?? '';
            $faultDescription = $_POST['fault_description'] ?? '';
            $reporterName = $_POST['reporter_name'] ?? '';
            $reporterEmail = $_POST['reporter_email'] ?? '';

            if ($this->reportModel->create($boatId, $faultDescription, $reporterName, $reporterEmail)) {
                // $this->mailService->sendRepairAssignedEmail('admin@example.com', $boatName, $faultDescription); // Uncomment when email is configured
                header('Location: /thanks');
                exit;
            } else {
                // Handle error (e.g., show an error message)
            }
        }
    }

    public function showThanks()
    {
        $isBosun = isset($_SESSION['user']);
        include '../src/Views/public/thanks.php';
    }

    public function showLogin()
    {
        include '../src/Views/public/login.php';
    }
}