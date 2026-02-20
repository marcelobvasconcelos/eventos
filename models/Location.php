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
        $stmt->execute([$name, $description, $capacity]);
        return $this->pdo->lastInsertId();
    }

    public function updateLocation($id, $name, $description, $capacity) {
        $stmt = $this->pdo->prepare("UPDATE locations SET name = ?, description = ?, capacity = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $capacity, $id]);
    }

    public function hasEvents($id) {
        return $this->getEventCount($id) > 0;
    }

    public function getEventCount($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE location_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function deleteLocation($id) {
        if ($this->hasEvents($id)) {
            // Cannot delete location with associated events without reassignment
            return false; 
        }
        $stmt = $this->pdo->prepare("DELETE FROM locations WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function reassignEventsAndDelete($id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Reassign events to 'Local a definir' (custom_location) and NULL location_id
            $stmt = $this->pdo->prepare("UPDATE events SET location_id = NULL, custom_location = 'Local a definir' WHERE location_id = ?");
            $stmt->execute([$id]);

            // 2. Delete associated images
            $stmt = $this->pdo->prepare("DELETE FROM location_images WHERE location_id = ?");
            $stmt->execute([$id]);

            // 3. Delete the location
            $stmt = $this->pdo->prepare("DELETE FROM locations WHERE id = ?");
            $stmt->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getLocationsWithAvailability($startDateTime = null, $endDateTime = null, $excludeEventId = null) {
        $sql = "SELECT l.*, 
                CASE WHEN EXISTS (
                    SELECT 1 FROM events e 
                    WHERE e.location_id = l.id 
                    AND e.status IN ('Aprovado', 'Pendente')
                    AND e.type != 'informativo_calendario'
                    AND (? < COALESCE(e.end_date, DATE_ADD(e.date, INTERVAL 1 HOUR))) 
                    AND (? > e.date)";
        
        $params = [$startDateTime, $endDateTime];
        
        if ($excludeEventId) {
            $sql .= " AND e.id != ?";
            $params[] = $excludeEventId;
        }

        $sql .= "
                ) THEN 1 ELSE 0 END as is_occupied
            FROM locations l
            ORDER BY l.name ASC";
            
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addImages($locationId, $imagePaths) {
        $sql = "INSERT INTO location_images (location_id, image_path) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        foreach ($imagePaths as $path) {
            $stmt->execute([$locationId, $path]);
        }
        return true;
    }

    public function getImages($locationId) {
        $stmt = $this->pdo->prepare("SELECT * FROM location_images WHERE location_id = ?");
        $stmt->execute([$locationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteImage($id) {
        $stmt = $this->pdo->prepare("DELETE FROM location_images WHERE id = ?");
        return $stmt->execute([$id]);
    }


    public function isAvailable($locationId, $startDateTime, $endDateTime, $excludeEventId = null) {
        $sql = "SELECT COUNT(*) FROM events 
                WHERE location_id = ? 
                AND status IN ('Aprovado', 'Pendente') 
                AND type != 'informativo_calendario'
                AND (? < COALESCE(end_date, DATE_ADD(date, INTERVAL 1 HOUR))) 
                AND (? > date)";
        
        $params = [$locationId, $startDateTime, $endDateTime];

        if ($excludeEventId) {
            $sql .= " AND id != ?";
            $params[] = $excludeEventId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function getBlockingEvent($locationId, $startDateTime, $endDateTime, $excludeEventId = null) {
        $sql = "SELECT * FROM events 
                WHERE location_id = ? 
                AND status IN ('Aprovado', 'Pendente') 
                AND type != 'informativo_calendario'
                AND (? < COALESCE(end_date, DATE_ADD(date, INTERVAL 1 HOUR))) 
                AND (? > date)";
        
        $params = [$locationId, $startDateTime, $endDateTime];

        if ($excludeEventId) {
            $sql .= " AND id != ?";
            $params[] = $excludeEventId;
        }
        
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLocationCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM locations");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

}

?>