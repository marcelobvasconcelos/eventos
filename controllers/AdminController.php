<?php

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/AssetCategory.php';
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
        
        $eventModel = new Event();
        $futureEventsCount = $eventModel->getFutureEventsCount();
        
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $userCount = $userModel->getUserCount();
        
        require_once __DIR__ . '/../models/Location.php';
        $locationModel = new Location();
        $locationCount = $locationModel->getLocationCount();
        
        require_once __DIR__ . '/../models/Category.php';
        $categoryModel = new Category();
        $categoryCount = $categoryModel->getCategoryCount();
        
        require_once __DIR__ . '/../models/Asset.php';
        $assetModel = new Asset();
        $assetCount = $assetModel->getAssetCount();

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
            $eventModel->updateStatus($eventId, 'Reprovado', $_SESSION['user_id']);

            // Release assets upon rejection
            require_once __DIR__ . '/../models/Loan.php';
            $loanModel = new Loan();
            $loanModel->cancelLoansForEvent($eventId);

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

    public function changePassword() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $password = $_POST['password'] ?? '';
            
            if (!empty($password)) {
                $userModel = new User();
                $userModel->updatePassword($userId, $password);
                header('Location: /eventos/admin/users?success=Senha atualizada com sucesso');
                exit;
            }
        }
        header('Location: /eventos/admin/users?error=Erro ao atualizar senha');
        exit;
    }

    public function approveUser() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $userModel = new User();
            $userModel->updateStatus($userId, 'Ativo');
        }
        header('Location: /eventos/admin/users?success=Usuário aprovado');
        exit;
    }

    public function rejectUser() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/users?error=Invalid CSRF token');
                exit;
            }
            $userId = $_POST['user_id'];
            $userModel = new User();
            // Or 'Inativo'? Or delete? Assuming Inativo for now or just delete. 
            // Better to delete if it's a new registration? 
            // Let's set to Inativo so they know they were rejected? Or Delete. 
            // Plan says approve/reject. Let's delete for "Reject" or set Inativo.
            // Let's set Inativo.
            $userModel->updateStatus($userId, 'Inativo');
        }
        header('Location: /eventos/admin/users?success=Usuário rejeitado/inativado');
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
        include __DIR__ . '/../views/location/index.php';
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
        
        // Capture return URL
        $returnUrl = $_GET['return_url'] ?? '/eventos/admin/events';

        $locationModel = new Location();
        // Use event dates to check availability
        $checkDate = $event['date'];
        $checkEndDate = $event['end_date'] ?? date('Y-m-d H:i:s', strtotime($checkDate . ' +1 hour'));
        $locations = $locationModel->getLocationsWithAvailability($checkDate, $checkEndDate, $id);
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategories();
        
        // Load Assets and Loans
        require_once __DIR__ . '/../models/Loan.php';
        require_once __DIR__ . '/../models/Asset.php';
        $loanModel = new Loan();
        $assetModel = new Asset();
        
        // Get existing loans, grouped by asset
        $rawLoans = $loanModel->getLoansByEvent($id);
        $currentAssets = [];
        foreach ($rawLoans as $loan) {
            $aId = $loan['asset_id'];
            if (!isset($currentAssets[$aId])) {
                $currentAssets[$aId] = 0;
            }
            $currentAssets[$aId]++;
        }
        
        // Pass $id (event ID) as 3rd argument to exclude current event's loans from availability check
        // This effectively returns "Total Capacity Available for this Event"
        $allAssets = $assetModel->getAllAssetsWithAvailability($event['date'], $event['end_date'] ?? $event['date'], $id);
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
            $returnUrl = $_POST['return_url'] ?? '/eventos/admin/events';
            
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['date'] ?? '';
            $time = $_POST['time'] ?? '';
            $endDateInput = $_POST['end_date'] ?? '';
            $endTimeInput = $_POST['end_time'] ?? '';
            
            $formattedDate = $date . ' ' . $time;
            $formattedEndDate = null;
            if (!empty($endDateInput) && !empty($endTimeInput)) {
                $formattedEndDate = $endDateInput . ' ' . $endTimeInput;
            }

            $locationId = (int)($_POST['location'] ?? 0);
            $categoryId = (int)($_POST['category'] ?? 0);
            $status = $_POST['status'] ?? 'Pendente';
            $isPublic = (int)($_POST['is_public'] ?? 1);
        $externalLink = trim($_POST['external_link'] ?? '');
        $linkTitle = trim($_POST['link_title'] ?? '');

        // 1. Validate Location Availability (excluding current event)
            require_once __DIR__ . '/../models/Location.php';
            $locationModel = new Location();
            $checkEnd = $formattedEndDate ?: date('Y-m-d H:i:s', strtotime($formattedDate . ' +1 hour'));
            
            if (!$locationModel->isAvailable($locationId, $formattedDate, $checkEnd, $id)) {
                 header('Location: /eventos/admin/editEvent?id=' . $id . '&return_url=' . urlencode($returnUrl) . '&error=' . urlencode('O local selecionado já está ocupado neste horário.'));
                 exit;
            }

            // 2. Validate Asset Availability
            require_once __DIR__ . '/../models/Asset.php';
            $assetModel = new Asset();
            $submittedAssets = $_POST['assets'] ?? [];
            
            // Get what's available globally (excluding THIS event's loans)
            $availableAssetsMap = [];
            $allAssetsAvail = $assetModel->getAllAssetsWithAvailability($formattedDate, $checkEnd, $id);
            foreach ($allAssetsAvail as $a) {
                $availableAssetsMap[$a['id']] = $a;
            }

            foreach ($submittedAssets as $assetId => $qty) {
                 $qty = (int)$qty;
                 $availItem = $availableAssetsMap[$assetId] ?? null;
                 if (!$availItem) {
                      // Asset doesn't exist or logic error
                      continue;
                 }
                 if ($qty > $availItem['available_count']) {
                      header('Location: /eventos/admin/editEvent?id=' . $id . '&return_url=' . urlencode($returnUrl) . '&error=' . urlencode('O equipamento ' . $availItem['name'] . ' não possui quantidade suficiente (' . $availItem['available_count'] . ' disponíveis).'));
                      exit;
                 }
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

            $eventModel = new Event();
        $eventModel->updateEvent($id, $name, $description, $formattedDate, $formattedEndDate, $locationId, $categoryId, $status, $isPublic, $imagePath, $externalLink, $linkTitle);
            
            // --- Asset Update Logic ---
            require_once __DIR__ . '/../models/Loan.php';
            $loanModel = new Loan();

            // Sync dates of existing loans
            $loanModel->updateEventLoans($id, $formattedDate, $formattedEndDate);

            // 1. Fetch current active loans for this event
            $currentLoans = $loanModel->getLoansByEvent($id);
            // Group active loans by Asset ID -> Stack of Loan IDs
            $activeLoanStacks = [];
            foreach ($currentLoans as $loan) {
                if ($loan['status'] === 'Emprestado' || $loan['status'] === 'Aguardando') {
                   $activeLoanStacks[$loan['asset_id']][] = $loan['id'];
                }
            }

            // 2. Process each submitted asset
            foreach ($submittedAssets as $assetId => $qty) {
                $qty = (int)$qty;
                $currentStack = $activeLoanStacks[$assetId] ?? [];
                $currentCount = count($currentStack);

                if ($qty > $currentCount) {
                    // Need more items
                    $diff = $qty - $currentCount;
                    $eventInfo = $eventModel->getEventById($id);
                    $borrowerId = $eventInfo['created_by']; 
                    
                    // Use updated time
                    $loanDate = $formattedDate;
                    $returnDate = $formattedEndDate ?: date('Y-m-d H:i:s', strtotime($loanDate . ' +1 hour'));
                    
                    $loanModel->requestLoan($assetId, $borrowerId, $id, $loanDate, $returnDate, $diff);
                } elseif ($qty < $currentCount) {
                    // Need to return items (reduce quantity)
                    $diff = $currentCount - $qty;
                    // Remove $diff items from stack
                    for ($i = 0; $i < $diff; $i++) {
                        $loanIdToRemove = array_pop($currentStack);
                        if ($loanIdToRemove) {
                            $loanModel->returnLoan($loanIdToRemove);
                        }
                    }
                }
            }
            // --------------------------
            
            header('Location: ' . $returnUrl);
            exit;
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
            
            // Release assets before deletion
            require_once __DIR__ . '/../models/Loan.php';
            $loanModel = new Loan();
            $loanModel->cancelLoansForEvent($id);

            $eventModel = new Event();
            $eventModel->deleteEvent($id);
        }
        header('Location: /eventos/public/calendar');
        exit;
    }

    public function cancelEvent() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $eventModel = new Event();
            $eventModel->updateStatus($id, 'Cancelado');
            
            // Release assets
            require_once __DIR__ . '/../models/Loan.php';
            $loanModel = new Loan();
            $loanModel->cancelLoansForEvent($id);

            header("Location: /eventos/public/detail?id=$id&status=cancelled");
            exit;
        }
        header('Location: /eventos/public/calendar');
        exit;
    }

    // --- Asset Management ---

    public function assetCategories() {
        $this->checkAdminAccess();
        $assetCategoryModel = new AssetCategory();
        $categories = $assetCategoryModel->getAll();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/asset_categories.php';
    }

    public function createAssetCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/assetCategories?error=Invalid CSRF token');
                exit;
            }
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $assetCategoryModel = new AssetCategory();
            if ($assetCategoryModel->create($name, $description)) {
                header('Location: /eventos/admin/assetCategories?success=Categoria criada');
            } else {
                header('Location: /eventos/admin/assetCategories?error=Erro ao criar categoria');
            }
            exit;
        }
        header('Location: /eventos/admin/assetCategories');
        exit;
    }

    public function updateAssetCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/assetCategories?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $assetCategoryModel = new AssetCategory();
            if ($assetCategoryModel->update($id, $name, $description)) {
                header('Location: /eventos/admin/assetCategories?success=Categoria atualizada');
            } else {
                header('Location: /eventos/admin/assetCategories?error=Erro ao atualizar categoria');
            }
            exit;
        }
        header('Location: /eventos/admin/assetCategories');
        exit;
    }

    public function deleteAssetCategory() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/assetCategories?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $assetCategoryModel = new AssetCategory();
            if ($assetCategoryModel->delete($id)) {
                 header('Location: /eventos/admin/assetCategories?success=Categoria excluída');
            } else {
                 header('Location: /eventos/admin/assetCategories?error=Erro ao excluir categoria');
            }
            exit;
        }
        header('Location: /eventos/admin/assetCategories');
        exit;
    }

    public function assets() {
        $this->checkAdminAccess();
        require_once __DIR__ . '/../models/Asset.php';
        // Need Loan model for view? view performs 'new Loan()' (line 77 of view).
        // But good to ensure it's loaded if not autoloaded.
        require_once __DIR__ . '/../models/Loan.php'; 
        $assetModel = new Asset();
        // Use getAllAssets (like AssetController) or getAllAssetsWithAvailability? 
        // View uses available_quantity column.
        // getAllAssetsWithAvailability also returns 'available_count'.
        // Let's use getAllAssetsWithAvailability (default) which mimics 'available_quantity' if no date passed.
        $assets = $assetModel->getAllAssetsWithAvailability(); 
        
        $assetCategoryModel = new AssetCategory();
        $categories = $assetCategoryModel->getAll();

        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/asset/index.php';
    }

    public function createAssetAction() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                 header('Location: /eventos/admin/assets?error=Invalid CSRF token');
                 exit;
             }
             $name = trim($_POST['name'] ?? '');
             $description = trim($_POST['description'] ?? '');
             $quantity = (int)($_POST['quantity'] ?? 0);
             $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
             $requires_patrimony = isset($_POST['requires_patrimony']) ? 1 : 0;
             
             require_once __DIR__ . '/../models/Asset.php';
             $assetModel = new Asset();
             if ($assetModel->addAsset($name, $description, $quantity, $category_id, $requires_patrimony)) {
                 header('Location: /eventos/admin/assets?success=Equipamento criado');
             } else {
                 header('Location: /eventos/admin/assets?error=Erro ao criar');
             }
             exit;
        }
    }

    public function editAsset() {
        $this->checkAdminAccess();
        $id = (int)($_GET['id'] ?? 0);
        require_once __DIR__ . '/../models/Asset.php';
        $assetModel = new Asset();
        $asset = $assetModel->getAssetById($id);
        if (!$asset) {
            header('Location: /eventos/admin/assets');
            exit;
        }
        $assetCategoryModel = new AssetCategory();
        $categories = $assetCategoryModel->getAll();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/admin/edit_asset.php';
    }

    public function updateAsset() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                 header('Location: /eventos/admin/assets?error=Invalid CSRF token');
                 exit;
             }
             $id = (int)($_POST['id'] ?? 0);
             $name = trim($_POST['name'] ?? '');
             $description = trim($_POST['description'] ?? '');
             $quantity = (int)($_POST['quantity'] ?? 0);
             $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
             $requires_patrimony = isset($_POST['requires_patrimony']) ? 1 : 0;
             
             require_once __DIR__ . '/../models/Asset.php';
             $assetModel = new Asset();
             // Validation could go here
             if ($assetModel->updateAsset($id, $name, $description, $quantity, $category_id, $requires_patrimony)) {
                 header('Location: /eventos/admin/assets?success=Equipamento atualizado');
             } else {
                 header('Location: /eventos/admin/assets?error=Erro ao atualizar');
             }
             exit;
        }
        header('Location: /eventos/admin/assets');
        exit;
    }

    public function deleteAsset() {
        $this->checkAdminAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/admin/assets?error=Invalid CSRF token');
                exit;
            }
            $id = (int)($_POST['id'] ?? 0);
            $confirmed = isset($_POST['confirm_delete']);

            require_once __DIR__ . '/../models/Asset.php';
            $assetModel = new Asset();
            
            if (!$confirmed) {
                // Check for future reservations
                $futureReservations = $assetModel->getFutureReservations($id);
                if (!empty($futureReservations)) {
                    // Show confirmation warning page
                    $asset = $assetModel->getAssetById($id);
                    $csrf_token = $_POST['csrf_token']; // Reuse token
                    include __DIR__ . '/../views/admin/delete_asset_confirm.php';
                    exit;
                }
            }

            // Delete (force if confirmed or no reservations)
            if ($assetModel->deleteAsset($id)) {
                header('Location: /eventos/admin/assets?success=Equipamento excluído');
            } else {
                header('Location: /eventos/admin/assets?error=Erro ao excluir');
            }
            exit;
        }
        header('Location: /eventos/admin/assets');
        exit;
    }

    public function printEvent() {
        $this->checkAdminAccess();
        $id = (int)($_GET['id'] ?? 0);
        
        $eventModel = new Event();
        $event = $eventModel->getEventById($id);
        
        if (!$event) {
            die('Evento não encontrado.');
        }

        require_once __DIR__ . '/../models/Loan.php';
        require_once __DIR__ . '/../models/PendingItem.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Asset.php';
        
        $loanModel = new Loan();
        $pendingItemModel = new PendingItem();
        $userModel = new User();
        $assetModel = new Asset();
        
        $loans = $loanModel->getLoansByEvent($id);
        
        // Fetch global pending items to filter in-memory (simplest for now)
        $allPending = $pendingItemModel->getAllItems(); 
        $eventPendingItems = array_filter($allPending, function($item) use ($id) {
            return $item['event_id'] == $id;
        });

        // Determine context
        $isFinished = false;
        if (!empty($event['end_date'])) {
            $isFinished = strtotime($event['end_date']) < time();
        } else {
            // If only start date, assume finished if start date was yesterday
            $isFinished = strtotime($event['date']) < strtotime('-1 day');
        }
        
        include __DIR__ . '/../views/admin/print_event.php';
    }

}

?>