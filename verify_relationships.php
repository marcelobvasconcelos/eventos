<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$results = [];

try {
    // 1. Check Assets without valid Category relationship
    // a. category_id is NULL
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets WHERE category_id IS NULL");
    $results['assets_null_category'] = $stmt->fetchColumn();

    // b. category_id points to non-existent category
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets a LEFT JOIN asset_categories c ON a.category_id = c.id WHERE c.id IS NULL AND a.category_id IS NOT NULL");
    $results['assets_orphaned_category'] = $stmt->fetchColumn();

    // 2. Check Asset Items without valid Asset relationship
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_items ai LEFT JOIN assets a ON ai.asset_id = a.id WHERE a.id IS NULL");
    $results['items_orphaned_asset'] = $stmt->fetchColumn();

    // 3. Check Loans without valid Item relationship
    $stmt = $pdo->query("SELECT COUNT(*) FROM loans l LEFT JOIN asset_items ai ON l.item_id = ai.id WHERE ai.id IS NULL");
    $results['loans_orphaned_item'] = $stmt->fetchColumn();

    // 4. Sample some Assets with their resolved category names to check for empty strings
    $stmt = $pdo->query("SELECT a.id, a.name, a.category_id, c.name as category_name FROM assets a LEFT JOIN asset_categories c ON a.category_id = c.id LIMIT 10");
    $results['sample_assets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
