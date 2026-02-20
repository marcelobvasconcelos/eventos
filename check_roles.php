<?php
require_once 'config/database.php';
try {
    $stmt = $pdo->query("SELECT DISTINCT role FROM users");
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Roles found in DB:\n";
    print_r($roles);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
