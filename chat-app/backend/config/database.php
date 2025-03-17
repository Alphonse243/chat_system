<?php
/**
 * Classe de gestion de la connexion à la base de données
 * Implémente le pattern Singleton pour une connexion unique
 */
class Database {
    /** @var Database|null Instance unique de la classe */
    private static $instance = null;
    
    /** @var mysqli Connexion à la base de données */
    private $conn;

    /**
     * Constructeur privé pour le pattern Singleton
     * Établit la connexion à la base de données
     * @throws Exception Si la connexion échoue
     */
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Créer et sélectionner la base de données
            $this->conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            $this->conn->select_db(DB_NAME);
            
            // Définir le charset
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance unique de la classe
     * @return Database Instance unique de la classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne la connexion à la base de données
     * @return mysqli Connexion à la base de données
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Vérifie l'existence d'une table
     * @param string $tableName Nom de la table à vérifier
     * @return bool True si la table existe
     */
    public function checkTable($tableName) {
        $result = $this->conn->query("SHOW TABLES LIKE '$tableName'");
        return $result->num_rows > 0;
    }

    /**
     * Vérifie l'existence de toutes les tables requises
     * @return array Liste des tables manquantes
     */
    public function checkAllTables() {
        $requiredTables = [
            'users',
            'conversations',
            'conversation_participants',
            'messages',
            'message_status',
            'attachments',
            'user_sessions'
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (!$this->checkTable($table)) {
                $missingTables[] = $table;
            }
        }

        return $missingTables;
    }
}

// Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'chat_system');
