<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../lib/Security.php';
require_once __DIR__ . '/../lib/Mailer.php';

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

            if ($user === 'pending_approval') {
                $error = 'Sua conta aguarda aprovação do administrador.';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/login.php';
            } elseif ($user) {
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
                $error = 'Email ou senha inválidos.';
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
            $role = 'user';

            $userModel = new User();
            $existing = $userModel->findByEmail($email);
            if ($existing) {
                $error = 'Este email já está cadastrado.';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            try {
                $userId = $userModel->register($name, $email, $role);
                if ($userId) {
                    $token = $userModel->createToken($userId, 'activation');
                    
                    // Send Email
                    $mailer = new Mailer();
                    $link = "http://" . $_SERVER['HTTP_HOST'] . "/eventos/auth/reset_password?token=$token&type=activation";
                    $body = "Olá $name,<br><br>Bem-vindo ao sistema de eventos. Por favor, clique no link abaixo para definir sua senha e ativar sua conta:<br><br><a href='$link'>$link</a><br><br>Este link expira em 1 hora.";
                    
                    if ($mailer->send($email, 'Ativação de Conta - Eventos UAST', $body)) {
                        $success = 'Cadastro realizado! Verifique seu email para definir sua senha.';
                        include __DIR__ . '/../views/auth/login.php'; // Show login with success message
                        return;
                    } else {
                        $error = 'Erro ao enviar email de ativação. Contate o suporte.';
                    }
                }
            } catch (Exception $e) {
                $error = 'Falha no registro: ' . $e->getMessage();
            }
            
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/register.php';

        } else {
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/register.php';
        }
    }

    public function forgot_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/forgot_password.php';
                return;
            }

            $email = $_POST['email'] ?? '';
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user) {
                $token = $userModel->createToken($user['id'], 'recovery');
                $mailer = new Mailer();
                $link = "http://" . $_SERVER['HTTP_HOST'] . "/eventos/auth/reset_password?token=$token&type=recovery";
                $body = "Olá {$user['name']},<br><br>Foi solicitada a recuperação de senha para sua conta. Clique no link abaixo para criar uma nova senha:<br><br><a href='$link'>$link</a><br><br>Este link expira em 1 hora. Se não foi você, ignore este email.";
                
                $mailer->send($email, 'Recuperação de Senha', $body);
            }
            
            // Always show success message for security
            $success = 'Se o e-mail existir, um link de recuperação foi enviado.';
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/forgot_password.php';
        } else {
            $csrf_token = Security::generateCsrfToken();
            include __DIR__ . '/../views/auth/forgot_password.php';
        }
    }

    public function reset_password() {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $type = $_GET['type'] ?? ($_POST['type'] ?? 'recovery');

        $userModel = new User();
        $userId = $userModel->verifyToken($token, $type);

        if (!$userId) {
            $error = 'Link inválido ou expirado.';
            include __DIR__ . '/../views/auth/login.php'; // Or a dedicated error page
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid CSRF token';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if ($password !== $password_confirm) {
                $error = 'As senhas não coincidem.';
                $csrf_token = Security::generateCsrfToken();
                include __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            if ($userModel->updatePassword($userId, $password)) {
                $userModel->invalidateToken($token);
                // If activation, ensure status is Ativo? Assuming "Pendente" meant "Pending Approval", not "Pending Activation".
                // If the prompt says "Ao registrar... a senha NÃO é solicitada", usually this implies email verification.
                // The prompt says "Nova Solicitação: ... Envie simultaneamente um alerta ao Administrador informando sobre a nova pendência de aprovação."
                // This implies "Event Approval", not "User Approval".
                // But `User::login` checks for 'Ativo'. 
                // Let's assume user is Auto-Active upon setting password OR requires Admin approval?
                // `User::register` sets status 'Pendente'. `User::login` blocks if not 'Ativo'.
                // If we want user to use the system, they need 'Ativo'.
                // If admin approval is required for USERS, then after setting password, they still wait.
                // I will NOT auto-activate user status here to be safe, unless prompt implies otherwise.
                // Prompt: "O sistema busca o e-mail, gera um novo token...". Nothing about removing admin approval for users.
                // Wait, "Cadastro: Ao registrar... O sistema de tokens impede que robôs criem contas falsas". This is email verification.
                // Usually email verification -> Active. But if there is also Admin Approval for users...
                // existing code: `register` sets 'Pendente'. `login` blocks. `AdminController::approveUser` sets 'Ativo'.
                // So I will keep 'Pendente' but allow password setting. User sees "Pending Approval" after login.
                
                $success = 'Senha definida com sucesso! Faça login.';
                include __DIR__ . '/../views/auth/login.php';
                return;
            } else {
                $error = 'Erro ao atualizar senha.';
            }
        }

        $csrf_token = Security::generateCsrfToken();
        include __DIR__ . '/../views/auth/reset_password.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /eventos/');
        exit;
    }

}