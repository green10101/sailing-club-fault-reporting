<?php

namespace src\Services;

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class MailService
{
    // private $mailer;

    public function __construct()
    {
        // $this->mailer = new PHPMailer(true);
        // $this->configureMailer();
    }

    // private function configureMailer()
    // {
    //     $this->mailer->isSMTP();
    //     $this->mailer->Host = 'smtp.example.com'; // Set the SMTP server to send through
    //     $this->mailer->SMTPAuth = true;
    //     $this->mailer->Username = 'your_email@example.com'; // SMTP username
    //     $this->mailer->Password = 'your_password'; // SMTP password
    //     $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //     $this->mailer->Port = 587; // TCP port to connect to
    // }

    public function sendRepairAssignedEmail($to, $boatName, $faultDescription)
    {
        // Placeholder for email sending
        // try {
        //     $this->mailer->setFrom('from@example.com', 'Sailing Club');
        //     $this->mailer->addAddress($to);
        //     $this->mailer->Subject = 'Repair Job Assigned';
        //     $this->mailer->Body = "A repair job has been assigned for the boat: $boatName.\nFault Description: $faultDescription";
        //     $this->mailer->send();
        // } catch (Exception $e) {
        //     // Handle error
        // }
    }

    public function sendRepairCompletedEmail($to, $boatName)
    {
        // Placeholder
        // try {
        //     $this->mailer->setFrom('from@example.com', 'Sailing Club');
        //     $this->mailer->addAddress($to);
        //     $this->mailer->Subject = 'Repair Job Completed';
        //     $this->mailer->Body = "The repair job for the boat: $boatName has been completed.";
        //     $this->mailer->send();
        // } catch (Exception $e) {
        //     // Handle error
        // }
    }
}