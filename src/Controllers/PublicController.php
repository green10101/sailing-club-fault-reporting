<?php

namespace src\Controllers;

use src\Models\Report;
use src\Models\Boat;
use src\Services\MailService;

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

            $reportId = $this->reportModel->createAndReturnId($boatId, $faultDescription, $reporterName, $reporterEmail);
            if ($reportId !== false) {
                $boat = $this->boatModel->getBoatById((int) $boatId);
                $boatName = $boat['boat_name'] ?? 'Unknown boat';

                // Email should never prevent a valid report from being saved.
                try {
                    $emailSent = $this->mailService->sendNewFaultReportEmail([
                        'report_id' => $reportId,
                        'boat_name' => $boatName,
                        'reporter_email' => $reporterEmail,
                        'reporter_name' => $reporterName,
                        'fault_description' => $faultDescription,
                    ]);

                    if ($emailSent) {
                        error_log('New fault report email sent for report #' . $reportId);
                    } else {
                        error_log('New fault report email could not be sent for report #' . $reportId);
                    }
                } catch (\Throwable $e) {
                    error_log('New fault report email failed for report #' . $reportId . ': ' . $e->getMessage());
                }

                header('Location: index.php?route=/thanks');
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