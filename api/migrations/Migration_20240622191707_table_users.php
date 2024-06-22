<?php

use App\Http\JWTManager;
use App\Models\Database;

class Migration_20240622191707_table_users extends Database{
    protected $db;

    public function __construct() {
        $this->db = self::getConnection();
    }

    public function up() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            lastName VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            token TEXT NOT NULL,
            active ENUM('Y', 'N'),
            role VARCHAR(50) NOT NULL,
            photo VARCHAR(255),
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $sql = $sql = "INSERT INTO users (name, email, password, token, active, role) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->db->prepare($sql);

        $values = [
            "Administrador",
            $_ENV['DEVEMAIL'],
            password_hash("aguip707", PASSWORD_DEFAULT),
            JWTManager::newCrypto(),
            "Y",
            "super"
        ];

        $stmt->execute($values);
    }

    public function down() {
        // Migration implementation (rollback)
    }
}