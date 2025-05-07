<?php

namespace App\Core;

class Database {
    private static $instance = null;
    private $connection = null;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->config['database']['host']};dbname={$this->config['database']['name']};charset=utf8mb4";
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->connection = new \PDO(
                    $dsn,
                    $this->config['database']['user'],
                    $this->config['database']['password'],
                    $options
                );
            } catch (\PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new \Exception("Database connection failed");
            }
        }
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            throw new \Exception("Database query failed");
        }
    }

    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    public function commit() {
        return $this->getConnection()->commit();
    }

    public function rollBack() {
        return $this->getConnection()->rollBack();
    }

    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
} 