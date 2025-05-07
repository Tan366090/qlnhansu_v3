<?php

class DatabaseConfig {
    // Database connection constants
    const HOST = 'localhost';
    const PORT = 3306;
    const NAME = 'qlnhansu';
    const USER = 'root';
    const PASS = '';
    const CHARSET = 'utf8mb4';

    // PDO options
    const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    // Get database DSN
    public static function getDSN() {
        return sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            self::HOST,
            self::PORT,
            self::NAME,
            self::CHARSET
        );
    }

    // Get PDO connection
    public static function getConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    self::getDSN(),
                    self::USER,
                    self::PASS,
                    self::PDO_OPTIONS
                );
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        
        return $pdo;
    }
} 