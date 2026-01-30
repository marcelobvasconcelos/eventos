<?php
require_once __DIR__ . '/../models/Config.php';
require_once __DIR__ . '/../lib/Security.php';

class SettingsController {

    private function checkAdminAccess() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /eventos/');
            exit;
        }
    }

    public function index() {
        $this->checkAdminAccess();
        
        $configModel = new Config();
        $configs = $configModel->getAll();
        
        // Define expected keys to ensure they exist in the array
        $defaultKeys = [
            'home_banner_image', 
            'event_card_default_image', 
            'footer_social_instagram', 
            'footer_social_facebook', 
            'footer_social_youtube', 
            'footer_text',
            'event_creation_info_text',
            'request_info_text',
            'normative_pdf',
            'footer_logo_1',
            'footer_logo_2',
            'footer_col1_title',
            'footer_col1_subtitle',
            'footer_address',
            'footer_email',
            'footer_phone'
        ];
        
        foreach ($defaultKeys as $key) {
            if (!isset($configs[$key])) {
                $configs[$key] = '';
            }
        }

        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/config.php';
    }

    public function update() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/settings?error=Token CSRF inválido');
                exit;
            }

            $configModel = new Config();
            $allowedKeys = [
                'footer_social_instagram', 
                'footer_social_facebook', 
                'footer_social_youtube', 
                'footer_text',
                'event_creation_info_text',
                'request_info_text',
                'footer_col1_title',
                'footer_col1_subtitle',
                'footer_address',
                'footer_email',
                'footer_phone'
            ];

            // Update text fields
            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $configModel->update($key, $_POST[$key]);
                }
            }

            // Handle File Uploads
            $uploadDir = __DIR__ . '/../public/uploads/config/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // 1. Handle Images
            $imageFields = ['home_banner_image', 'event_card_default_image', 'footer_logo_1', 'footer_logo_2'];
            
            foreach ($imageFields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES[$field]['tmp_name'];
                    $fileName = $_FILES[$field]['name'];
                    $fileSize = $_FILES[$field]['size'];
                    $fileType = $_FILES[$field]['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    $allowedfileExtensions = array('jpg', 'gif', 'png', 'webp', 'jpeg');

                    if (in_array($fileExtension, $allowedfileExtensions)) {
                        $newFileName = $field . '.' . $fileExtension;
                        $dest_path = $uploadDir . $newFileName;
                        
                        if(move_uploaded_file($fileTmpPath, $dest_path)) {
                            // Save relative path to DB
                            $dbPath = 'public/uploads/config/' . $newFileName;
                            $configModel->update($field, $dbPath);
                        }
                    }
                }
            }

            // 2. Handle Normative PDF
            if (isset($_FILES['normative_pdf']) && $_FILES['normative_pdf']['error'] === UPLOAD_ERR_OK) {
                $field = 'normative_pdf';
                $fileTmpPath = $_FILES[$field]['tmp_name'];
                $fileName = $_FILES[$field]['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                if ($fileExtension === 'pdf') {
                    $newFileName = 'normative_orientation.pdf'; // Fixed name or timestamped
                    $dest_path = $uploadDir . $newFileName;
                    
                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $dbPath = 'public/uploads/config/' . $newFileName;
                        $configModel->update($field, $dbPath);
                    }
                }
            }

            header('Location: /eventos/settings?success=Configurações atualizadas');
            exit;
        }
    }
}
