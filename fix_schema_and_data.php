<?php
require_once __DIR__ . '/config/database.php';

try {
    echo "1. Altering Table to VARCHAR...\n";
    $pdo->exec("ALTER TABLE pending_items MODIFY COLUMN item_type VARCHAR(100) NOT NULL DEFAULT 'asset'");
    echo "Table Update Successful.\n";
    
    echo "2. Formatting 'key' to 'Chave' for consistency (Optional but good)...\n";
    $pdo->exec("UPDATE pending_items SET item_type = 'Chave' WHERE item_type = 'key'");

    echo "3. Fixing empty items to 'Equipamento'...\n";
    $sql = "UPDATE pending_items SET item_type = 'Equipamento' WHERE item_type IS NULL OR TRIM(item_type) = '' OR item_type = 'asset'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo "Rows updated: " . $stmt->rowCount() . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
