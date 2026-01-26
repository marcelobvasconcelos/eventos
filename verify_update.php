<?php
require_once 'config/database.php';

try {
    echo "Testing Update on ID 134...\n";
    
    // 1. Check before
    $before = $pdo->query("SELECT item_type, description FROM pending_items WHERE id = 134")->fetch(PDO::FETCH_ASSOC);
    echo "Before: Type=['" . $before['item_type'] . "'] Desc=['" . $before['description'] . "']\n";
    
    // 2. Update
    $stmt = $pdo->prepare("UPDATE pending_items SET item_type = 'Mobiliário_FIX' WHERE id = 134");
    $stmt->execute();
    echo "Update executed. Rows affected: " . $stmt->rowCount() . "\n";
    
    // 3. Check after
    $after = $pdo->query("SELECT item_type FROM pending_items WHERE id = 134")->fetch(PDO::FETCH_ASSOC);
    echo "After: Type=['" . $after['item_type'] . "']\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
