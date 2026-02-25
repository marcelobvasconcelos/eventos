<?php

require_once __DIR__ . '/../config/database.php';

class EventRequest {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createRequest($userId, $eventId, $status = 'Pendente') {
        $stmt = $this->pdo->prepare("INSERT INTO event_requests (user_id, event_id, status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $eventId, $status]);
        return $this->pdo->lastInsertId();
    }

    public function getRequestsByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                id as event_id, 
                id,
                name as event_name, 
                date as event_date, 
                status as event_status, 
                status as status,
                created_at as request_date
            FROM events 
            WHERE created_by = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}