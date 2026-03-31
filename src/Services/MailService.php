<?php

namespace src\Services;

use src\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
            . "Fault Description:\n\n" . $faultDescription . "\n\n"
            . "https://cyc.uk/bosun/index.php?route=/bosun/dashboard";

        try {
            $mail = $this->createMailer();
            $recipients = $this->getNotificationRecipients();
            foreach ($recipients as $recipient) {
                $mail->addAddress($recipient);
            }
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('MailService::sendNewFaultReportEmail failed: ' . $e->getMessage());
            return false;
        }
    }

    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $this->env('MAIL_HOST', 'localhost');
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->env('MAIL_USERNAME', '');
        $mail->Password   = $this->env('MAIL_PASSWORD', '');
        $mail->Port       = (int) $this->env('MAIL_PORT', '587');

        $encryption = strtolower($this->env('MAIL_ENCRYPTION', 'tls'));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom(
            $this->getFromAddress(),
            $this->getFromName()
        );

        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        return $mail;
    }

    private function getNotificationRecipients(): array
    {
        $recipients = User::getFaultNotificationEmails();
        if (!empty($recipients)) {
            return $recipients;
        }

        return [$this->env('MAIL_TO', 'cycdinghysection@gmail.com')];
    }

    private function getFromAddress(): string
    {
        return $this->env('MAIL_FROM_ADDRESS', 'noreply@sailingclub.com');
    }

    private function getFromName(): string
    {
        return $this->env('MAIL_FROM_NAME', 'Sailing Club Fault Reporting');
    }

    private function env(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value !== false && $value !== '') ? $value : $default;
    }
}

