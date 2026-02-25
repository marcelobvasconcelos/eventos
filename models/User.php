<?php

require_once __DIR__ . '/../config/database.php';

class User {

    private $pdo;

    public function __construct() {

        global $pdo;

        $this->pdo = $pdo;

    }

    public function register($name, $email, $role = 'user') {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, NULL, ?, 'Pendente')");
        if ($stmt->execute([$name, $email, $role])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Validates if user has a password set and verifies it
        if ($user && $user['password'] !== null && password_verify($password, $user['password'])) {
            // Check status
            if ($user['status'] !== 'Ativo') {
                return 'pending_approval'; // Should ideally be handled by checking activation token status too? No, status checks admin approval usually.
                // If it's pure email validation flow, maybe we auto-activate after password set?
                // For now, respect existing logic but add null check.
            }
            return $user;
        }
        return false;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createToken($userId, $type) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Invalidate old tokens of same type
        $stmt = $this->pdo->prepare("DELETE FROM user_tokens WHERE user_id = ? AND type = ?");
        $stmt->execute([$userId, $type]);

        $stmt = $this->pdo->prepare("INSERT INTO user_tokens (user_id, token, type, expires_at) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$userId, $token, $type, $expires])) {
            return $token;
        }
        return false;
    }

    public function verifyToken($token, $type) {
        $stmt = $this->pdo->prepare("SELECT user_id, expires_at FROM user_tokens WHERE token = ? AND type = ?");
        $stmt->execute([$token, $type]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && strtotime($data['expires_at']) > time()) {
            return $data['user_id'];
        }
        return false;
    }

    public function invalidateToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM user_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public function checkRole($userId) {

        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");

        $stmt->execute([$userId]);

        $result = $stmt->fetch();

        return $result ? $result['role'] : null;

    }

    public function getAllUsers() {

        $stmt = $this->pdo->prepare("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function updateRole($userId, $role) {

        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");

        return $stmt->execute([$role, $userId]);

    }

    public function updateStatus($userId, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $userId]);
    }

    public function updateUser($userId, $name, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $userId]);
    }

    public function updatePassword($userId, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }

    public function deleteUser($userId) {
        try {
            $this->pdo->beginTransaction();

            // 1. Remove orphaned references (Set NULL)
            $stmt = $this->pdo->prepare("UPDATE events SET created_by = NULL WHERE created_by = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("UPDATE event_requests SET approved_by = NULL WHERE approved_by = ?");
            $stmt->execute([$userId]);

            // 2. Delete dependent records (Cascade manually)
            $stmt = $this->pdo->prepare("DELETE FROM loans WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("DELETE FROM event_requests WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$userId]);

            // 3. Delete the user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getDependencyStats($userId) {
        $stats = [];

        // Count loans
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['loans'] = $stmt->fetchColumn();

        // Count event requests
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM event_requests WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats['requests'] = $stmt->fetchColumn();

        // Count events created (will be set NULL)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE created_by = ?");
        $stmt->execute([$userId]);
        $stats['events_created'] = $stmt->fetchColumn();

        // Count requests approved (will be set NULL)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM event_requests WHERE approved_by = ?");
        $stmt->execute([$userId]);
        $stats['approvals'] = $stmt->fetchColumn();

        return $stats;
    }

    public function getUserCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getPendingUsersCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE status = 'Pendente'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getPendingUsers() {
        $stmt = $this->pdo->prepare("SELECT id, name, email, created_at FROM users WHERE status = 'Pendente' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>