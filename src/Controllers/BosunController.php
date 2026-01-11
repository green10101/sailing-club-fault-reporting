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
        $this->reportModel->updateStatus($reportId, $status);
        $this->mailService->sendRepairAssignedNotification($reportId);
        header('Location: /bosun/dashboard');
    }

    public function addReportNote($reportId, $note)
    {
        $this->reportModel->addNoteToReport($reportId, $note);
        header('Location: /bosun/dashboard');
    }
}