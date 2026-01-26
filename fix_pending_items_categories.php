<?php
require_once 'config/database.php';

try {
    echo "Starting Pending Items Fix (Round 2)...\n";

    // 1. Get all pending items that need fixing (Empty, NULL, 'asset', 'Equipamento')
    $sql = "SELECT id, description, item_type FROM pending_items WHERE item_type IS NULL OR item_type = '' OR item_type IN ('asset', 'Equipamento')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($items) . " items to check.\n";

    $updatedCount = 0;

    foreach ($items as $item) {
        $description = $item['description'];
        
        // Parse Asset Name: "Devolução de [Name]"
        if (strpos($description, 'Devolução de ') === 0) {
            $assetName = substr($description, strlen('Devolução de '));
            
            // Find Asset Category
            // We join assets with asset_categories
            $stmtAsset = $pdo->prepare("
                SELECT c.name as category_name 
                FROM assets a 
                JOIN asset_categories c ON a.category_id = c.id 
                WHERE a.name = ? 
                LIMIT 1
            ");
            $stmtAsset->execute([$assetName]);
            $category = $stmtAsset->fetch(PDO::FETCH_ASSOC);
            
            if ($category && $category['category_name']) {
                $newType = $category['category_name'];
                
                // Update item
                if ($newType !== $item['item_type']) {
                    $updateStmt = $pdo->prepare("UPDATE pending_items SET item_type = :type WHERE id = :id");
                    $updateStmt->execute([':type' => $newType, ':id' => $item['id']]);
                    echo "Updated Item ID {$item['id']}: '{$item['item_type']}' -> '$newType' (Asset: $assetName)\n";
                    $updatedCount++;
                }
            } else {
                echo "Warning: Could not find category for asset '$assetName' (Item ID: {$item['id']})\n";
            }
        }
    }

    echo "Fix Complete. Updated $updatedCount items.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
