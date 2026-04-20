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
        $reportSubmissionToken = $this->refreshReportSubmissionToken();
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

            if (!$this->consumeReportSubmissionToken((string) ($_POST['report_submission_token'] ?? ''))) {
                $existingReportId = $this->reportModel->findRecentDuplicateReportId(
                    $boatId,
                    $faultDescription,
                    $reporterName,
                    $reporterEmail
                );

                if ($existingReportId !== null) {
                    error_log('Duplicate fault report blocked by used submission token; using existing report #' . $existingReportId);
                    header('Location: index.php?route=/thanks');
                    exit;
                }

                error_log('Fault report submission rejected due to invalid or reused submission token');
                $error = 'This form has already been submitted. Please try again if you still need to report a fault.';
                $reportSubmissionToken = $this->refreshReportSubmissionToken();
                $boats = $this->boatModel->getAllBoats();
                include '../src/Views/public/report_form.php';
                exit;
            }

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

    private function refreshReportSubmissionToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['report_submission_token'] = $token;
        return $token;
    }

    private function consumeReportSubmissionToken($submittedToken)
    {
        $sessionToken = $_SESSION['report_submission_token'] ?? null;
        if (!is_string($sessionToken) || $sessionToken === '' || $submittedToken === '') {
            return false;
        }

        $isValid = hash_equals($sessionToken, $submittedToken);
        if ($isValid) {
            unset($_SESSION['report_submission_token']);
        }

        return $isValid;
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