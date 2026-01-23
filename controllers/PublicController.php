<?php

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/AssetItem.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../lib/Security.php';

class PublicController {

    public function index() {
        $eventModel = new Event();

        $events = [];
        $events = [];
        $activeEvents = $eventModel->getActiveEvents();
        $events = $eventModel->getAllApprovedEvents();

        include __DIR__ . '/../views/public/index.php';
    }

    public function calendar() {
        $eventModel = new Event();

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        // Ensure month and year are integers
        $month = (int)$month;
        $year = (int)$year;

        // Validate month
        if ($month < 1 || $month > 12) {
            $month = (int)date('m');
            $year = (int)date('Y');
        }

        // Calculate start and end dates for the month
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $events = $eventModel->getEventsByDateRange($startDate, $endDate);

        include __DIR__ . '/../views/public/calendar.php';
    }

    public function day() {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        // Basic validation for date format YYYY-MM-DD
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $date = date('Y-m-d');
        }

        $eventModel = new Event();
        $events = $eventModel->getApprovedEventsByDate($date);

        include __DIR__ . '/../views/public/day_timeline.php';
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $eventModel = new Event();
        $event = $eventModel->getEventById($id);

        if (!$event) {
            echo "Event not found.";
            return;
        }

        $loanModel = new Loan();
        $loans = $loanModel->getLoansByEvent($id);

        $csrf_token = Security::generateCsrfToken();

        include __DIR__ . '/../views/public/detail.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            $returnTo = urlencode($_SERVER['REQUEST_URI']);
            header('Location: /eventos/auth/login?return_to=' . $returnTo);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $errorMessages = 'Invalid CSRF token';
                $csrf_token = Security::generateCsrfToken();
                
                // Fetch necessary data for re-render
                $locationModel = new Location();
                $categoryModel = new Category();
                $assetModel = new Asset();
                
                // Use current time as default or try to rescue from POST if possible (logic simplified)
                $startDateTime = date('Y-m-d H:i');
                $endDateTime = date('Y-m-d H:i', strtotime('+1 hour'));

                $locations = $locationModel->getLocationsWithAvailability($startDateTime, $endDateTime);
                $categories = $categoryModel->getAllCategories();
                $assets = $assetModel->getAllAssetsWithAvailability($startDateTime, $endDateTime);

                include __DIR__ . '/../views/public/create.php';
                return;
            }

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['date'] ?? '';
            $time = $_POST['time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $endDateInput = !empty($_POST['end_date']) ? $_POST['end_date'] : $date;
            $locationId = (int)($_POST['location'] ?? 0);
            $categoryId = (int)($_POST['category'] ?? 0);

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Título é obrigatório.';
            }

            if (empty($description)) {
                $errors[] = 'Descrição é obrigatória.';
            }

            if (empty($date) || !strtotime($date)) {
                $errors[] = 'Data válida é obrigatória.';
            }

            if (empty($time) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
                $errors[] = 'Hora válida é obrigatória (HH:MM).';
            }

            if (empty($locationId)) {
                $errors[] = 'Localização é obrigatória.';
            }

            if (empty($categoryId)) {
                $errors[] = 'Categoria é obrigatória.';
            }

            $startDateTime = $date . ' ' . $time;
            $endDateTime = $endTime ? ($endDateInput . ' ' . $endTime) : null;

            // Validate Start Time in the future
            if (strtotime($startDateTime) && strtotime($startDateTime) <= time()) {
                $errors[] = 'O evento deve ser no futuro.';
            }

            // New: Validate End Time > Start Time
            if ($endDateTime && strtotime($endDateTime) <= strtotime($startDateTime)) {
                $errors[] = 'A hora de término deve ser posterior à hora de início.';
            }

            if (!empty($errors)) {
                $errorMessages = implode('<br>', $errors);
                // Re-fetch data for the form if there are errors
                $locationModel = new Location();
                // Check availability for re-render
                $locations = $locationModel->getLocationsWithAvailability($startDateTime, $endDateTime);
                $categoryModel = new Category();
                $categories = $categoryModel->getAllCategories();
                $assetModel = new Asset();
                // Pass the proposed start and end times for availability check
                $assets = $assetModel->getAllAssetsWithAvailability($startDateTime, $endDateTime);
                
                $csrf_token = Security::generateCsrfToken();
                
                include __DIR__ . '/../views/public/create.php';
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
                $fileSize = $_FILES['image']['size'];
                $fileType = $_FILES['image']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadDir . $newFileName;
                    
                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $imagePath = '/eventos/public/uploads/events/' . $newFileName;
                    } else {
                         // Non-blocking error for now, just skip image
                    }
                }
            }

            $isPublic = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;

            $eventModel = new Event();
            // Modified: Pass endDateTime and imagePath to createEvent
            $eventId = $eventModel->createEvent($name, $description, $startDateTime, $endDateTime, $locationId, $categoryId, $_SESSION['user_id'], $isPublic, $imagePath);

            // Handle asset loans
            $selectedAssets = $_POST['assets'] ?? [];
            if (!empty($selectedAssets)) {
                $loanModel = new Loan();
                // Default return date to event end time, or start + 1 hour if no end time
                $returnDate = $endDateTime ?: date('Y-m-d H:i:s', strtotime($startDateTime . ' +1 hour'));
                
                foreach ($selectedAssets as $assetId) {
                    $qty = (int)($quantities[$assetId] ?? 1);
                    if ($qty < 1) $qty = 1;

                    // Pass quantity to requestLoan
                    if (!$loanModel->requestLoan($assetId, $_SESSION['user_id'], $eventId, $startDateTime, $returnDate, $qty)) {
                         // Handle partial failure? ideally transaction for whole event...
                         // For now, consistent with existing logic.
                    }
                }
            }

            header('Location: /eventos/?message=Evento criado com sucesso');
            exit;
        } else {
            $csrf_token = Security::generateCsrfToken();
            // Pre-fill from GET
            if (isset($_GET['date'])) {
                $datetime = strtotime($_GET['date']);
                if ($datetime) {
                    $_POST['date'] = date('Y-m-d', $datetime);
                    $_POST['time'] = date('H:i', $datetime);
                }
            }
            $locationModel = new Location();
            
            // Define range for initial check. If times not set, it returns available.
            // But if users pre-fills time via GET?
            $checkDate = $_POST['date'] ?? date('Y-m-d');
            $checkEndDate = $_POST['end_date'] ?? $checkDate;
            
            $startDateTime = $checkDate . ' ' . ($_POST['time'] ?? '00:00');
            $endDateTime = $checkEndDate . ' ' . ($_POST['end_time'] ?? '23:59');
            
            $locations = $locationModel->getLocationsWithAvailability($startDateTime, $endDateTime);
            
            $categoryModel = new Category();
            $categories = $categoryModel->getAllCategories();
            $assetModel = new Asset();
            // Modified: Pass start and end times for availability check
            $assets = $assetModel->getAllAssetsWithAvailability($startDateTime, $endDateTime);
            include __DIR__ . '/../views/public/create.php';
        }
    }

    public function locations() {
        $locationModel = new Location();
        $locations = $locationModel->getAllLocations();
        include __DIR__ . '/../views/public/locations.php';
    }
}

?>