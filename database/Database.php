<?php
/**
 * Classe de connexion à la base de données utilisant le pattern Singleton
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Configuration de la base de données
            $host = 'localhost';
            $db   = 'chat_system';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';

            // Construction du DSN et options PDO
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch(PDOException $e) {
            die("Erreur de connexion: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
