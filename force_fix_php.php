<?php
require_once 'config/database.php';

try {
    echo "Starting PHP Logic Force Fix...\n";
    
    // 1. Get all items with issue
    $stmt = $pdo->query("SELECT id, description, item_type FROM pending_items WHERE item_type IS NULL OR item_type = ''");
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
        $desc = $item['description']; // e.g. "Devolução de Cadeira..."
        $newType = null;
        
        foreach ($mapping as $keyword => $category) {
            // Case insensitive check
            if (mb_stripos($desc, $keyword) !== false) {
                $newType = $category;
                break;
            }
        }
        
        if ($newType) {
            // Update by ID
            $upd = $pdo->prepare("UPDATE pending_items SET item_type = ? WHERE id = ?");
            $upd->execute([$newType, $item['id']]);
            echo "Updated ID " . $item['id'] . " to '$newType' (Matched: $keyword)\n";
            $updatedCount++;
        } else {
            // Default fallback if known pattern not found
            // Maybe it is something else? Set to 'Outros'?
            // Let's print it
            echo "Skipped ID " . $item['id'] . " (" . $desc . ") - No match found.\n";
            
            // Hard fallback?
            // $upd = $pdo->prepare("UPDATE pending_items SET item_type = 'Geral' WHERE id = ?");
            // $upd->execute([$item['id']]);
        }
    }
    
    echo "Fix Complete. Updated $updatedCount items.\n";
    
    // Verify count again
    $finalCount = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type = ''")->fetchColumn();
    echo "Final remaining empty: $finalCount\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
