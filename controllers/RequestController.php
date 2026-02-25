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
            header('Location: /eventos/auth/login');
            exit;
        }

        $locationModel = new Location();
        $assetModel = new Asset();
        
        require_once __DIR__ . '/../models/Config.php';
        $configModel = new Config();
        $globalConfigs = $configModel->getAll();
        
        // Single day logic: date, start_time, end_time
        $checkDate = $_POST['date'] ?? ($_GET['date'] ?? date('Y-m-d'));
        $startTime = $_POST['start_time'] ?? ($_GET['start_time'] ?? '08:00');
        $endTime = $_POST['end_time'] ?? ($_GET['end_time'] ?? '10:00');
        
        $startDateTime = $checkDate . ' ' . $startTime;
        $endDateTime = $checkDate . ' ' . $endTime;

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
            header('Location: /eventos/request/form?error=Invalid CSRF token');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $date = $_POST['date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $categoryId = (int)($_POST['category'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        $errors = []; // Initialize errors array here

        $locationPost = $_POST['location'] ?? '';
        $locationId = null;
        $customLocation = null;

        if ($locationPost === 'other') {
            $customLocation = trim($_POST['custom_location'] ?? '');
            if (empty($customLocation)) $errors[] = 'O local customizado é obrigatório quando "Outros" é selecionado.';
        } elseif (!empty($locationPost)) {
            $locationId = (int)$locationPost;
        } else {
            $errors[] = 'O local do evento é obrigatório.';
        }

        if (empty($title)) $errors[] = 'Título é obrigatório.';
        if (empty($date)) $errors[] = 'Data é obrigatória.';
        if (empty($startTime)) $errors[] = 'Hora de início é obrigatória.';
        if (empty($endTime)) $errors[] = 'Hora de término é obrigatória.';
        if (empty($categoryId)) $errors[] = 'Categoria é obrigatória.';
        if (empty($description)) $errors[] = 'Descrição é obrigatória.';

        $startDateTime = $date . ' ' . $startTime;
        $endDateTime = $date . ' ' . $endTime;

        if (strtotime($startDateTime) === false || strtotime($startDateTime) <= time()) {
            $errors[] = 'A data e hora do evento devem ser futuras e válidas.';
        }

        if (strtotime($endDateTime) <= strtotime($startDateTime)) {
            $errors[] = 'A hora de término deve ser posterior à hora de início.';
        }

        if (!empty($errors)) {
            $errorMsg = implode('<br>', $errors);
            header('Location: /eventos/request/form?error=' . urlencode($errorMsg));
            exit;
        }

        // Image handling
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/events/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = md5(time() . $_FILES['image']['name']) . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $imagePath = '/eventos/public/uploads/events/' . $fileName;
            }
        }

        // Schedule handling
        $scheduleFilePath = null;
        if (isset($_FILES['schedule_file']) && $_FILES['schedule_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/schedules/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = md5(time() . $_FILES['schedule_file']['name']) . '.' . strtolower(pathinfo($_FILES['schedule_file']['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES['schedule_file']['tmp_name'], $uploadDir . $fileName)) {
                $scheduleFilePath = '/eventos/public/uploads/schedules/' . $fileName;
            }
        }

        $isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;
        $externalLink = trim($_POST['external_link'] ?? '');
        $linkTitle = trim($_POST['link_title'] ?? '');
        $publicEstimation = (int)($_POST['public_estimation'] ?? 0);
        $requiresRegistration = isset($_POST['requires_registration']) ? 1 : 0;
        $hasCertificate = isset($_POST['has_certificate']) ? 1 : 0;
        $maxParticipants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;

        $eventModel = new Event();
        $locationModel = new Location();

        // Check availability
        if ($locationId && !$locationModel->isAvailable($locationId, $startDateTime, $endDateTime)) {
            header('Location: /eventos/request/my_requests?error=' . urlencode('O local selecionado já está ocupado neste horário.'));
            exit;
        }

        $initialStatus = ($_SESSION['user_role'] === 'admin') ? 'Aprovado' : 'Pendente';

        try {
            $eventId = $eventModel->createEvent($title, $description, $date, $startTime, $endTime, $locationId, $categoryId, $_SESSION['user_id'], $initialStatus, 'evento_publico', $isPublic, $imagePath, $externalLink, $linkTitle, $publicEstimation, $scheduleFilePath, $customLocation, $requiresRegistration, $maxParticipants, $hasCertificate);
            
            // Handle Assets
            $selectedAssets = $_POST['assets'] ?? [];
            $quantities = $_POST['quantities'] ?? [];
            if (!empty($selectedAssets)) {
                require_once __DIR__ . '/../models/Loan.php';
                $loanModel = new Loan();
                foreach ($selectedAssets as $assetId) {
                    $qty = (int)($quantities[$assetId] ?? 1);
                    $loanModel->requestLoan($assetId, $_SESSION['user_id'], $eventId, $startDateTime, $endDateTime, $qty);
                }
            }

            header('Location: /eventos/request/my_requests?message=' . urlencode('Solicitação enviada com sucesso!'));
            exit;
        } catch (Exception $e) {
            header('Location: /eventos/request/form?error=' . urlencode('Erro ao salvar evento: ' . $e->getMessage()));
            exit;
        }
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $eventModel = new Event();
        $event = $eventModel->getEventById($id);

        if (!$event || ($event['created_by'] != $_SESSION['user_id'] && !in_array($_SESSION['user_role'], ['admin', 'gestor']))) {
            header('Location: /eventos/request/my_requests?error=Acesso negado.');
            exit;
        }

        // Rule: Can only edit BEFORE the event date
        if ($event['date'] < date('Y-m-d')) {
            header('Location: /eventos/request/my_requests?error=Não é possível editar eventos que já ocorreram.');
            exit;
        }

        $locationModel = new Location();
        $categoryModel = new Category();
        $assetModel = new Asset();
        
        $locations = $locationModel->getAllLocations();
        $categories = $categoryModel->getAllCategories();
        $assets = $assetModel->getAllAssets();
        
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/request/edit.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            header('Location: /eventos/request/my_requests?error=Invalid CSRF token');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $eventModel = new Event();
        $event = $eventModel->getEventById($id);

        if (!$event || ($event['created_by'] != $_SESSION['user_id'] && !in_array($_SESSION['user_role'], ['admin', 'gestor']))) {
            header('Location: /eventos/request/my_requests?error=Acesso negado.');
            exit;
        }

        // Basic validation
        $data = [
            'name' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'date' => $_POST['date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'category_id' => (int)($_POST['category'] ?? 0),
            'is_public' => isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1,
            'external_link' => trim($_POST['external_link'] ?? ''),
            'link_title' => trim($_POST['link_title'] ?? ''),
            'custom_location' => $event['custom_location'] // Use existing, ignore proposed
        ];

        // Image handling for edit
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/events/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = md5(time() . $_FILES['image']['name']) . '.' . strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                $data['image_path'] = '/eventos/public/uploads/events/' . $fileName;
            }
        }

        // Schedule file handling for edit
        if (isset($_FILES['schedule_file']) && $_FILES['schedule_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/schedules/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = md5(time() . $_FILES['schedule_file']['name']) . '.' . strtolower(pathinfo($_FILES['schedule_file']['name'], PATHINFO_EXTENSION));
            if (move_uploaded_file($_FILES['schedule_file']['tmp_name'], $uploadDir . $fileName)) {
                $data['schedule_file_path'] = '/eventos/public/uploads/schedules/' . $fileName;
            }
        }

        // Workflow: If approved, save as proposal. If pending, update directly.
        if ($event['status'] === 'Aprovado') {
            require_once __DIR__ . '/../models/EventEdit.php';
            $editModel = new EventEdit();
            
            $proposalData = [
                'event_id' => $id,
                'user_id' => $_SESSION['user_id']
            ];
            
            // Define fields to compare
            $fieldsToCompare = [
                'name' => 'name',
                'description' => 'description',
                'date' => 'date',
                'start_time' => 'start_time',
                'end_time' => 'end_time',
                'category_id' => 'category_id',
                'is_public' => 'is_public',
                'external_link' => 'external_link',
                'link_title' => 'link_title'
            ];
            
            foreach ($fieldsToCompare as $dataKey => $dbKey) {
                $val = $data[$dataKey];
                $orig = $event[$dbKey];
                
                $isSame = false;
                if ($dataKey === 'date') {
                    $isSame = date('Y-m-d', strtotime($val)) === date('Y-m-d', strtotime($orig));
                } elseif ($dataKey === 'start_time' || $dataKey === 'end_time') {
                    $isSame = date('H:i', strtotime($val)) === date('H:i', strtotime($orig));
                } else {
                    $isSame = trim((string)$val) === trim((string)$orig);
                }
                
                if (!$isSame) {
                    $proposalData[$dataKey] = $val;
                }
            }
            
            // Always include files if they were uploaded
            if (isset($data['image_path'])) $proposalData['image_path'] = $data['image_path'];
            if (isset($data['schedule_file_path'])) $proposalData['schedule_file_path'] = $data['schedule_file_path'];

            // Only create proposal if something actually changed (beyond event_id and user_id)
            if (count($proposalData) > 2) {
                $editModel->createProposal($proposalData);
                header('Location: /eventos/request/my_requests?message=' . urlencode('Sua proposta de alteração foi enviada para análise do administrador.'));
            } else {
                header('Location: /eventos/request/my_requests?message=' . urlencode('Nenhuma alteração detectada nas informações do evento.'));
            }
        } else {
            // Update directly
            $eventModel->updateEvent($id, $data['name'], $data['description'], $data['date'], $data['start_time'], $data['end_time'], $event['location_id'], $data['category_id'], $event['status'], $data['is_public'], $data['image_path'] ?? null, $data['external_link'], $data['link_title'], 0, $data['schedule_file_path'] ?? null, $data['custom_location']);
            header('Location: /eventos/request/my_requests?message=' . urlencode('Evento atualizado com sucesso!'));
        }
        exit;
    }

}