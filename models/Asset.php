<?php

require_once __DIR__ . '/../config/database.php';

class Asset {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function addAsset($name, $description, $quantity, $category_id = null, $requires_patrimony = 0) {
        $this->pdo->beginTransaction();
        try {
            // 1. Insert Asset
            $stmt = $this->pdo->prepare("INSERT INTO assets (name, description, quantity, available_quantity, category_id, requires_patrimony) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $quantity, $quantity, $category_id, $requires_patrimony]);
            $assetId = $this->pdo->lastInsertId();

            // 2. Insert Asset Items
            $stmtItem = $this->pdo->prepare("INSERT INTO asset_items (asset_id, identification, status) VALUES (?, ?, 'Disponivel')");
            for ($i = 1; $i <= $quantity; $i++) {
                // Simple identification strategy: AssetName-ID-Index
                // Cleaning name for ID: remove spaces, uppercase
                $cleanName = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', substr($name, 0, 3)));
                $identification = sprintf("%s-%04d-%03d", $cleanName, $assetId, $i);
                $stmtItem->execute([$assetId, $identification]);
            }

            $this->pdo->commit();
            return $assetId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            // Log error or throw to be caught by controller
            error_log("Error in addAsset: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllAssets() {
        $stmt = $this->pdo->prepare("SELECT * FROM assets ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableAssets($eventDate = null) {
        return $this->getAllAssetsWithAvailability($eventDate);
    }

    public function getAllAssetsWithAvailability($startDateTime = null, $endDateTime = null, $excludeEventId = null) {
        if ($startDateTime && $endDateTime) {
            // Temporal Availability Logic with Range:
            // Check for overlaps: (LoanStart < RequestEnd) AND (LoanEnd > RequestStart)
            // Available = Total Quantity - Count of Active Loans overlapping the requested range
            // If excludeEventId is provided, do NOT count loans from that event.
            
            $sql = "SELECT a.*, c.name as category_name,
                (a.quantity - (

                    SELECT COUNT(*) 
                    FROM loans l 
                    JOIN events e ON l.event_id = e.id
                    JOIN asset_items ai ON l.item_id = ai.id 
                    WHERE ai.asset_id = a.id 
                    AND l.status = 'Emprestado' 
                    AND e.date = DATE(?)
                    AND (? < DATE_FORMAT(CONCAT(e.date, ' ', e.end_time), '%Y-%m-%d %H:%i:%s')) 
                    AND (? > DATE_FORMAT(CONCAT(e.date, ' ', e.start_time), '%Y-%m-%d %H:%i:%s'))";
            
            $params = [$startDateTime, $startDateTime, $endDateTime];
            if ($excludeEventId) {
                $sql .= " AND l.event_id != ?";
                $params[] = $excludeEventId;
            }
            
            $sql .= ")) as available_count
                FROM assets a
                LEFT JOIN asset_categories c ON a.category_id = c.id
                ORDER BY c.name ASC, a.name ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Post-process to add is_available boolean for backward compatibility if needed, 
            // though views should use available_count now.
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($assets as &$asset) {
                $asset['is_available'] = $asset['available_count'] > 0;
            }
            return $assets;

        } elseif ($startDateTime) {
             // Fallback for just date provided (assume full day overlap if no end time, 
             // but ideally we should always have range now. Left for backward compat or partial implementation step)
             // Using previous logic but mapping to new range logic implies full day.
             $endOfDay = date('Y-m-d 23:59:59', strtotime($startDateTime));
             return $this->getAllAssetsWithAvailability($startDateTime, $endOfDay);
        } else {
            // No date, fallback to physical quantity check
            $stmt = $this->pdo->prepare("
                SELECT a.*, c.name as category_name, a.quantity as available_count, (a.quantity > 0) as is_available 
                FROM assets a
                LEFT JOIN asset_categories c ON a.category_id = c.id
                ORDER BY c.name ASC, a.name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getAssetById($id) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, c.name as category_name 
            FROM assets a 
            LEFT JOIN asset_categories c ON a.category_id = c.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAsset($id, $name, $description, $quantity, $category_id = null, $requires_patrimony = 0) {
        $this->pdo->beginTransaction();
        try {
            // Get current quantity
            $stmt = $this->pdo->prepare("SELECT quantity FROM assets WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();
            
            $delta = $quantity - $current;

            $stmt = $this->pdo->prepare("UPDATE assets SET name = ?, description = ?, quantity = ?, available_quantity = available_quantity + ?, category_id = ?, requires_patrimony = ? WHERE id = ?");
            $stmt->execute([$name, $description, $quantity, $delta, $category_id, $requires_patrimony, $id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function deleteAsset($id) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("DELETE FROM loans WHERE item_id IN (SELECT id FROM asset_items WHERE asset_id = ?)");
            $stmt->execute([$id]);

            $stmt = $this->pdo->prepare("DELETE FROM asset_items WHERE asset_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->pdo->prepare("DELETE FROM assets WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getFutureReservations($asset_id) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT e.id, e.name, e.date, e.start_time, e.end_time
            FROM events e
            JOIN loans l ON e.id = l.event_id
            JOIN asset_items ai ON l.item_id = ai.id
            WHERE ai.asset_id = ?
            AND e.date >= CURDATE()
            AND e.status IN ('Aprovado', 'Pendente')
            ORDER BY e.date ASC
        ");
        $stmt->execute([$asset_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssetCount() {
        // Count total unique asset types defined
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM assets");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

}