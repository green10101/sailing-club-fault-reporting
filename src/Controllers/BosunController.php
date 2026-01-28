<?php

namespace App\Controllers;

use App\Models\Report;
use App\Models\Boat;
use App\Services\MailService;

class BosunController
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
            
            $boats = $this->boatModel->getAllBoats();
            $filteredBoat = null;
            if ($boatId) {
                $filteredBoat = $this->boatModel->getBoatById($boatId);
            }
            include '../src/Views/bosun/dashboard.php';
        } catch (Exception $e) {
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
        foreach ($boats as &$boat) {
            $boat['active_faults'] = $this->boatModel->getActiveFaultCount($boat['id']);
        }
        unset($boat); // break reference to avoid unexpected foreach behavior
        include '../src/Views/bosun/boats.php';
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
        $boat = $this->boatModel->getBoatById($boatId);
        if (!$boat) {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }
        include '../src/Views/bosun/boat_edit.php';
    }

    public function updateBoat($boatId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/bosun/boats');
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/bosun/dashboard');
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
        // Get all active faults (statuses: New, In progress, Waiting parts)
        $reports = $this->reportModel->getAllReports('active', 'b.boat_name', 'ASC', null);
        
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
}
