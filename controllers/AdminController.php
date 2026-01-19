<?php

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../lib/Security.php';

class AdminController {

    private function checkAdminAccess() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /eventos/');
            exit;
        }
    }

    public function dashboard() {
        $this->checkAdminAccess();
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    public function events() {
        $this->listPendingEvents();
    }

    public function listPendingEvents() {
        $this->checkAdminAccess();
        $eventModel = new Event();
        $events = $eventModel->getPendingEvents();
        
        require_once __DIR__ . '/../models/Loan.php';
        $loanModel = new Loan();
        
        foreach ($events as &$event) {
            $loans = $loanModel->getLoansByEvent($event['id']);
            $assetCounts = [];
            foreach ($loans as $loan) {
                $name = $loan['asset_name'];
                if (!isset($assetCounts[$name])) {
                    $assetCounts[$name] = 0;
                }
                $assetCounts[$name]++;
            }
            $assetsDisplay = [];
            foreach ($assetCounts as $name => $count) {
                $assetsDisplay[] = $name . " ({$count})";
            }
            $event['assets_display'] = !empty($assetsDisplay) ? implode(', ', $assetsDisplay) : 'Nenhum';
        }
        unset($event);

        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/events.php';
    }

    public function approve() {
        $this->approveEvent();
    }

    public function approveEvent() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/events?error=Invalid CSRF token');
                exit;
            }
            $eventId = $_POST['event_id'];
            $eventModel = new Event();
            $eventModel->updateStatus($eventId, 'Aprovado', $_SESSION['user_id']);

            // Send approval email
            require_once __DIR__ . '/../lib/Notification.php';
            global $pdo;
            $notification = new Notification($pdo);
            $stmt = $pdo->prepare("SELECT created_by FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($event) {
                $notification->sendApproval($event['created_by'], $eventId, true);
            }
        }
        header('Location: /eventos/admin/events');
        exit;
    }

    public function reject() {
        $this->rejectEvent();
    }

    public function rejectEvent() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/events?error=Token CSRF inválido');
                exit;
            }
            $eventId = $_POST['event_id'];
            $eventModel = new Event();
            $eventModel->updateStatus($eventId, 'Reprovado');

            // Send rejection email
            require_once __DIR__ . '/../lib/Notification.php';
            global $pdo;
            $notification = new Notification($pdo);
            $stmt = $pdo->prepare("SELECT created_by FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($event) {
                $notification->sendApproval($event['created_by'], $eventId, false);
            }
        }
        header('Location: /eventos/admin/events');
        exit;
    }

    public function users() {
        $this->listUsers();
    }

    public function listUsers() {
        $this->checkAdminAccess();
        $userModel = new User();
        $users = $userModel->getAllUsers();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/users.php';
    }

    public function updateRole() {
        $this->updateUserRole();
    }

    public function updateUserRole() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $role = $_POST['role'];
            $userModel = new User();
            $userModel->updateRole($userId, $role);
        }
        header('Location: /eventos/admin/users');
        exit;
    }

    public function updateUser() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            $userModel = new User();
            $userModel->updateUser($userId, $name, $email);
        }
        header('Location: /eventos/admin/users');
        exit;
    }

    public function getUserStats() {
        $this->checkAdminAccess();
        if (isset($_GET['user_id'])) {
            $userId = $_GET['user_id'];
            $userModel = new User();
            $stats = $userModel->getDependencyStats($userId);
            header('Content-Type: application/json');
            echo json_encode($stats);
            exit;
        }
    }

    public function deleteUser() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $userModel = new User();
            if ($userModel->deleteUser($userId)) {
                 header('Location: /eventos/admin/users?success=Usuário excluído com sucesso');
            } else {
                 header('Location: /eventos/admin/users?error=Erro ao excluir usuário. Verifique dependências.');
            }
            exit;
        }
        header('Location: /eventos/admin/users');
        exit;
    }

    public function locations() {
        $this->checkAdminAccess();
        $locationModel = new Location();
        $locations = $locationModel->getAllLocations();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/locations.php';
    }

    public function createLocation() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/locations?error=Invalid CSRF token');
                exit;
            }
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $locationModel = new Location();
            $locationModel->createLocation($name, $description, $capacity);
        }
        header('Location: /eventos/admin/locations');
        exit;
    }

    public function updateLocation() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/locations?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $locationModel = new Location();
            $locationModel->updateLocation($id, $name, $description, $capacity);
        }
        header('Location: /eventos/admin/locations');
        exit;
    }

    public function deleteLocation() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/locations?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $locationModel = new Location();
            $locationModel->deleteLocation($id);
        }
        header('Location: /eventos/admin/locations');
        exit;
    }

    public function categories() {
        $this->checkAdminAccess();
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategories();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/categories.php';
    }

    public function createCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/categories?error=Invalid CSRF token');
                exit;
            }
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryModel = new Category();
            $categoryModel->createCategory($name, $description);
        }
        header('Location: /eventos/admin/categories');
        exit;
    }

    public function updateCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/categories?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryModel = new Category();
            $categoryModel->updateCategory($id, $name, $description);
        }
        header('Location: /eventos/admin/categories');
        exit;
    }

    public function deleteCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/categories?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $categoryModel = new Category();
            $categoryModel->deleteCategory($id);
        }
        header('Location: /eventos/admin/categories');
        exit;
    }

    public function editEvent() {
        $this->checkAdminAccess();
        $id = (int)($_GET['id'] ?? 0);
        $eventModel = new Event();
        $event = $eventModel->getEventById($id);
        if (!$event) {
            header('Location: /eventos/admin/events');
            exit;
        }
        $locationModel = new Location();
        $locations = $locationModel->getAllLocations();
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategories();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/edit_event.php';
    }

    public function updateEvent() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/events?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['date'] ?? '';
            $time = $_POST['time'] ?? '';
            $locationId = (int)($_POST['location'] ?? 0);
            $categoryId = (int)($_POST['category'] ?? 0);
            $status = $_POST['status'] ?? 'Pendente';

            $eventModel = new Event();
            $eventModel->updateEvent($id, $name, $description, $date . ' ' . $time, $locationId, $categoryId, $status);
        }
        header('Location: /eventos/admin/events');
        exit;
    }

    public function deleteEvent() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $eventModel = new Event();
            $eventModel->deleteEvent($id);
        }
        header('Location: /eventos/');
        exit;
    }

}

?>