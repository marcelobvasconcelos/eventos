<?php

require_once __DIR__ . '/../config/database.php';

class EventRequest {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createRequest($userId, $eventId) {
        $stmt = $this->pdo->prepare("INSERT INTO event_requests (user_id, event_id, status) VALUES (?, ?, 'Pendente')");
        $stmt->execute([$userId, $eventId]);
        return $this->pdo->lastInsertId();
    }

    public function getRequestsByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT er.*, e.name as event_name, e.date as event_date, e.status as event_status, e.status as status 
            FROM event_requests er 
            JOIN events e ON er.event_id = e.id 
            WHERE er.user_id = ? 
            ORDER BY er.request_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}