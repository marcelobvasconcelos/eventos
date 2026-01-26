<?php
require_once 'config/database.php';

try {
    $item = $pdo->query("SELECT id, item_type FROM pending_items WHERE id = 134")->fetch(PDO::FETCH_ASSOC);
    echo "ID 134 Item Type: [" . $item['item_type'] . "]\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
