<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    // Get last 20 items
    $stmt = $pdo->query("SELECT id, item_type, description, status, created_at FROM pending_items ORDER BY created_at DESC LIMIT 20");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $stmt = $pdo->query("SELECT item_type, COUNT(*) as count FROM pending_items GROUP BY item_type");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['items' => $items, 'stats' => $stats], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
