<?php

namespace App\Models;

use PDO;
use PDOException;

class MigrationManager extends Database{
    protected $db;

    public function __construct() {
        try {
            $this->db = self::getConnection();
        } catch (PDOException $e) {
            showAlertLog("ERROR: ". $e->getMessage());
            throw $e;
        }
    }

    public function migrate() {
        try {
            $this->createMigrationsTableIfNotExists();
            $appliedMigrations = $this->getAppliedMigrations();
            $migrationsFiles = scandir(__DIR__ . '/../../migrations');
            $migrationsToApply = array_diff($migrationsFiles, $appliedMigrations);

            foreach ($migrationsToApply as $migrationFile) {
                if ($migrationFile === '.' || $migrationFile === '..') {
                    continue;
                }

                require_once __DIR__ . '/../../migrations/' . $migrationFile;
                $className = pathinfo($migrationFile, PATHINFO_FILENAME);
                $migration = new $className();
                $migration->up();

                $this->markMigrationApplied($migrationFile);
                showLog("Running migration to $migrationFile", true);
            }

            showSuccessLog("Migrations have been executed successfully.");
        } catch (PDOException $e) {
            showAlertLog("ERROR: ". $e->getMessage());
        }
    }

    public function rollback($whichMigration = null, $order = 1) {
        try {
            $appliedMigrations = $this->getAppliedMigrations();
            $migrationsToRollback = array_reverse($appliedMigrations);

            $count = 0;
            foreach ($migrationsToRollback as $migrationFile) {
                if ($count >= $order) {
                    break; // Stop the loop if it reaches the desired number of rollback migrations
                }

                // Check if $whichMigration is set and matches the current migration being processed
                if ($whichMigration !== null && $migrationFile !== $whichMigration) {
                    continue; // Skip this migration if it's not the specified one
                }

                require_once __DIR__ . '/../../migrations/' . $migrationFile;
                $className = pathinfo($migrationFile, PATHINFO_FILENAME);
                $migration = new $className();
                $migration->down();
                $count++;
                $this->removeMigrationRecord($migrationFile);
                showLog("Running the rollback to $migrationFile", true);
            }
    
            showSuccessLog("Rollback executed successfully");
        } catch (PDOException $e) {
            showAlertLog("ERROR: ". $e->getMessage());
        }
    }

    protected function createMigrationsTableIfNotExists() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function getAppliedMigrations() {
        $stmt = $this->db->prepare("SELECT migration FROM migrations");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function markMigrationApplied($migrationFile) {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $stmt->bindValue(':migration', $migrationFile);
        $stmt->execute();
    }

    protected function removeMigrationRecord($migrationFile) {
        $stmt = $this->db->prepare("DELETE FROM migrations WHERE migration = :migration");
        $stmt->bindValue(':migration', $migrationFile);
        $stmt->execute();
    }
}