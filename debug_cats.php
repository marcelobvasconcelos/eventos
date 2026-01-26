<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM asset_categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cats, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
