<?php

require_once __DIR__ . '/../config/database.php';

class Event {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAllApprovedEvents() {
        $stmt = $this->pdo->prepare("SELECT e.*, l.name as location_name, c.name as category_name, u.name as creator_name FROM events e LEFT JOIN locations l ON e.location_id = l.id LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN users u ON e.created_by = u.id WHERE e.status IN ('Aprovado', 'Cancelado') AND e.type != 'bloqueio_administrativo' AND e.type != 'informativo_calendario' ORDER BY e.date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHighlights() {
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE type = 'informativo_calendario' ORDER BY date DESC");
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
            AND e.type != 'bloqueio_administrativo'
            AND e.type != 'informativo_calendario'
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
            AND e.type != 'bloqueio_administrativo'
            AND e.type != 'informativo_calendario'
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

    public function updateEvent($id, $name, $description, $date, $endDate, $locationId, $categoryId, $status, $isPublic, $imagePath = null, $externalLink = null, $linkTitle = null, $publicEstimation = 0, $scheduleFilePath = null, $customLocation = null, $requiresRegistration = 0, $maxParticipants = null, $hasCertificate = 0) {
        $sql = "UPDATE events SET name = ?, description = ?, date = ?, end_date = ?, location_id = ?, category_id = ?, status = ?, is_public = ?, external_link = ?, link_title = ?, public_estimation = ?, custom_location = ?, requires_registration = ?, max_participants = ?, has_certificate = ?";
        $params = [$name, $description, $date, $endDate ?: null, $locationId ?: null, $categoryId ?: null, $status, $isPublic, $externalLink, $linkTitle, $publicEstimation, $customLocation, $requiresRegistration, $maxParticipants, $hasCertificate];
        
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

    public function createEvent($name, $description, $date, $end_date, $location_id, $category_id, $created_by, $status = 'Pendente', $type = 'evento_publico', $is_public = 1, $image_path = null, $external_link = null, $link_title = null, $public_estimation = 0, $schedule_file_path = null, $custom_location = null, $requires_registration = 0, $max_participants = null, $has_certificate = 0) {
        $sql = "INSERT INTO events (name, description, date, end_date, location_id, category_id, created_by, status, type, is_public, image_path, external_link, link_title, public_estimation, schedule_file_path, custom_location, requires_registration, max_participants, has_certificate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $name, 
            $description, 
            $date, 
            $end_date,
            $location_id, 
            $category_id, 
            $created_by,
            $status,
            $type,
            $is_public,
            $image_path,
            $external_link,
            $link_title,
            $public_estimation,
            $schedule_file_path,
            $custom_location,
            $requires_registration,
            $max_participants,
            $has_certificate
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getFutureEventsCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM events WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND date >= CURDATE()");
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
    public function getRealizedHours() {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, date, COALESCE(end_date, DATE_ADD(date, INTERVAL 4 HOUR)))), 0) 
            FROM events 
            WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario'
            AND date < NOW() 
            AND YEAR(date) = YEAR(CURDATE())
        ");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getAnalyticsData($year) {
        $stats = [
            'location_stats' => [],
            'hours_stats' => ['today' => 0, 'week' => 0, 'month' => 0, 'year' => 0],
            'timeline_stats' => [],
            'status_stats' => ['realized' => 0, 'scheduled' => 0],
            'total_events' => 0
        ];

        // 1. Location Stats
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(l.name, e.custom_location) as name, COUNT(e.id) as total 
            FROM events e 
            LEFT JOIN locations l ON e.location_id = l.id 
            WHERE YEAR(e.date) = ? AND e.status = 'Aprovado' AND e.type != 'bloqueio_administrativo' AND e.type != 'informativo_calendario'
            GROUP BY COALESCE(l.name, e.custom_location)
            ORDER BY total DESC
        ");
        $stmt->execute([$year]);
        $stats['location_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Hours Stats (Approximation: 4h default if end_date missing)
        // Today
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, date, COALESCE(end_date, DATE_ADD(date, INTERVAL 4 HOUR)))), 0) 
            FROM events WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND DATE(date) = CURDATE()
        ");
        $stmt->execute();
        $stats['hours_stats']['today'] = $stmt->fetchColumn();

        // This Week
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, date, COALESCE(end_date, DATE_ADD(date, INTERVAL 4 HOUR)))), 0) 
            FROM events WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND YEAR(date) = YEAR(CURDATE()) AND WEEK(date) = WEEK(CURDATE())
        ");
        $stmt->execute();
        $stats['hours_stats']['week'] = $stmt->fetchColumn();

        // This Month
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, date, COALESCE(end_date, DATE_ADD(date, INTERVAL 4 HOUR)))), 0) 
            FROM events WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())
        ");
        $stmt->execute();
        $stats['hours_stats']['month'] = $stmt->fetchColumn();

        // Total Year
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(TIMESTAMPDIFF(HOUR, date, COALESCE(end_date, DATE_ADD(date, INTERVAL 4 HOUR)))), 0) 
            FROM events WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND YEAR(date) = ?
        ");
        $stmt->execute([$year]);
        $stats['hours_stats']['year'] = $stmt->fetchColumn();

        // 3. Timeline & Status Stats
        // Group by Month
        $stmt = $this->pdo->prepare("
            SELECT 
                MONTH(date) as month,
                SUM(CASE WHEN date < NOW() THEN 1 ELSE 0 END) as realized,
                SUM(CASE WHEN date >= NOW() THEN 1 ELSE 0 END) as scheduled
            FROM events 
            WHERE status = 'Aprovado' AND type != 'bloqueio_administrativo' AND type != 'informativo_calendario' AND YEAR(date) = ?
            GROUP BY MONTH(date)
            ORDER BY month ASC
        ");
        $stmt->execute([$year]);
        $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize all 12 months with 0
        for ($i = 1; $i <= 12; $i++) {
            $stats['timeline_stats'][$i] = ['realized' => 0, 'scheduled' => 0];
        }

        foreach ($monthlyData as $row) {
            $stats['timeline_stats'][$row['month']] = [
                'realized' => (int)$row['realized'],
                'scheduled' => (int)$row['scheduled']
            ];
            $stats['status_stats']['realized'] += (int)$row['realized'];
            $stats['status_stats']['scheduled'] += (int)$row['scheduled'];
        }
        
        $stats['total_events'] = $stats['status_stats']['realized'] + $stats['status_stats']['scheduled'];

        return $stats;
    }
}