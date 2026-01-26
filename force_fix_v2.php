<?php
require_once 'config/database.php';

try {
    echo "Starting Manual Force Fix (Round 2)...\n";
    
    // Updates
    $updates = [
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
    
    foreach ($updates as $keyword => $category) {
        $sql = "UPDATE pending_items SET item_type = :cat WHERE description LIKE :desc AND (item_type IS NULL OR item_type = '')";
        $stmt = $pdo->prepare($sql);
        // Ensure wildcards are around the keyword
        $term = "%" . $keyword . "%"; 
        $stmt->execute([':cat' => $category, ':desc' => $term]);
        
        echo "Updated items matching '$keyword' to '$category'. Rows affected: " . $stmt->rowCount() . "\n";
    }

    // Fallback for anything else: set to 'Geral' or check manually
    // Let's manually set ID 134 back to 'Mobiliário' if it was left as 'Mobiliário_FIX'
    $pdo->exec("UPDATE pending_items SET item_type = 'Mobiliário' WHERE item_type = 'Mobiliário_FIX'");
    
    $count = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type = ''")->fetchColumn();
    echo "Remaining empty items: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
