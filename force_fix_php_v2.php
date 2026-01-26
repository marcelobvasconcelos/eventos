<?php
require_once 'config/database.php';

try {
    echo "Starting PHP Logic Force Fix (Round 3 - Direct SQL)...\n";
    
    // 1. Get all items with issue
    $stmt = $pdo->query("SELECT id, description FROM pending_items WHERE item_type IS NULL OR item_type = ''");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($items) . " empty items.\n";
    
    $mapping = [
        'Cadeira' => 'Mobiliário',
        'Mesa' => 'Mobiliário',
        'Sofá' => 'Mobiliário',
        'Poltrona' => 'Mobiliário',
        'Projetor' => 'Audio e Vídeo',
        'Microfone' => 'Audio e Vídeo',
        'Caixa de Som' => 'Audio e Vídeo',
        'Iluminação' => 'Iluminação',
        'Refletor' => 'Iluminação'
    ];
    
    $updatedCount = 0;
    
    foreach ($items as $item) {
        $desc = $item['description'];
        $newType = null;
        
        foreach ($mapping as $keyword => $category) {
            if (mb_stripos($desc, $keyword) !== false) {
                $newType = $category;
                break;
            }
        }
        
        if ($newType) {
            // Direct injection since values are hardcoded safe strings
            $sql = "UPDATE pending_items SET item_type = '$newType' WHERE id = " . $item['id'];
            $pdo->exec($sql);
            echo "Updated ID " . $item['id'] . " to '$newType' (Matched: $keyword)\n";
            $updatedCount++;
        }
    }
    
    echo "Fix Complete. Updated $updatedCount items.\n";
    
    $finalCount = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type = ''")->fetchColumn();
    echo "Final remaining empty: $finalCount\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
