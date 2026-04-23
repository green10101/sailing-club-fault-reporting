<?php

namespace src\Controllers;

use src\Models\Report;
use src\Models\Boat;
use src\Models\BoatCheckin;
use src\Services\MailService;

class PublicController
{
    protected $reportModel;
    protected $boatModel;
    protected $boatCheckinModel;
    protected $mailService;

    public function __construct()
    {
        $this->reportModel = new Report();
        $this->boatModel = new Boat();
        $this->boatCheckinModel = new BoatCheckin();
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
                    $notificationsSuppressed = $this->mailService->notificationsAreSuppressed();
                    $emailSent = $this->mailService->sendNewFaultReportEmail([
                        'report_id' => $reportId,
                        'boat_name' => $boatName,
                        'reporter_email' => $reporterEmail,
                        'reporter_name' => $reporterName,
                        'fault_description' => $faultDescription,
                    ]);

                    if ($emailSent) {
                        if ($notificationsSuppressed) {
                            error_log('New fault report email suppressed by test mode for report #' . $reportId);
                        } else {
                            error_log('New fault report email sent for report #' . $reportId);
                        }
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

    public function showCheckinForm($error = null, $old = [])
    {
        $checkinSubmissionToken = $this->refreshCheckinSubmissionToken();
        $boats = $this->boatModel->getAllBoats();
        include '../src/Views/public/checkin_form.php';
    }

    public function submitCheckin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/checkin');
            exit;
        }

        $boatId = (int) ($_POST['boat_id'] ?? 0);
        $userName = trim((string) ($_POST['user_name'] ?? ''));
        $userEmail = strtolower(trim((string) ($_POST['user_email'] ?? '')));
        $putAwayOkRaw = strtolower(trim((string) ($_POST['put_away_ok'] ?? '')));
        $safeForNextUserRaw = strtolower(trim((string) ($_POST['safe_for_next_user'] ?? '')));
        $hasFaultsToRectifyRaw = strtolower(trim((string) ($_POST['has_faults_to_rectify'] ?? '')));
        $damageDuringCheckoutRaw = strtolower(trim((string) ($_POST['damage_during_checkout'] ?? '')));
        $faultDescription = trim((string) ($_POST['fault_description'] ?? ''));
        $checkinNotes = trim((string) ($_POST['checkin_notes'] ?? ''));

        $old = [
            'boat_id' => $boatId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'put_away_ok' => $putAwayOkRaw,
            'safe_for_next_user' => $safeForNextUserRaw,
            'has_faults_to_rectify' => $hasFaultsToRectifyRaw,
            'damage_during_checkout' => $damageDuringCheckoutRaw,
            'fault_description' => $faultDescription,
            'checkin_notes' => $checkinNotes,
        ];

        if (!$this->consumeCheckinSubmissionToken((string) ($_POST['checkin_submission_token'] ?? ''))) {
            $this->showCheckinForm('This form has already been submitted. Please start a new check-in.', $old);
            exit;
        }

        if ($boatId <= 0 || $userName === '' || $userEmail === '') {
            $this->showCheckinForm('Please complete all required fields.', $old);
            exit;
        }

        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $this->showCheckinForm('Please provide a valid email address.', $old);
            exit;
        }

        if (!in_array($putAwayOkRaw, ['yes', 'no'], true)
            || !in_array($safeForNextUserRaw, ['yes', 'no'], true)
            || !in_array($hasFaultsToRectifyRaw, ['yes', 'no'], true)
        ) {
            $this->showCheckinForm('Please answer all checklist questions.', $old);
            exit;
        }

        $boat = $this->boatModel->getBoatById($boatId);
        if (!$boat || (($boat['status'] ?? '') === 'Retired')) {
            $this->showCheckinForm('Please choose a valid active asset.', $old);
            exit;
        }

        $safeForNextUser = ($safeForNextUserRaw === 'yes');
        // Q3 is phrased as "fault-free". "No" means faults exist and should be rectified.
        $isFaultFree = ($hasFaultsToRectifyRaw === 'yes');
        $hasFaultsToRectify = !$isFaultFree;
        $requiresFaultDetails = (!$safeForNextUser) || $hasFaultsToRectify;

        $damageDuringCheckout = null;
        if ($requiresFaultDetails) {
            if (!in_array($damageDuringCheckoutRaw, ['yes', 'no'], true)) {
                $this->showCheckinForm('Please confirm whether damage happened during this checkout.', $old);
                exit;
            }
            if ($faultDescription === '') {
                $this->showCheckinForm('Please provide fault details when safety or fault concerns are raised.', $old);
                exit;
            }
            $damageDuringCheckout = ($damageDuringCheckoutRaw === 'yes');
        }

        $checkinId = $this->boatCheckinModel->create([
            'boat_id' => $boatId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'put_away_ok' => ($putAwayOkRaw === 'yes'),
            'safe_for_next_user' => $safeForNextUser,
            'has_faults_to_rectify' => $hasFaultsToRectify,
            'damage_during_checkout' => $damageDuringCheckout,
            'checkin_notes' => $checkinNotes,
        ]);

        if ($checkinId === false) {
            error_log('Boat check-in could not be created for boat #' . $boatId);
            $this->showCheckinForm('We could not save your check-in at this time. Please try again.', $old);
            exit;
        }

        if ($requiresFaultDetails) {
            $reportId = $this->reportModel->createFromCheckin(
                $boatId,
                $faultDescription,
                $userName,
                $userEmail,
                $checkinId
            );

            if ($reportId !== false) {
                $this->boatCheckinModel->updateFaultReportId($checkinId, (int) $reportId);
            } else {
                error_log('Fault report creation from check-in failed for checkin #' . $checkinId);
            }
        }

        header('Location: index.php?route=/thanks&type=checkin');
        exit;
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

    private function refreshCheckinSubmissionToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['checkin_submission_token'] = $token;
        return $token;
    }

    private function consumeCheckinSubmissionToken($submittedToken)
    {
        $sessionToken = $_SESSION['checkin_submission_token'] ?? null;
        if (!is_string($sessionToken) || $sessionToken === '' || $submittedToken === '') {
            return false;
        }

        $isValid = hash_equals($sessionToken, $submittedToken);
        if ($isValid) {
            unset($_SESSION['checkin_submission_token']);
        }

        return $isValid;
    }

    public function showThanks()
    {
        $isBosun = isset($_SESSION['user']);
        $submissionType = ($_GET['type'] ?? 'fault') === 'checkin' ? 'checkin' : 'fault';
        include '../src/Views/public/thanks.php';
    }

    public function showLogin()
    {
        include '../src/Views/public/login.php';
    }
}