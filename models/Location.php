<?php

require_once __DIR__ . '/../config/database.php';

class Location {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAllLocations() {
        $stmt = $this->pdo->prepare("SELECT * FROM locations ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLocationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM locations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createLocation($name, $description, $capacity) {
        $stmt = $this->pdo->prepare("INSERT INTO locations (name, description, capacity) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $description, $capacity]);
    }

    public function updateLocation($id, $name, $description, $capacity) {
        $stmt = $this->pdo->prepare("UPDATE locations SET name = ?, description = ?, capacity = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $capacity, $id]);
    }

    public function deleteLocation($id) {
        $stmt = $this->pdo->prepare("DELETE FROM locations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getLocationsWithAvailability($startDateTime = null, $endDateTime = null, $excludeEventId = null) {
        $sql = "SELECT l.*, 
                CASE WHEN EXISTS (
                    SELECT 1 FROM events e 
                    WHERE e.location_id = l.id 
                    AND e.status IN ('Aprovado', 'Pendente')
                    AND (? < COALESCE(e.end_date, DATE_ADD(e.date, INTERVAL 1 HOUR))) 
                    AND (? > e.date)";
        
        $params = [$startDateTime, $endDateTime];
        if ($excludeEventId) {
            $sql .= " AND e.id != ?";
            $params[] = $excludeEventId;
        }

        $sql .= " ) THEN 1 ELSE 0 END as is_occupied
                FROM locations l 
                ORDER BY l.name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        
        if (!$startDateTime || !$endDateTime) {
             return $this->getAllLocations();
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isAvailable($locationId, $startDateTime, $endDateTime, $excludeEventId = null) {
        $sql = "SELECT COUNT(*) FROM events 
                WHERE location_id = ? 
                AND status IN ('Aprovado', 'Pendente') 
                AND (? < COALESCE(end_date, DATE_ADD(date, INTERVAL 1 HOUR))) 
                AND (? > date)";
        
        // Correct overlap logic: (NewStart < ExistingEnd) AND (NewEnd > ExistingStart)
        // 1st param (?): compared to ExistingEnd. Should be NewStart.
        // 2nd param (?): compared to ExistingStart. Should be NewEnd.
        $params = [$locationId, $startDateTime, $endDateTime];

        if ($excludeEventId) {
            $sql .= " AND id != ?";
            $params[] = $excludeEventId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

}

?>