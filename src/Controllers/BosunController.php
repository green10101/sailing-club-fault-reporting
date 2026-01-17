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
            $filter = $_GET['filter'] ?? 'all';
            $sortBy = $_GET['sort'] ?? 'r.created_at';
            $sortOrder = $_GET['order'] ?? 'DESC';
            $boatId = $_GET['boat_id'] ?? null;
            $reports = $this->reportModel->getAllReports($filter, $sortBy, $sortOrder, $boatId);
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
            $boats = $this->boatModel->getAllBoats();
            $filteredBoat = null;
            include '../src/Views/bosun/dashboard.php';
        }
    }

    public function updateReportStatus($reportId, $status)
    {
        $this->reportModel->updateReportStatus($reportId, $status);
        // $this->mailService->sendRepairAssignedEmail('volunteer@example.com', $this->reportModel->getReportById($reportId)['boat_name'], $this->reportModel->getReportById($reportId)['fault_description']);
        header('Location: /bosun/dashboard');
    }

    public function boats()
    {
        $filter = $_GET['filter'] ?? 'all';
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
            header('Location: /bosun/boats');
        } else {
            // Handle error, perhaps log or show message
            error_log("Failed to update boat status for boat_id: $boatId");
            header('Location: /bosun/boats?error=1');
        }
    }

    public function editReport($reportId)
    {
        $report = $this->reportModel->getReportById($reportId);
        if (!$report) {
            header('Location: /bosun/dashboard');
            exit;
        }
        $boats = $this->boatModel->getAllBoats();
        include '../src/Views/bosun/edit.php';
    }

    public function editBoat($boatId)
    {
        $boat = $this->boatModel->getBoatById($boatId);
        if (!$boat) {
            header('Location: /bosun/boats');
            exit;
        }
        include '../src/Views/bosun/boat_edit.php';
    }

    public function updateBoat($boatId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /bosun/boats');
            exit;
        }

        $boatName = $_POST['boat_name'] ?? '';
        $boatType = $_POST['boat_type'] ?? '';
        $serialNumber = $_POST['serial_number'] ?? '';
        $status = $_POST['status'] ?? '';

        $this->boatModel->updateDetails($boatId, $boatName, $boatType, $serialNumber, $status);
        header('Location: /bosun/boats');
    }

    public function newBoat()
    {
        include '../src/Views/bosun/boat_new.php';
    }

    public function createBoat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /bosun/boats');
            exit;
        }

        $boatName = $_POST['boat_name'] ?? '';
        $boatType = $_POST['boat_type'] ?? '';
        $serialNumber = $_POST['serial_number'] ?? '';
        $status = $_POST['status'] ?? 'OK';

        try {
            $this->boatModel->createBoat($boatName, $boatType, $serialNumber, $status);
            header('Location: /bosun/boats');
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
            header('Location: /bosun/dashboard');
            exit;
        }

        $boatId = $_POST['boat_id'] ?? '';
        $faultDescription = $_POST['fault_description'] ?? '';
        $status = $_POST['status'] ?? '';
        $bosunNotes = $_POST['bosun_notes'] ?? '';

        $this->reportModel->updateReport($reportId, $boatId, $faultDescription, $status, $bosunNotes);
        header('Location: /bosun/dashboard');
    }
}