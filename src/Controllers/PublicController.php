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
            $boatId = (int) ($_POST['boat_id'] ?? 0);
            $faultDescription = trim((string) ($_POST['fault_description'] ?? ''));
            $reporterName = trim((string) ($_POST['reporter_name'] ?? ''));
            $reporterEmail = strtolower(trim((string) ($_POST['reporter_email'] ?? '')));

            $existingReportId = $this->reportModel->findRecentDuplicateReportId(
                $boatId,
                $faultDescription,
                $reporterName,
                $reporterEmail
            );

            if ($existingReportId !== null) {
                error_log('Duplicate fault report suppressed; using existing report #' . $existingReportId);
                header('Location: index.php?route=/thanks');
                exit;
            }

            $reportId = $this->reportModel->createAndReturnId($boatId, $faultDescription, $reporterName, $reporterEmail);
            if ($reportId !== false) {
                $boat = $this->boatModel->getBoatById($boatId);
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
                error_log('Fault report could not be created for boat #' . $boatId);
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