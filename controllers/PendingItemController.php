<?php

require_once __DIR__ . '/../models/PendingItem.php';
require_once __DIR__ . '/../lib/Security.php';
require_once __DIR__ . '/../lib/PendingManager.php';

class PendingItemController {

    private $pendingModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->pendingModel = new PendingItem();
        
        // Lazy Trigger Check on every controller load (or specific pages)
        // Ideally should be in a middleware or base controller.
        // For simplicity, we can trigger it when Admin or User accesses pending pages.
        $pm = new PendingManager();
        $pm->checkAndGenerate();
    }

    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
            header('Location: /eventos/auth/login');
            exit;
        }

        $pendingItems = $this->pendingModel->getAllItems();
        include __DIR__ . '/../views/admin/pending_items.php';
    }

    public function myPending() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        // Use getAllItemsByUser to include history (completed items)
        $pendingItems = $this->pendingModel->getAllItemsByUser($userId);
        include __DIR__ . '/../views/user/pending_items.php';
    }

    public function markReturned() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = [];
            if (isset($_POST['ids']) && is_array($_POST['ids'])) {
                $ids = $_POST['ids'];
            } elseif (isset($_POST['id'])) {
                $ids = [$_POST['id']];
            }
            
            $userNote = $_POST['user_note'] ?? null;
            $count = 0;

            foreach ($ids as $id) {
                // Verify ownership for each item
                $item = $this->pendingModel->find($id);
                if ($item && $item['user_id'] == $_SESSION['user_id']) {
                    $this->pendingModel->updateStatus($id, 'user_informed', null, $userNote);
                    $count++;
                }
            }
            
            if ($count > 0) {
                header('Location: /eventos/pending/myPending?message=Devolução informada com sucesso para ' . $count . ' item(ns)');
            } else {
                echo "Unauthorized or No items selected";
            }
        }
    }
    
    public function updateStatus() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
             header('Location: /eventos/auth/login');
             exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = [];
            if (isset($_POST['ids']) && is_array($_POST['ids'])) {
                // Handling bulk connection from JSON or array input
                // If it came from a form submission with name="ids[]"
                $ids = $_POST['ids']; 
            } elseif (isset($_POST['ids']) && is_string($_POST['ids'])) {
                // Handling comma separated string if passed that way
                 $ids = explode(',', $_POST['ids']);
            } elseif (isset($_POST['id'])) {
                // Single item fallback
                $ids = [$_POST['id']];
            }

            $status = $_POST['status']; // 'completed', 'contested'
            $observation = $_POST['observation'] ?? null;
            
            $count = 0;
            foreach ($ids as $id) {
                if(!empty($id)) {
                    $this->pendingModel->updateStatus($id, $status, $observation);
                    $count++;
                }
            }
            
            header('Location: /eventos/pending?message=Status atualizado com sucesso para ' . $count . ' item(ns)');
        }
    }
}
