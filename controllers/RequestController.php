<?php

require_once __DIR__ . '/../models/EventRequest.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../lib/Security.php';

class RequestController {

    public function form() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }

        $locationModel = new Location();
        $assetModel = new Asset();
        
        require_once __DIR__ . '/../models/Config.php';
        $configModel = new Config();
        $globalConfigs = $configModel->getAll();
        
        // Handle end_date for availability check
        $checkDate = $_POST['date'] ?? ($_GET['date'] ?? date('Y-m-d'));
        $checkEndDate = $_POST['end_date'] ?? ($_GET['end_date'] ?? $checkDate);
        
        $startDateTime = $checkDate . ' ' . ($_POST['time'] ?? '00:00');
        $endDateTime = $checkEndDate . ' ' . ($_POST['end_time'] ?? '23:59');

        $categoryModel = new Category();
        $locations = $locationModel->getLocationsWithAvailability($startDateTime, $endDateTime);
        $categories = $categoryModel->getAllCategories();
        $assets = $assetModel->getAllAssetsWithAvailability($startDateTime, $endDateTime);
        
        $csrf_token = Security::generateCsrfToken();

        include __DIR__ . '/../views/request/form.php';
    }

    public function my_requests() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        $requestModel = new EventRequest();
        $requests = $requestModel->getRequestsByUserId($_SESSION['user_id']);

        include __DIR__ . '/../views/request/my_requests.php';
    }

    public function submit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /eventos/request/form');
            exit;
        }

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $errorMessages = 'Invalid CSRF token';
            $csrf_token = Security::generateCsrfToken();
            
            // Fetch necessary data for the view
            $locationModel = new Location();
            $categoryModel = new Category();
            $assetModel = new Asset();
            
            require_once __DIR__ . '/../models/Config.php';
            $configModel = new Config();
            $globalConfigs = $configModel->getAll();
            
            // Re-use posted dates or default to now for availability
            $checkDate = $_POST['date'] ?? date('Y-m-d');
            $checkEndDate = $_POST['end_date'] ?? $checkDate;
            $startDateTime = $checkDate . ' ' . ($_POST['time'] ?? '00:00');
            $endDateTime = $checkEndDate . ' ' . ($_POST['end_time'] ?? '23:59');

            $locations = $locationModel->getLocationsWithAvailability($startDateTime, $endDateTime);
            $categories = $categoryModel->getAllCategories();
            $assets = $assetModel->getAllAssetsWithAvailability($startDateTime, $endDateTime);

            include __DIR__ . '/../views/request/form.php';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $endDateInput = !empty($_POST['end_date']) ? $_POST['end_date'] : $date;
        $categoryId = (int)($_POST['category'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $assets = $_POST['assets'] ?? [];
        $quantities = $_POST['quantities'] ?? [];

        $errors = [];

        if (empty($title)) $errors[] = 'Título é obrigatório.';
        if (empty($date)) $errors[] = 'Data é obrigatória.';
        if (empty($time)) $errors[] = 'Hora de início é obrigatória.';

        $locationPost = $_POST['location'] ?? '';
        $customLocation = null;
        $locationId = null;

        if ($locationPost === 'other') {
            $customLocation = trim($_POST['custom_location'] ?? '');
            if (empty($customLocation)) {
                $errors[] = 'Nome do local é obrigatório para "Outros".';
            }
        } else {
            $locationId = (int)$locationPost;
            if (empty($locationId)) $errors[] = 'Localização é obrigatória.';
        }

        if (empty($categoryId)) $errors[] = 'Categoria é obrigatória.';
        if (empty($description)) $errors[] = 'Descrição é obrigatória.';

        $formattedDate = $date . ' ' . $time;
        if (strtotime($formattedDate) === false || strtotime($formattedDate) <= time()) {
            $errors[] = 'A data e hora de início do evento devem ser futuras e válidas.';
        }

        $endDateTime = null;
        if (!empty($endTime)) {
            $endDateTime = $endDateInput . ' ' . $endTime;
            if (strtotime($endDateTime) === false) {
                $errors[] = 'A hora de término é inválida.';
            } elseif (strtotime($endDateTime) <= strtotime($formattedDate)) {
                 $errors[] = 'A hora de término deve ser posterior à hora de início.';
            }
        }

        if (!empty($errors)) {
            $errorMessages = implode('<br>', $errors);
            $locationModel = new Location();
            $locations = $locationModel->getLocationsWithAvailability($formattedDate, $endDateTime);
            $categoryModel = new Category();
            $categories = $categoryModel->getAllCategories();
            $categories = $categoryModel->getAllCategories();
            $assetModel = new Asset();
            
            require_once __DIR__ . '/../models/Config.php';
            $configModel = new Config();
            $globalConfigs = $configModel->getAll();
            // Pass start and end times to availability check
            $assets = $assetModel->getAllAssetsWithAvailability($formattedDate, $endDateTime);
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/request/form.php';
            return;
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/events/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
            
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imagePath = '/eventos/public/uploads/events/' . $newFileName;
                }
            }
        }

        $scheduleFilePath = null;
        if (isset($_FILES['schedule_file']) && $_FILES['schedule_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/schedules/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileTmpPath = $_FILES['schedule_file']['tmp_name'];
            $fileName = $_FILES['schedule_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = array('pdf', 'doc', 'docx', 'odt', 'jpg', 'jpeg', 'png');
            
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $scheduleFilePath = '/eventos/public/uploads/schedules/' . $newFileName;
                }
            }
        }

        $isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;
        $externalLink = trim($_POST['external_link'] ?? '');
        $linkTitle = trim($_POST['link_title'] ?? '');
        $publicEstimation = (int)($_POST['public_estimation'] ?? 0);

        // Access Control Fields
        $requiresRegistration = isset($_POST['requires_registration']) ? 1 : 0;
        $hasCertificate = isset($_POST['has_certificate']) ? 1 : 0;
        $maxParticipants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;

        $eventModel = new Event();
        
        // Auto-approve if user is admin
        $initialStatus = 'Pendente';
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $initialStatus = 'Aprovado';
        }

        // Check availability
        $locationModel = new Location();
        
        // Check for specific blocking event first
        $blockingEvent = $locationModel->getBlockingEvent($locationId, $formattedDate, $endDateTime);
        if ($blockingEvent) {
            if ($blockingEvent['type'] === 'bloqueio_administrativo') {
                 // Use the blocking event's name or description as the reason
                 $reason = !empty($blockingEvent['description']) ? $blockingEvent['description'] : $blockingEvent['name'];
                 header('Location: /eventos/request/my_requests?error=' . urlencode("Este local está reservado para: " . $reason));
                 exit;
            }
        }

        if (!$locationModel->isAvailable($locationId, $formattedDate, $endDateTime)) {
            header('Location: /eventos/request/my_requests?error=' . urlencode('O local selecionado já está ocupado neste horário.'));
            exit;
        }

        $eventId = $eventModel->createEvent($title, $description, $formattedDate, $endDateTime, $locationId, $categoryId, $_SESSION['user_id'], $initialStatus, 'evento_publico', $isPublic, $imagePath, $externalLink, $linkTitle, $publicEstimation, $scheduleFilePath, $customLocation, $requiresRegistration, $maxParticipants, $hasCertificate);
        
        $requestModel = new EventRequest();
        $requestId = $requestModel->createRequest($_SESSION['user_id'], $eventId, $initialStatus);

        // Handle Assets
        $selectedAssets = $_POST['assets'] ?? [];
        $failedAssets = [];
        
        if (!empty($selectedAssets)) {
            $loanModel = new Loan();
            // Return date defaults to end time or +1 hour if no end time
            $returnDate = $endDateTime ?: date('Y-m-d H:i:s', strtotime($formattedDate . ' +1 hour'));
            
            foreach ($selectedAssets as $assetId) {
                $qty = (int)($quantities[$assetId] ?? 1);
                if ($qty < 1) $qty = 1;
                
                try {
                    if (!$loanModel->requestLoan($assetId, $_SESSION['user_id'], $eventId, $formattedDate, $returnDate, $qty)) {
                        $failedAssets[] = $assetId;
                    }
                } catch (Exception $e) {
                    $failedAssets[] = $assetId;
                }
            }
        }

        // Capacity Check Logic
        $warningMsg = '';
        $locationModel = new Location();
        $location = $locationModel->getLocationById($locationId);
        
        if ($location) {
            $capacity = (int)$location['capacity'];
            // Check if estimation is LESS than 80% of capacity
            if ($capacity > 0 && $publicEstimation > 0 && $publicEstimation < ($capacity * 0.8)) {
                $warningMsg = ' ATENÇÃO: Poderá haver mudanças de local do seu evento em decorrência da estimativa de público ser inferior ao local solicitado.';
            }
        }

        if (!empty($failedAssets)) {
            // Fetch asset names for better error message
            $assetModel = new Asset();
            $failedNames = [];
            foreach ($failedAssets as $fid) {
                $a = $assetModel->getAssetById($fid);
                if ($a) $failedNames[] = $a['name'];
            }
            $msg = 'Solicitação criada, MAS alguns equipamentos não puderam ser reservados (estoque insuficiente ou erro): ' . implode(', ', $failedNames) . $warningMsg;
            header('Location: /eventos/request/my_requests?error=' . urlencode($msg));
        } else {
             // Send Emails
             require_once __DIR__ . '/../lib/Notification.php';
             global $pdo;
             $notification = new Notification($pdo);
             
             if ($initialStatus === 'Aprovado') {
                 // If auto-approved (admin), send approval email immediately
                 $notification->sendApproval($_SESSION['user_id'], $eventId, true);
             } else {
                 // If pending, send confirmation of receipt
                 $notification->sendConfirmation($_SESSION['user_id'], $eventId);
                 // And alert admins
                 $notification->sendAdminAlert($eventId);
             }
             
             $msg = 'Solicitação enviada com sucesso! Um e-mail de confirmação foi enviado para você.' . $warningMsg;
             header('Location: /eventos/request/my_requests?message=' . urlencode($msg));
        }
        exit;
    }

}