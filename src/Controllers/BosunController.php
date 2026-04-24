<?php

namespace src\Controllers;

use src\Models\Report;
use src\Models\Boat;
use src\Models\BoatCheckin;
use src\Services\MailService;

class BosunController
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

    public function dashboard()
    {
        try {
            $status = $_GET['status'] ?? null;
            // Determine the filter based on status parameter:
            // - If status is 'All', show all reports
            // - If a specific status is selected (New, In progress, etc), don't apply base filter
            // - If no status is selected (null), show active faults only
            $filter = 'all';
            if ($status === null) {
                $filter = 'active';
            }
            
            $sortBy = $_GET['sort'] ?? 'r.reported_at';
            $sortOrder = $_GET['order'] ?? 'DESC';
            $boatId = $_GET['boat_id'] ?? null;
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 50; // Show 50 reports per page
            
            $reports = $this->reportModel->getAllReports($filter, $sortBy, $sortOrder, $boatId, $status, $page, $perPage);
            $totalReports = $this->reportModel->getReportsCount($filter, $boatId, $status);
            $totalPages = ceil($totalReports / $perPage);
            
            // Calculate counts for each status filter
            $countAllActive = $this->reportModel->getReportsCount('active', $boatId, null);
            $countAllReports = $this->reportModel->getReportsCount('all', $boatId, 'All');
            $countNew = $this->reportModel->getReportsCount('all', $boatId, 'New');
            $countInProgress = $this->reportModel->getReportsCount('all', $boatId, 'In progress');
            $countWaitingParts = $this->reportModel->getReportsCount('all', $boatId, 'Waiting parts');
            $countComplete = $this->reportModel->getReportsCount('all', $boatId, 'Complete');
            
            $boats = $this->boatModel->getAllBoats();
            $filteredBoat = null;
            if ($boatId) {
                $filteredBoat = $this->boatModel->getBoatById($boatId);
            }
            include '../src/Views/bosun/dashboard.php';
        } catch (\Exception $e) {
            // Log error and show basic dashboard
            error_log("Error in dashboard: " . $e->getMessage());
            $reports = $this->reportModel->getAllReports();
            $totalReports = 0;
            $totalPages = 1;
            $page = 1;
            $boats = $this->boatModel->getAllBoats();
            $filteredBoat = null;
            include '../src/Views/bosun/dashboard.php';
        }
    }

    public function updateReportStatus($reportId, $status)
    {
        $this->reportModel->updateReportStatus($reportId, $status);
        // $this->mailService->sendRepairAssignedEmail('volunteer@example.com', $this->reportModel->getReportById($reportId)['boat_name'], $this->reportModel->getReportById($reportId)['fault_description']);
        header('Location: index.php?route=/bosun/dashboard');
    }

    public function boats()
    {
        $filter = $_GET['filter'] ?? 'current';
        $sortBy = $_GET['sort'] ?? 'boat_name';
        $sortOrder = $_GET['order'] ?? 'ASC';
        $boats = $this->boatModel->getBoatsFilteredSorted($filter, $sortBy, $sortOrder);
        $activeFaultCounts = $this->boatModel->getActiveFaultCountsByBoatId();
        $useCounts = $this->boatModel->getUseCountsByBoatId();
        foreach ($boats as &$boat) {
            $boatId = (int) $boat['id'];
            $boat['active_faults'] = $activeFaultCounts[$boatId] ?? 0;
            $boat['number_of_uses'] = $useCounts[$boatId] ?? 0;
        }
        unset($boat); // break reference to avoid unexpected foreach behavior
        include '../src/Views/bosun/boats.php';
    }

    public function checkins()
    {
        $boatId = isset($_GET['boat_id']) && $_GET['boat_id'] !== '' ? (int) $_GET['boat_id'] : null;
        $faultFilter = $_GET['fault_filter'] ?? 'all';
        if (!in_array($faultFilter, ['all', 'with_fault', 'without_fault'], true)) {
            $faultFilter = 'all';
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 50;

        $checkins = $this->boatCheckinModel->getCheckins($boatId, $faultFilter, $page, $perPage);
        $totalCheckins = $this->boatCheckinModel->getCheckinsCount($boatId, $faultFilter);
        $totalPages = max(1, (int) ceil($totalCheckins / $perPage));
        $boats = $this->boatModel->getAllBoats();

        include '../src/Views/bosun/checkins.php';
    }

    public function exportCheckinsCsv()
    {
        $boatId = isset($_GET['boat_id']) && $_GET['boat_id'] !== '' ? (int) $_GET['boat_id'] : null;
        $faultFilter = $_GET['fault_filter'] ?? 'all';
        if (!in_array($faultFilter, ['all', 'with_fault', 'without_fault'], true)) {
            $faultFilter = 'all';
        }

        $checkins = $this->boatCheckinModel->getCheckinsForExport($boatId, $faultFilter);
        $timestamp = date('Ymd_His');
        $filename = 'boat_checkins_' . $timestamp . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit;
        }

        fputcsv($output, [
            'Check-In ID',
            'Checked In At',
            'Boat ID',
            'Boat Name',
            'User Name',
            'User Email',
            'Put Away OK',
            'Safe For Next User',
            'Has Faults To Rectify',
            'Damage During Checkout',
            'Check-In Notes',
            'Fault Report ID',
            'Fault Report Status',
            'Fault Description',
        ]);

        foreach ($checkins as $checkin) {
            $damageDuringCheckout = '';
            if ($checkin['damage_during_checkout'] !== null) {
                $damageDuringCheckout = ((int) $checkin['damage_during_checkout'] === 1) ? 'Yes' : 'No';
            }

            fputcsv($output, [
                (int) $checkin['id'],
                (string) ($checkin['checked_in_at'] ?? ''),
                (int) ($checkin['boat_id'] ?? 0),
                (string) ($checkin['boat_name'] ?? ''),
                (string) ($checkin['user_name'] ?? ''),
                (string) ($checkin['user_email'] ?? ''),
                ((int) ($checkin['put_away_ok'] ?? 0) === 1) ? 'Yes' : 'No',
                ((int) ($checkin['safe_for_next_user'] ?? 0) === 1) ? 'Yes' : 'No',
                ((int) ($checkin['has_faults_to_rectify'] ?? 0) === 1) ? 'Yes' : 'No',
                $damageDuringCheckout,
                (string) ($checkin['checkin_notes'] ?? ''),
                !empty($checkin['fault_report_id']) ? (int) $checkin['fault_report_id'] : '',
                (string) ($checkin['fault_report_status'] ?? ''),
                (string) ($checkin['fault_description'] ?? ''),
            ]);
        }

        fclose($output);
        exit;
    }

    public function updateBoatStatus($boatId, $status)
    {
        if ($this->boatModel->updateStatus($boatId, $status)) {
            header('Location: index.php?route=/bosun/boats');
        } else {
            // Handle error, perhaps log or show message
            error_log("Failed to update boat status for boat_id: $boatId");
            header('Location: index.php?route=/bosun/boats&error=1');
        }
    }

    public function editReport($reportId)
    {
        // Verify user is authenticated
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }

        $report = $this->reportModel->getReportById($reportId);
        if (!$report) {
            header('Location: index.php?route=/bosun/dashboard');
            exit;
        }
        $boats = $this->boatModel->getAllBoats();
        include '../src/Views/bosun/edit.php';
    }

    public function editBoat($boatId)
    {
        // Verify user is authenticated and is a bosun
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'bosun' && $_SESSION['user']['role'] !== 'admin')) {
            header('Location: index.php?route=/login');
            exit;
        }

        $boat = $this->boatModel->getBoatById($boatId);
        if (!$boat) {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }
        include '../src/Views/bosun/boat_edit.php';
    }

    public function updateBoat($boatId)
    {
        // Verify user is authenticated and is a bosun
        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'bosun' && $_SESSION['user']['role'] !== 'admin')) {
            header('Location: index.php?route=/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            header('Location: index.php?route=/bosun/boats?error=csrf');
            exit;
        }

        $boatName = $_POST['boat_name'] ?? '';
        $boatType = $_POST['boat_type'] ?? '';
        $serialNumber = $_POST['serial_number'] ?? '';
        $status = $_POST['status'] ?? '';

        $this->boatModel->updateDetails($boatId, $boatName, $boatType, $serialNumber, $status);
        header('Location: index.php?route=/bosun/boats');
    }

    public function newBoat()
    {
        include '../src/Views/bosun/boat_new.php';
    }

    public function createBoat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            header('Location: index.php?route=/bosun/boats?error=csrf');
            exit;
        }

        $boatName = $_POST['boat_name'] ?? '';
        $boatType = $_POST['boat_type'] ?? '';
        $serialNumber = $_POST['serial_number'] ?? '';
        $status = $_POST['status'] ?? 'OK';

        try {
            $this->boatModel->createBoat($boatName, $boatType, $serialNumber, $status);
            header('Location: index.php?route=/bosun/boats');
        } catch (\PDOException $e) {
            $error = 'Failed to create boat: ' . $e->getMessage();
            // Re-render form with previous values
            $prefill = compact('boatName','boatType','serialNumber','status');
            include '../src/Views/bosun/boat_new.php';
        }
    }

    public function updateReport($reportId)
    {
        // Verify user is authenticated
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/bosun/dashboard');
            exit;
        }

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            header('Location: index.php?route=/bosun/edit/' . $reportId . '?error=csrf');
            exit;
        }

        $boatId = $_POST['boat_id'] ?? '';
        $faultDescription = $_POST['fault_description'] ?? '';
        $status = $_POST['status'] ?? '';
        $bosunNotes = $_POST['bosun_notes'] ?? '';
        $bosunAssessment = $_POST['bosun_assessment'] ?? '';
        $partRequired = $_POST['part_required'] ?? '';
        $partStatus = $_POST['part_status'] ?? '';
        $manualCompletionDate = $_POST['completion_date'] ?? '';

        // Handle completion date
        $completionDate = null;
        
        // If manually set, use that date
        if (!empty($manualCompletionDate)) {
            $completionDate = $manualCompletionDate . ' 00:00:00';
        }
        // Otherwise, if status is Complete and no manual date, auto-set to now
        elseif ($status === 'Complete') {
            $currentReport = $this->reportModel->getReportById($reportId);
            if (empty($currentReport['completion_date'])) {
                $completionDate = date('Y-m-d H:i:s');
            } else {
                $completionDate = $currentReport['completion_date'];
            }
        }

        $this->reportModel->updateReport($reportId, $boatId, $faultDescription, $status, $bosunNotes, $bosunAssessment, $partRequired, $partStatus, $completionDate);
        header('Location: index.php?route=/bosun/dashboard');
    }

    public function printReport()
    {
        // Check if specific report IDs are requested (from multi-select)
        $reportIds = isset($_GET['report_ids']) ? explode(',', $_GET['report_ids']) : null;
        
        if ($reportIds) {
            // Get only selected reports
            $reports = $this->reportModel->getReportsByIds($reportIds);
        } else {
            // Get all active faults (statuses: New, In progress, Waiting parts)
            $reports = $this->reportModel->getAllReports('active', 'b.boat_name', 'ASC', null);
        }
        
        // Group faults by boat
        $boatGroups = [];
        $totalFaults = 0;
        
        foreach ($reports as $report) {
            $boatName = $report['boat_name'];
            if (!isset($boatGroups[$boatName])) {
                $boatGroups[$boatName] = [
                    'boat_type' => $report['boat_type'] ?? 'Unknown',
                    'faults' => []
                ];
            }
            $boatGroups[$boatName]['faults'][] = $report;
            $totalFaults++;
        }
        
        // Sort boats alphabetically
        ksort($boatGroups);
        
        include '../src/Views/bosun/print_report.php';
    }

    public function exportReportsCsv()
    {
        $status = $_GET['status'] ?? null;
        $filter = 'all';
        if ($status === null) {
            $filter = 'active';
        }

        $sortBy = $_GET['sort'] ?? 'r.reported_at';
        $sortOrder = $_GET['order'] ?? 'DESC';
        $boatId = $_GET['boat_id'] ?? null;
        if ($boatId === '') {
            $boatId = null;
        }

        $reports = $this->reportModel->getReportsForExport($filter, $sortBy, $sortOrder, $boatId, $status);

        $timestamp = date('Ymd_His');
        $filename = 'fault_reports_' . $timestamp . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit;
        }

        fputcsv($output, [
            'Report ID',
            'Reported At',
            'Boat ID',
            'Boat Name',
            'Boat Type',
            'Fault Description',
            'Reporter Name',
            'Reporter Email',
            'Status',
            'Source',
            'Bosun Notes',
            'Bosun Assessment',
            'Part Required',
            'Part Status',
            'Completion Date',
        ]);

        foreach ($reports as $report) {
            fputcsv($output, [
                (int) ($report['id'] ?? 0),
                (string) ($report['reported_at'] ?? ''),
                !empty($report['boat_id']) ? (int) $report['boat_id'] : '',
                (string) ($report['boat_name'] ?? ''),
                (string) ($report['boat_type'] ?? ''),
                (string) ($report['fault_description'] ?? ''),
                (string) ($report['reporter_name'] ?? ''),
                (string) ($report['reporter_email'] ?? ''),
                (string) ($report['status'] ?? ''),
                (string) ($report['source'] ?? ''),
                (string) ($report['bosun_notes'] ?? ''),
                (string) ($report['bosun_assessment'] ?? ''),
                (string) ($report['part_required'] ?? ''),
                (string) ($report['part_status'] ?? ''),
                (string) ($report['completion_date'] ?? ''),
            ]);
        }

        fclose($output);
        exit;
    }
}
