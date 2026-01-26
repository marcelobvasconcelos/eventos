<?php
require_once 'config/database.php';

try {
    echo "Starting Manual Force Fix...\n";
    
    // Hardcoded mapping based on user request "Mobiliário"
    // We update anything containing "Cadeira" to "Mobiliário"
    
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
        $term = "%$keyword%";
        $stmt->execute([':cat' => $category, ':desc' => $term]);
        
        echo "Updated items matching '$keyword' to '$category'. Rows affected: " . $stmt->rowCount() . "\n";
    }
    
    // Also, let's verify if there are any still empty
    $count = $pdo->query("SELECT COUNT(*) FROM pending_items WHERE item_type = ''")->fetchColumn();
    echo "Remaining empty items: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
