<?php

require_once __DIR__ . '/../config/database.php';

class AssetItem {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAvailableItems($eventDate = null) {
        if ($eventDate) {
            // Get items not loaned on the event date
            $stmt = $this->pdo->prepare("
                SELECT ai.*, a.name as asset_name, a.description as asset_description FROM asset_items ai
                JOIN assets a ON ai.asset_id = a.id
                WHERE ai.status = 'Disponivel'
                AND ai.id NOT IN (
                    SELECT l.item_id FROM loans l
                    WHERE l.status = 'Emprestado'
                    AND ? BETWEEN l.loan_date AND COALESCE(l.return_date, l.loan_date)
                )
                ORDER BY a.name ASC, ai.identification ASC
            ");
            $stmt->execute([$eventDate]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT ai.*, a.name as asset_name, a.description as asset_description FROM asset_items ai
                JOIN assets a ON ai.asset_id = a.id
                WHERE ai.status = 'Disponivel'
                ORDER BY a.name ASC, ai.identification ASC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>