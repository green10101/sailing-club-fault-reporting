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
        $reports = $this->reportModel->getAllReports();
        include '../src/Views/bosun/dashboard.php';
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
}