<?php

require_once __DIR__ . '/../config/database.php';

class Event {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAllApprovedEvents() {
        $stmt = $this->pdo->prepare("SELECT e.*, l.name as location_name, c.name as category_name FROM events e LEFT JOIN locations l ON e.location_id = l.id LEFT JOIN categories c ON e.category_id = c.id WHERE e.status = 'Aprovado' ORDER BY e.date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByDateRange($startDate, $endDate) {
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE status = 'Aprovado' AND date BETWEEN ? AND ? ORDER BY date ASC");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEvents() {
        $stmt = $this->pdo->prepare("
            SELECT e.*, l.name as location_name 
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            WHERE e.status = 'Aprovado' 
            AND (
                (e.date <= NOW() AND e.end_date >= NOW())
                OR 
                (e.end_date IS NULL AND e.date <= NOW() AND e.date >= DATE_SUB(NOW(), INTERVAL 4 HOUR))
            )
            ORDER BY e.date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByCategory($category) {
        // Since no category column, filter by location containing the category
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE status = 'Aprovado' AND location LIKE ? ORDER BY date ASC");
        $stmt->execute(['%' . $category . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.*, 
                l.name as location_name, 
                c.name as category_name,
                creator.name as creator_name,
                approver.name as approver_name
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            LEFT JOIN categories c ON e.category_id = c.id 
            LEFT JOIN users creator ON e.created_by = creator.id
            LEFT JOIN event_requests er ON e.id = er.event_id

            LEFT JOIN users approver ON e.approved_by = approver.id
            WHERE e.id = ? AND (e.status = 'Aprovado' OR e.status = 'Concluido' OR e.status = 'Pendente')
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPendingEvents() {
        $stmt = $this->pdo->prepare("
            SELECT e.*, l.name as location_name 
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            WHERE e.status = 'Pendente' 
            ORDER BY e.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $approvedBy = null) {
        if ($approvedBy) {
            $stmt = $this->pdo->prepare("UPDATE events SET status = ?, approved_by = ? WHERE id = ?");
            return $stmt->execute([$status, $approvedBy, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE events SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        }
    }

    public function updateEvent($id, $name, $description, $date, $locationId, $categoryId, $status) {
        $stmt = $this->pdo->prepare("UPDATE events SET name = ?, description = ?, date = ?, location_id = ?, category_id = ?, status = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $date, $locationId ?: null, $categoryId ?: null, $status, $id]);
    }

    public function deleteEvent($id) {
        $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createEvent($name, $description, $date, $endDate, $locationId, $categoryId, $createdBy) {
        $stmt = $this->pdo->prepare("INSERT INTO events (name, description, date, end_date, location_id, category_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $date, $endDate ?: null, $locationId, $categoryId, $createdBy]);
        return $this->pdo->lastInsertId();
    }

}

?>