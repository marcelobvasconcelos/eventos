<?php

require_once __DIR__ . '/../config/database.php';

class User {

    private $pdo;

    public function __construct() {

        global $pdo;

        $this->pdo = $pdo;

    }

    public function register($name, $email, $password, $role = 'user') {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

        return $stmt->execute([$name, $email, $hashedPassword, $role]);

    }

    public function login($email, $password) {

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            return $user;

        }

        return false;

    }

    public function checkRole($userId) {

        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");

        $stmt->execute([$userId]);

        $result = $stmt->fetch();

        return $result ? $result['role'] : null;

    }

    public function getAllUsers() {

        $stmt = $this->pdo->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function updateRole($userId, $role) {

        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");

        return $stmt->execute([$role, $userId]);

    }

    public function updateUser($userId, $name, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $userId]);
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

}

?>