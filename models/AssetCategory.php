<?php

require_once __DIR__ . '/../config/database.php';

class AssetCategory {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM asset_categories ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM asset_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO asset_categories (name, description) VALUES (?, ?)");
        if ($stmt->execute([$name, $description])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function update($id, $name, $description) {
        $stmt = $this->pdo->prepare("UPDATE asset_categories SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    /* 
     * Delete a category.
     * Note: Assets with this category won't be deleted, their category_id will be set to NULL (via FK ON DELETE SET NULL).
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM asset_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
