<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../lib/Security.php';

class AuthController {

    public function login() {
        $returnTo = $_GET['return_to'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $returnTo = $_POST['return_to'] ?? '';

            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/login.php';
                return;
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new User();
            $user = $userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                
                if (!empty($returnTo) && strpos($returnTo, '/') === 0) {
                     header('Location: ' . $returnTo);
                } else {
                     header('Location: /eventos/');
                }
                exit;
            } else {
                $error = 'Invalid email or password';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/login.php';
            }
        } else {
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/login.php';
        }
    }

    public function register() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            $name = $_POST['name'] ?? '';

            $email = $_POST['email'] ?? '';

            $password = $_POST['password'] ?? '';

            $role = $_POST['role'] ?? 'user';

            $userModel = new User();

            try {

                $userModel->register($name, $email, $password, $role);

                header('Location: /eventos/auth/login');

                exit;

            } catch (Exception $e) {

                $error = 'Registration failed: ' . $e->getMessage();

                $csrf_token = Security::generateCsrfToken();

                include __DIR__ . '/../views/auth/register.php';

            }

        } else {

            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/register.php';

        }

    }

    public function logout() {

        session_destroy();

        header('Location: /eventos/');

        exit;

    }

}

?>