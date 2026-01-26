<?php
require_once __DIR__ . '/config/database.php';

try {
    $id = 40;
    
    // Check before
    $stmt = $pdo->prepare("SELECT item_type FROM pending_items WHERE id = ?");
    $stmt->execute([$id]);
    $before = $stmt->fetchColumn();
    echo "ID $id Before: [" . var_export($before, true) . "]\n";

    $sql = "UPDATE pending_items SET item_type = 'Equipamento' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    echo "Update Executed. Rows affected: " . $stmt->rowCount() . "\n";
    
    // Check after
    $stmt = $pdo->prepare("SELECT item_type FROM pending_items WHERE id = ?");
    $stmt->execute([$id]);
    $after = $stmt->fetchColumn();
    echo "ID $id After: [" . var_export($after, true) . "]\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
