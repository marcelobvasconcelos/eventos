<?php
require_once __DIR__ . '/config/database.php';

try {
    $sql = "UPDATE pending_items SET item_type = 'Equipamento' WHERE item_type = '' OR item_type IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "Atualização concluída. Linhas afetadas: " . $stmt->rowCount() . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
