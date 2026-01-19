<?php

require_once __DIR__ . '/../config/database.php';

class EventRequest {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createEventRequest($title, $description, $date, $time, $locationId, $categoryId, $userId) {
        $datetime = $date . ' ' . $time;

        $stmt = $this->pdo->prepare("INSERT INTO events (name, description, date, location_id, category_id, status, created_by) VALUES (?, ?, ?, ?, ?, 'Pendente', ?)");
        $stmt->execute([$title, $description, $datetime, $locationId, $categoryId, $userId]);
        return $this->pdo->lastInsertId();
    }

}