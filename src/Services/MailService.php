<?php

namespace src\Services;

use src\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public function notificationsAreSuppressed()
    {
        return $this->envFlag('MAIL_TEST_MODE', false)
            || $this->envFlag('DISABLE_NOTIFICATIONS', false);
    }

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
        $fromCheckin = (($reportData['source'] ?? '') === 'boat_checkin');
        $isSevere = (bool) ($reportData['is_severe'] ?? false);
        $reportedAt = trim((string) ($reportData['reported_at'] ?? date('Y-m-d H:i:s')));

        $sourceLabel = $fromCheckin ? 'Boat Check-In' : 'Fault Form';
        $severityHeading = $isSevere
            ? "SEVERE ISSUE: Safety / Not Operational"
            : "Standard Fault Report";

        $faultSection = ($faultDescription !== '') ? $faultDescription : '(No description provided)';

        $subjectPrefix = $isSevere ? '[SEVERE] ' : '';
        $subject = $subjectPrefix . 'Fault Report #' . $reportId . ' - ' . $boatName;
        $body = "FAULT ALERT\n"
            . "===========\n"
            . $severityHeading . "\n\n"
            . "Boat: " . $boatName . "\n\n"
            . "Fault Description:\n"
            . "------------------\n"
            . $faultSection . "\n\n"
            . "Additional Details\n"
            . "------------------\n"
            . "Report ID: " . $reportId . "\n"
            . "Source: " . $sourceLabel . "\n"
            . "Reported By: " . $reporterName . "\n"
            . "Reporter Email: " . $reporterEmail . "\n"
            . "Reported At: " . $reportedAt . "\n\n"
            . "Open in bosun dashboard:\n"
            . "https://cyc.uk/bosun/index.php";

        try {
            $recipients = $this->getNotificationRecipients();

            if ($this->notificationsAreSuppressed()) {
                error_log(
                    'MailService::sendNewFaultReportEmail suppressed by test mode for report #' . $reportId
                    . '; recipients=' . implode(',', $recipients)
                );
                return true;
            }

            $mail = $this->createMailer();
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
        return $this->env('MAIL_FROM_ADDRESS', 'no-reply@cyc.uk');
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

    private function envFlag(string $key, bool $default): bool
    {
        $defaultValue = $default ? '1' : '0';
        $value = strtolower(trim($this->env($key, $defaultValue)));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}

