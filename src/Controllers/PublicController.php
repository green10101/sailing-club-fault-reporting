<?php

namespace App\Controllers;

use App\Models\Report;
use App\Services\MailService;

class PublicController
{
    protected $reportModel;
    protected $mailService;

    public function __construct()
    {
        $this->reportModel = new Report();
        $this->mailService = new MailService();
    }

    public function showReportForm()
    {
        include '../src/Views/public/report_form.php';
    }

    public function submitReport()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $boatName = $_POST['boat_name'] ?? '';
            $faultDescription = $_POST['fault_description'] ?? '';

            if ($this->reportModel->createReport($boatName, $faultDescription)) {
                $this->mailService->sendRepairNotification($boatName, $faultDescription);
                header('Location: thanks.php');
                exit;
            } else {
                // Handle error (e.g., show an error message)
            }
        }
    }
}