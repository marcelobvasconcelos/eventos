<?php

require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../lib/Security.php';

class LocationController {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /eventos/auth/login');
            exit;
        }

        $locationModel = new Location();
        $locations = $locationModel->getAllLocations();
        $csrf_token = Security::generateCsrfToken();
        
        include __DIR__ . '/../views/location/index.php';
    }

}
?>
