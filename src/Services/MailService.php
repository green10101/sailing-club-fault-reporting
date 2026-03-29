<?php

namespace src\Services;

class MailService
{
    public function sendRepairAssignedEmail($to, $boatName, $faultDescription)
    {
        return true;
    }

    public function sendRepairCompletedEmail($to, $boatName)
    {
        return true;
    }

    public function sendNewFaultReportEmail(array $reportData)
    {
        $to = $this->getNotificationRecipient();
        $fromAddress = $this->getFromAddress();
        $fromName = $this->getFromName();

        $reportId = (int) ($reportData['report_id'] ?? 0);
        $boatName = trim((string) ($reportData['boat_name'] ?? 'Unknown boat'));
        $reporterEmail = trim((string) ($reportData['reporter_email'] ?? ''));
        $reporterName = trim((string) ($reportData['reporter_name'] ?? ''));
        $faultDescription = trim((string) ($reportData['fault_description'] ?? ''));

        $subject = 'New Fault Report #' . $reportId . ' - ' . $boatName;
        $body = "A new fault report has been submitted.\n\n"
            . "Report ID: " . $reportId . "\n"
            . "Boat Name: " . $boatName . "\n"
            . "Reporter Email: " . $reporterEmail . "\n"
            . "Reporter Name: " . $reporterName . "\n"
            . "Fault Description:\n" . $faultDescription . "\n";

        $safeFromName = str_replace(["\r", "\n"], '', $fromName);
        $safeFromAddress = str_replace(["\r", "\n"], '', $fromAddress);
        $headers = [
            'From: ' . $safeFromName . ' <' . $safeFromAddress . '>',
            'Reply-To: ' . $safeFromAddress,
            'Content-Type: text/plain; charset=UTF-8',
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private function getNotificationRecipient()
    {
        $configured = $_ENV['MAIL_TO'] ?? getenv('MAIL_TO');
        return $configured ?: 'cycdinghysection@gmail.com';
    }

    private function getFromAddress()
    {
        $configured = $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS');
        return $configured ?: 'noreply@sailingclub.com';
    }

    private function getFromName()
    {
        $configured = $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME');
        return $configured ?: 'Sailing Club Fault Reporting';
    }
}
