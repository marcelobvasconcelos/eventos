<?php

require_once __DIR__ . '/../config/database.php';

class EventEdit {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createProposal($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO event_edits (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function getPendingProposals() {
        $stmt = $this->pdo->prepare("
            SELECT ee.*, ee.created_at as proposed_at,
                   e.name as original_name, e.description as original_description,
                   e.date as original_date, e.start_time as original_start_time,
                   e.end_time as original_end_time, e.category_id as original_category_id,
                   e.is_public as original_is_public, e.external_link as original_external_link,
                   e.link_title as original_link_title, e.custom_location as original_custom_location,
                   e.image_path as original_image_path, e.schedule_file_path as original_schedule_file_path,
                   u.name as user_name 
            FROM event_edits ee
            JOIN events e ON ee.event_id = e.id
            JOIN users u ON ee.user_id = u.id
            WHERE ee.status = 'Pendente'
            ORDER BY ee.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProposalById($id) {
        $stmt = $this->pdo->prepare("
            SELECT ee.*, ee.created_at as proposed_at,
                   e.name as original_name, e.description as original_description,
                   e.date as original_date, e.start_time as original_start_time,
                   e.end_time as original_end_time, e.category_id as original_category_id,
                   e.is_public as original_is_public, e.external_link as original_external_link,
                   e.link_title as original_link_title, e.custom_location as original_custom_location,
                   e.image_path as original_image_path, e.schedule_file_path as original_schedule_file_path,
                   u.name as user_name 
            FROM event_edits ee
            JOIN events e ON ee.event_id = e.id
            JOIN users u ON ee.user_id = u.id
            WHERE ee.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $adminId, $notes = '') {
        $stmt = $this->pdo->prepare("
            UPDATE event_edits 
            SET status = ?, processed_by = ?, admin_notes = ?, processed_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $adminId, $notes, $id]);
    }

    public function applyProposal($proposalId, $adminId) {
        $proposal = $this->getProposalById($proposalId);
        if (!$proposal) return false;

        $this->pdo->beginTransaction();
        try {
            // Update the main event
            require_once __DIR__ . '/Event.php';
            $eventModel = new Event();
            
            // We only update fields that are present in the proposal
            $updateFields = [];
            $params = [];
            $editableFields = ['name', 'description', 'date', 'start_time', 'end_time', 'category_id', 'is_public', 'external_link', 'link_title', 'image_path', 'custom_location', 'schedule_file_path'];
            
            foreach ($editableFields as $field) {
                if ($proposal[$field] !== null) {
                    $updateFields[] = "$field = ?";
                    $params[] = $proposal[$field];
                }
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE events SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $params[] = $proposal['event_id'];
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // Mark proposal as approved
            $this->updateStatus($proposalId, 'Aprovado', $adminId);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getPendingCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM event_edits WHERE status = 'Pendente'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
