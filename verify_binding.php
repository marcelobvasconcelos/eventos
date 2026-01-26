<?php
require_once 'config/database.php';

try {
    echo "Testing Binding on ID 134...\n";
    
    $val = 'Mobiliário_BIND';
    echo "Value to bind: $val\n";
    
    $stmt = $pdo->prepare("UPDATE pending_items SET item_type = ? WHERE id = 134");
    $stmt->execute([$val]);
    echo "Update executed.\n";
    
    $after = $pdo->query("SELECT item_type FROM pending_items WHERE id = 134")->fetch(PDO::FETCH_ASSOC);
    echo "After: Type=['" . $after['item_type'] . "']\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
