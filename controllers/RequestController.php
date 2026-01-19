<?php

require_once __DIR__ . '/../models/EventRequest.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
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
        $locationId = (int)($_POST['location'] ?? 0);
        $categoryId = (int)($_POST['category'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $assets = $_POST['assets'] ?? [];
        $quantities = $_POST['quantities'] ?? [];

        $errors = [];

        if (empty($title)) $errors[] = 'Título é obrigatório.';
        if (empty($date)) $errors[] = 'Data é obrigatória.';
        if (empty($time)) $errors[] = 'Hora de início é obrigatória.';
        if (empty($locationId)) $errors[] = 'Localização é obrigatória.';
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
            $assetModel = new Asset();
            // Pass start and end times to availability check
            $assets = $assetModel->getAllAssetsWithAvailability($formattedDate, $endDateTime);
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/request/form.php';
            return;
        }

        $isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;

        $eventModel = new Event();
        $eventId = $eventModel->createEvent($title, $description, $formattedDate, $endDateTime, $locationId, $categoryId, $_SESSION['user_id'], $isPublic);
        
        $requestModel = new EventRequest();
        $requestId = $requestModel->createRequest($_SESSION['user_id'], $eventId);

        // Handle Assets
        $selectedAssets = $_POST['assets'] ?? [];
        if (!empty($selectedAssets)) {
            $loanModel = new Loan();
            // Return date defaults to end time or +1 hour if no end time
            $returnDate = $endDateTime ?: date('Y-m-d H:i:s', strtotime($formattedDate . ' +1 hour'));
            
            foreach ($selectedAssets as $assetId) {
                $qty = (int)($quantities[$assetId] ?? 1);
                if ($qty < 1) $qty = 1;
                
                if (!$loanModel->requestLoan($assetId, $_SESSION['user_id'], $eventId, $formattedDate, $returnDate, $qty)) {
                    // Optionally log error or add to a list of failed loans
                }
            }
        }

        header('Location: /eventos/request/my_requests?message=Solicitação enviada com sucesso');
        exit;
    }

}