<?php

require_once __DIR__ . '/../config/database.php';

class Event {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAllApprovedEvents() {
        $stmt = $this->pdo->prepare("SELECT e.*, l.name as location_name, c.name as category_name, u.name as creator_name FROM events e LEFT JOIN locations l ON e.location_id = l.id LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN users u ON e.created_by = u.id WHERE e.status IN ('Aprovado', 'Cancelado') ORDER BY e.date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByDateRange($startDate, $endDate) {
        $stmt = $this->pdo->prepare("SELECT e.*, l.name as location_name, c.name as category_name, u.name as creator_name FROM events e LEFT JOIN locations l ON e.location_id = l.id LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN users u ON e.created_by = u.id WHERE e.status IN ('Aprovado', 'Cancelado') AND e.date BETWEEN ? AND ? ORDER BY e.date ASC");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedEventsByDate($date) {
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        // Left join to get location and creator, important for display
        // Logic change: Include events that overlap this day, not just start on it.
        // Overlap Condition: (Start <= DayEnd) AND (End >= DayStart)
        // Handling NULL end_date: Treat as same as start date (point event or short duration)
        $stmt = $this->pdo->prepare("
            SELECT e.*, COALESCE(l.name, e.custom_location) as location_name, c.name as category_name, u.name as creator_name 
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            LEFT JOIN categories c ON e.category_id = c.id
            LEFT JOIN users u ON e.created_by = u.id 
            WHERE e.status IN ('Aprovado', 'Cancelado') 
            AND e.date <= ? 
            AND COALESCE(e.end_date, e.date) >= ?
            ORDER BY e.date ASC
        ");
        $stmt->execute([$endDate, $startDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEvents() {
        $stmt = $this->pdo->prepare("
            SELECT e.*, COALESCE(l.name, e.custom_location) as location_name, u.name as creator_name
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            LEFT JOIN users u ON e.created_by = u.id
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
                COALESCE(l.name, e.custom_location) as location_name, 
                c.name as category_name,
                creator.name as creator_name,
                approver.name as approver_name
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            LEFT JOIN categories c ON e.category_id = c.id 
            LEFT JOIN users creator ON e.created_by = creator.id
            LEFT JOIN event_requests er ON e.id = er.event_id

            LEFT JOIN users approver ON e.approved_by = approver.id
            WHERE e.id = ? AND (e.status = 'Aprovado' OR e.status = 'Concluido' OR e.status = 'Pendente' OR e.status = 'Cancelado')
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPendingEvents() {
        $stmt = $this->pdo->prepare("
            SELECT e.*, COALESCE(l.name, e.custom_location) as location_name 
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

    public function updateEvent($id, $name, $description, $date, $endDate, $locationId, $categoryId, $status, $isPublic, $imagePath = null, $externalLink = null, $linkTitle = null, $publicEstimation = 0, $scheduleFilePath = null, $customLocation = null) {
        $sql = "UPDATE events SET name = ?, description = ?, date = ?, end_date = ?, location_id = ?, category_id = ?, status = ?, is_public = ?, external_link = ?, link_title = ?, public_estimation = ?, custom_location = ?";
        $params = [$name, $description, $date, $endDate ?: null, $locationId ?: null, $categoryId ?: null, $status, $isPublic, $externalLink, $linkTitle, $publicEstimation, $customLocation];
        
        if ($imagePath !== null) { 
            $sql .= ", image_path = ?";
            $params[] = $imagePath;
        }

        if ($scheduleFilePath !== null) {
            $sql .= ", schedule_file_path = ?";
            $params[] = $scheduleFilePath;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteEvent($id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Delete associated loans
            $stmt = $this->pdo->prepare("DELETE FROM loans WHERE event_id = ?");
            $stmt->execute([$id]);

            // 2. Delete pending items
            $stmt = $this->pdo->prepare("DELETE FROM pending_items WHERE event_id = ?");
            $stmt->execute([$id]);

            // 3. Delete event requests reference (if table setup allows deletion or requires update)
            // Assuming event_requests table might link to event_id. 
            // Based on earlier view, event_requests links user to event? Let's check model structure implications.
            // If event_requests.event_id is FK, we should delete.
            $stmt = $this->pdo->prepare("DELETE FROM event_requests WHERE event_id = ?");
            $stmt->execute([$id]);

            // 4. Delete the event
            $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = ?");
            $result = $stmt->execute([$id]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Log error if possible
            return false;
        }
    }

    public function createEvent($name, $description, $date, $endDate, $locationId, $categoryId, $createdBy, $isPublic = 1, $imagePath = null, $externalLink = null, $linkTitle = null, $publicEstimation = 0, $scheduleFilePath = null, $customLocation = null) {
        $stmt = $this->pdo->prepare("INSERT INTO events (name, description, date, end_date, location_id, category_id, created_by, is_public, image_path, external_link, link_title, public_estimation, schedule_file_path, custom_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $date, $endDate ?: null, $locationId, $categoryId, $createdBy, $isPublic, $imagePath, $externalLink, $linkTitle, $publicEstimation, $scheduleFilePath, $customLocation]);
        return $this->pdo->lastInsertId();
    }

    public function getFutureEventsCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE status = 'Aprovado' AND date >= CURDATE()");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getPendingEventsCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE status = 'Pendente'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getEventsReport($filters = []) {
        $sql = "SELECT e.id, e.name, e.date, COALESCE(l.name, e.custom_location) as location_name 
                FROM events e 
                LEFT JOIN locations l ON e.location_id = l.id 
                WHERE 1=1";
        
        $params = [];

        // Search Filter (Name or Location)
        if (!empty($filters['search']) && strlen($filters['search']) >= 3) {
            $sql .= " AND (e.name LIKE ? OR l.name LIKE ? OR e.custom_location LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Date Range Filter
        if (!empty($filters['startDate'])) {
            $sql .= " AND e.date >= ?";
            $params[] = $filters['startDate'] . ' 00:00:00';
        }
        if (!empty($filters['endDate'])) {
            $sql .= " AND e.date <= ?";
            $params[] = $filters['endDate'] . ' 23:59:59';
        }

        // Status Filter (Optional, strict to approved/active usually for reports, or all?)
        // Requirement implies "Relatórios de Eventos". Usually valid events.
        // Let's include all non-deleted if not specified, or maybe just Approved/Concluded?
        // Let's assume all for now or filter by Approved/Concluded/Pending?
        // User didn't specify status. Let's show all except maybe deleted (but deletes are hard delete).
        // Let's show distinct statuses in UI? Or just mix.
        // I'll add status to select to show it maybe? Specification: "Nome, Data, Local". 
        // I will stick to specs. Filters might be broad.

        // Ordering
        $allowedSorts = ['name' => 'e.name', 'date' => 'e.date', 'location' => 'location_name'];
        $orderBy = $filters['orderBy'] ?? 'date';
        $orderDir = strtoupper($filters['orderDir'] ?? 'ASC');
        
        if (!array_key_exists($orderBy, $allowedSorts)) {
            $orderBy = 'date';
        }
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'ASC';
        }
        
        $sql .= " ORDER BY " . $allowedSorts[$orderBy] . " " . $orderDir;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>