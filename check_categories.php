<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM asset_categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Asset Categories:\n";
    foreach ($cats as $c) {
        echo "ID: " . $c['id'] . " | Name: '" . $c['name'] . "'\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
