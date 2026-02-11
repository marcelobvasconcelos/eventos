<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

class Mailer {
    private $mail;

    public function __construct() {
        $config = require __DIR__ . '/../config/email.php';
        
        $this->mail = new PHPMailer(true);
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = $config['host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $config['username'];
            $this->mail->Password   = $config['password'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = $config['port'];
            $this->mail->CharSet    = 'UTF-8';

            // Default sender
            $this->mail->setFrom($config['from_email'], $config['from_name']);
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function send($to, $subject, $body) {
        try {
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            
            // Embed Banner Image
            $bannerPath = __DIR__ . '/banner.jpeg'; // Assuming banner.jpeg is in lib/
            if (file_exists($bannerPath)) {
                $this->mail->addEmbeddedImage($bannerPath, 'banner_img', 'banner.jpeg');
                $bannerHtml = '<div style="text-align: center; margin-bottom: 20px;"><img src="cid:banner_img" alt="Eventos UAST" style="max-width: 100%; height: auto; border-radius: 8px;"></div>';
            } else {
                // Fallback text header
                 $bannerHtml = '<div style="background-color: #f8f9fa; padding: 20px; text-align: center;"><h1 style="color: #2c3e50; margin: 0;">Eventos UAST</h1></div>';
            }
            
            $content = '<div style="padding: 20px; font-family: Arial, sans-serif; line-height: 1.6; color: #333;">' . $body . '</div>';
            
            $footer = '<div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #eee; margin-top: 20px;">';
            $footer .= '&copy; ' . date('Y') . ' UAST/UFRPE - Sistema de Eventos<br>';
            $footer .= 'Este é um e-mail automático, por favor não responda.';
            $footer .= '</div>';

            $this->mail->Body = '<div style="max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">' . $bannerHtml . $content . $footer . '</div>';
            $this->mail->AltBody = strip_tags($body);

            $this->mail->send();
            $this->mail->clearAddresses();
            $this->mail->clearAttachments(); // Clear embedded images for next send
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
