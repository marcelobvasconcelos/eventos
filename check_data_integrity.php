<?php
require_once 'config/database.php';

echo "<h2>Checking Data Integrity</h2>";

// 1. Check Assets vs Asset Items
$stmt = $pdo->query("SELECT id, name, quantity FROM assets");
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($assets as $asset) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM asset_items WHERE asset_id = ?");
    $stmtCount->execute([$asset['id']]);
    $itemCount = $stmtCount->fetchColumn();
    
    if ($itemCount != $asset['quantity']) {
        echo "<p style='color:red;'>MISMATCH for Asset [{$asset['name']}]: Configured Quantity = {$asset['quantity']}, Physical Items Found = $itemCount.</p>";
        
        // Repair
        if ($itemCount < $asset['quantity']) {
            $diff = $asset['quantity'] - $itemCount;
            echo " -> Repairing... Adding $diff items.<br>";
            
            $stmtItem = $pdo->prepare("INSERT INTO asset_items (asset_id, identification, status) VALUES (?, ?, 'Disponivel')");
            for ($i = 1; $i <= $diff; $i++) {
                // Generate simple ID
                $cleanName = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', substr($asset['name'], 0, 3)));
                // Use explicit timestamp or random to ensure unique if standard sequence is broken, 
                // but simple increment based on current count is safer visually.
                $newIndex = $itemCount + $i;
                $identification = sprintf("%s-%04d-%03d", $cleanName, $asset['id'], $newIndex);
                $stmtItem->execute([$asset['id'], $identification]);
            }
            echo " -> FIXED.<br>";
        } else {
            echo " -> Physical items > Configured Quantity. Updating Asset Quantity to match physical items.<br>";
            $pdo->prepare("UPDATE assets SET quantity = ? WHERE id = ?")->execute([$itemCount, $asset['id']]);
             echo " -> FIXED.<br>";
        }
    } else {
        echo "<p style='color:green;'>OK: Asset [{$asset['name']}] (Qty: {$asset['quantity']})</p>";
    }
}

echo "<hr>Done.";
?>
