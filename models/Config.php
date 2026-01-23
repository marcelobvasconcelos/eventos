<?php
require_once __DIR__ . '/../config/database.php';

class Config {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT `key`, value FROM configuracoes");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function get($key, $default = null) {
        $stmt = $this->pdo->prepare("SELECT value FROM configuracoes WHERE `key` = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public function update($key, $value) {
        $stmt = $this->pdo->prepare("INSERT INTO configuracoes (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        return $stmt->execute([$key, $value]);
    }

    public function set($key, $value) {
        return $this->update($key, $value);
    }
}
