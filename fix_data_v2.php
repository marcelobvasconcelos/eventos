<?php
require_once __DIR__ . '/config/database.php';

try {
    // Check count first
    $stmt = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type IS NULL OR TRIM(item_type) = ''");
    $count = $stmt->fetchColumn();
    echo "Itens encontrados para correção: " . $count . "\n";

    if ($count > 0) {
        $sql = "UPDATE pending_items SET item_type = 'Equipamento' WHERE item_type IS NULL OR TRIM(item_type) = ''";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
        
        if ($result) {
            echo "Comando executado com sucesso.\n";
            echo "Linhas afetadas: " . $stmt->rowCount() . "\n";
        } else {
            print_r($stmt->errorInfo());
        }
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
