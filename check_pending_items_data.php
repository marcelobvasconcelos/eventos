<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT id, item_type, description, status FROM pending_items ORDER BY id DESC LIMIT 20");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Last 20 Pending Items:\n";
    foreach ($items as $item) {
        echo "ID: " . $item['id'] . " | Type: '" . $item['item_type'] . "' | Desc: " . $item['description'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
