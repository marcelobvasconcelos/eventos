<?php

require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Event.php'; // For events in form
require_once __DIR__ . '/../models/Asset.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Event.php'; // For events in form
require_once __DIR__ . '/../models/AssetCategory.php'; 
require_once __DIR__ . '/../lib/Security.php';

class AssetController {

    public function index() {
        $assetModel = new Asset();
        $assets = $assetModel->getAllAssetsWithAvailability();
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/asset/index.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }
        $csrf_token = Security::generateCsrfToken();
        
        $categoryModel = new AssetCategory();
        $categories = $categoryModel->getAll();

        include __DIR__ . '/../views/asset/create.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Token CSRF inválido';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/asset/create.php';
                return;
            }

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $quantity = (int)($_POST['quantity'] ?? 0);
            $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $requires_patrimony = isset($_POST['requires_patrimony']) ? 1 : 0;

            if (empty($name) || $quantity < 1 || empty($category_id)) {
                $error = 'Nome, quantidade válida e Categoria são obrigatórios.';
                $csrf_token = Security::generateCsrfToken();
                $categoryModel = new AssetCategory();
                $categories = $categoryModel->getAll();
                include __DIR__ . '/../views/asset/create.php';
                return;
            }

            // DEBUG POINT 2
            // die('DEBUG: REACHED POINT BEFORE ASSET MODEL CALL');

            $assetModel = new Asset();
            try {
                if ($assetModel->addAsset($name, $description, $quantity, $category_id, $requires_patrimony)) {
                    header('Location: /eventos/asset?message=Ativo criado com sucesso');
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Erro ao criar ativo: ' . $e->getMessage();
                $csrf_token = Security::generateCsrfToken();
                $categoryModel = new AssetCategory();
                $categories = $categoryModel->getAll();
                include __DIR__ . '/../views/asset/create.php';
            }
        }
    }

    public function loan() {
        $this->requestLoan();
    }

    public function requestLoan() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token';
            } else {
                $asset_id = $_POST['asset_id'] ?? '';
                $event_id = $_POST['event_id'] ?? '';
                $loan_date = $_POST['loan_date'] ?? '';
                $return_date = $_POST['return_date'] ?? '';

                $loanModel = new Loan();
                if ($loanModel->requestLoan($asset_id, $_SESSION['user_id'], $event_id, $loan_date, $return_date)) {
                    header('Location: /eventos/asset');
                    exit;
                } else {
                    $error = 'Failed to request loan';
                }
            }
        }

        // Get available assets and events for form
        $assetModel = new Asset();
        $eventModel = new Event();
        $events = $eventModel->getAllApprovedEvents();
        $assets = []; // Will be filtered by selected event
        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/asset/loan.php';
    }

    public function checkIn() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                header('Location: /eventos/asset?error=Token CSRF inválido');
                exit;
            }
            $loan_id = $_POST['loan_id'] ?? '';
            $loanModel = new Loan();
            if ($loanModel->returnLoan($loan_id)) {
                header('Location: /eventos/asset');
                exit;
            } else {
                $error = 'Failed to check in';
            }
        }
        // Perhaps redirect or show error
        header('Location: /asset');
        exit;
    }

}