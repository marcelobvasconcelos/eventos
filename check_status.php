<?php
require_once 'config/database.php';

try {
    $item = $pdo->query("SELECT id, item_type FROM pending_items WHERE id = 134")->fetch(PDO::FETCH_ASSOC);
    echo "ID 134 Item Type: [" . $item['item_type'] . "]\n";
    
    // Check broad count
    $count = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type IS NULL OR item_type = ''")->fetchColumn();
    echo "Real Empty Count: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
