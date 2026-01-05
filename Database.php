<?php

class Database {
    private static ?PDO $instance = null;
    
    private function __construct() {}
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $host = 'localhost';
                $dbname = 'photospheredb';
                $username = 'root';
                $password = '';
                $charset = 'utf8mb4';
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
}