<?php
require_once 'config/database.php';

try {
    // Get a few items that SHOULD start with 'Devolução de' to be sure we are looking at the right ones
    $stmt = $pdo->query("SELECT id, item_type, HEX(item_type) as item_type_hex, description FROM pending_items WHERE description LIKE 'Devolução de%' LIMIT 10");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Checking DB Content:\n";
    foreach ($items as $item) {
        echo "ID: " . $item['id'] . "\n";
        echo "Description: " . $item['description'] . "\n";
        echo "Item Type: [" . $item['item_type'] . "]\n"; // Brackets to see if empty
        echo "Hex: " . $item['item_type_hex'] . "\n";
        echo "-------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
