<?php

require_once __DIR__ . '/../config/database.php';

class PendingItem {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($eventId, $userId, $itemType, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO pending_items (event_id, user_id, item_type, description, status) VALUES (?, ?, ?, ?, 'pending')");
        return $stmt->execute([$eventId, $userId, $itemType, $description]);
    }

    public function getPendingByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT pi.*, e.name as event_name, e.date as event_date, e.end_date as event_end_date 
            FROM pending_items pi 
            JOIN events e ON pi.event_id = e.id 
            WHERE pi.user_id = ? AND pi.status != 'completed' 
            ORDER BY pi.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllItemsByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT pi.*, e.name as event_name, e.date as event_date, e.end_date as event_end_date 
            FROM pending_items pi 
            JOIN events e ON pi.event_id = e.id 
            WHERE pi.user_id = ?
            ORDER BY pi.status = 'pending' DESC, pi.status = 'user_informed' DESC, pi.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPendingCountByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pending_items WHERE user_id = ? AND status != 'completed'");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn(); 
    }

    public function getAllPending() {
        $stmt = $this->pdo->prepare("
            SELECT pi.*, e.name as event_name, u.name as user_name 
            FROM pending_items pi 
            JOIN events e ON pi.event_id = e.id 
            JOIN users u ON pi.user_id = u.id 
            WHERE pi.status != 'completed' 
            ORDER BY pi.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllItems() {
        $stmt = $this->pdo->prepare("
            SELECT pi.*, e.name as event_name, u.name as user_name 
            FROM pending_items pi 
            JOIN events e ON pi.event_id = e.id 
            JOIN users u ON pi.user_id = u.id 
            ORDER BY pi.status = 'user_informed' DESC, pi.status = 'pending' DESC, pi.status = 'contested' DESC, pi.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllPendingCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pending_items WHERE status != 'completed'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function updateStatus($id, $status, $observation = null, $userNote = null) {
        $sql = "UPDATE pending_items SET status = ?";
        $params = [$status];

        if ($observation !== null) {
            $sql .= ", observation = ?";
            $params[] = $observation;
        }

        if ($userNote !== null) {
            $sql .= ", user_note = ?";
            $params[] = $userNote;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pending_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Check if pending items already exist for this event to avoid duplicates
    public function existsForEvent($eventId, $itemType) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pending_items WHERE event_id = ? AND item_type = ?");
        $stmt->execute([$eventId, $itemType]);
        return $stmt->fetchColumn() > 0;
    }
}
