<?php

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notification {

    private $pdo;
    private $mail;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $config = require __DIR__ . '/../config/email.php';
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = $config['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $config['username'];
        $this->mail->Password = $config['password'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = $config['port'];
        $this->mail->setFrom($config['from_email'], $config['from_name']);
    }

    public function sendConfirmation($userId, $eventId) {
        $stmt = $this->pdo->prepare("SELECT u.email, e.name FROM users u JOIN events e ON e.id = ? WHERE u.id = ?");
        $stmt->execute([$eventId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            try {
                $this->mail->addAddress($data['email']);
                $this->mail->Subject = 'Event Request Confirmation';
                $this->mail->Body = "Your request for event '{$data['name']}' has been submitted.";
                $this->mail->send();
                $this->mail->clearAddresses();
            } catch (Exception $e) {
                // Log error or handle
            }
        }
    }

    public function sendApproval($userId, $eventId, $approved) {
        $stmt = $this->pdo->prepare("SELECT u.email, e.name FROM users u JOIN events e ON e.id = ? WHERE u.id = ?");
        $stmt->execute([$eventId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $status = $approved ? 'approved' : 'rejected';
            try {
                $this->mail->addAddress($data['email']);
                $this->mail->Subject = "Event Request $status";
                $this->mail->Body = "Your request for event '{$data['name']}' has been $status.";
                $this->mail->send();
                $this->mail->clearAddresses();
            } catch (Exception $e) {
                // Log error
            }
        }
    }

    public function sendReminder($userId, $assetId, $dueDate) {
        $stmt = $this->pdo->prepare("SELECT u.email, a.name FROM users u JOIN assets a ON a.id = ? WHERE u.id = ?");
        $stmt->execute([$assetId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            try {
                $this->mail->addAddress($data['email']);
                $this->mail->Subject = 'Loan Reminder';
                $this->mail->Body = "Please return the asset '{$data['name']}' by $dueDate.";
                $this->mail->send();
                $this->mail->clearAddresses();
            } catch (Exception $e) {
                // Log error
            }
        }
    }

    public function checkOverdueLoans() {
        $stmt = $this->pdo->prepare("SELECT l.user_id, l.asset_id, l.return_date FROM loans l WHERE l.status = 'Emprestado' AND l.return_date < NOW()");
        $stmt->execute();
        $overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($overdue as $loan) {
            $this->sendReminder($loan['user_id'], $loan['asset_id'], $loan['return_date']);
        }
    }
}