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
        // Charset
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendConfirmation($userId, $eventId) {
        $stmt = $this->pdo->prepare("SELECT u.email, e.name FROM users u JOIN events e ON e.id = ? WHERE u.id = ?");
        $stmt->execute([$eventId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            try {
                $this->mail->addAddress($data['email']);
                $this->mail->Subject = 'Confirmação de Solicitação de Evento';
                $this->mail->Body = "Sua solicitação para o evento '{$data['name']}' foi enviada.";
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
            $status = $approved ? 'aprovado' : 'rejeitado';
            try {
                $this->mail->addAddress($data['email']);
                $this->mail->Subject = "Solicitação de Evento $status";
                $this->mail->Body = "Sua solicitação para o evento '{$data['name']}' foi $status.";
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
                $this->mail->Subject = 'Lembrete de Devolução';
                $this->mail->Body = "Por favor, devolva o item '{$data['name']}' até $dueDate.";
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

    // New method for pending items
    public function sendPendingReturnNotification($userId, $eventName, $pendingItems) {
        $stmt = $this->pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            try {
                $this->mail->addAddress($user['email']);
                $this->mail->Subject = 'Lembrete de Devolução - Evento Finalizado';
                
                $body = "Olá, {$user['name']}.<br><br>";
                $body .= "O evento '<strong>{$eventName}</strong>' foi finalizado.<br>";
                $body .= "Você possui as seguintes pendências de devolução:<br><ul>";
                
                foreach ($pendingItems as $item) {
                     $body .= "<li>{$item}</li>";
                }
                
                $body .= "</ul><br>Por favor, acesse o sistema para informar a devolução ou procure o setor responsável.<br>";
                
                $this->mail->isHTML(true);
                $this->mail->Body = $body;
                $this->mail->AltBody = strip_tags($body); // Plain text version
                
                $this->mail->send();
                $this->mail->clearAddresses();
                return true;
            } catch (Exception $e) {
                // Log error
                return false;
            }
        }
        return false;
    }
}