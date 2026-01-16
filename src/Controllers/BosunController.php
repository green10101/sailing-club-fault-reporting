<?php

namespace App\Controllers;

use App\Models\Report;
use App\Services\MailService;

class BosunController
{
    protected $reportModel;
    protected $mailService;

    public function __construct()
    {
        $this->reportModel = new Report();
        $this->mailService = new MailService();
    }

    public function dashboard()
    {
        try {
            $filter = $_GET['filter'] ?? 'all';
            $sortBy = $_GET['sort'] ?? 'created_at';
            $sortOrder = $_GET['order'] ?? 'DESC';
            $reports = $this->reportModel->getAllReports($filter, $sortBy, $sortOrder);
            include '../src/Views/bosun/dashboard.php';
        } catch (Exception $e) {
            // Log error and show basic dashboard
            error_log("Error in dashboard: " . $e->getMessage());
            $reports = $this->reportModel->getAllReports();
            include '../src/Views/bosun/dashboard.php';
        }
    }

    public function updateReportStatus($reportId, $status)
    {
        $this->reportModel->updateReportStatus($reportId, $status);
        // $this->mailService->sendRepairAssignedEmail('volunteer@example.com', $this->reportModel->getReportById($reportId)['boat_name'], $this->reportModel->getReportById($reportId)['fault_description']);
        header('Location: /bosun/dashboard');
    }

    public function updateReportNotes($reportId, $notes)
    {
        $this->reportModel->updateReportNotes($reportId, $notes);
        header('Location: /bosun/dashboard');
    }

    public function editReport($reportId)
    {
        $report = $this->reportModel->getReportById($reportId);
        if (!$report) {
            header('Location: /bosun/dashboard');
            exit;
        }
        include '../src/Views/bosun/edit.php';
    }

    public function updateReport($reportId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /bosun/dashboard');
            exit;
        }

        $boatName = $_POST['boat_name'] ?? '';
        $faultDescription = $_POST['fault_description'] ?? '';
        $status = $_POST['status'] ?? '';
        $bosunNotes = $_POST['bosun_notes'] ?? '';

        $this->reportModel->updateReport($reportId, $boatName, $faultDescription, $status, $bosunNotes);
        header('Location: /bosun/dashboard');
    }
}