<?php
/**
 * Classe de connexion à la base de données utilisant le pattern Singleton
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=localhost;dbname=chat_system;charset=utf8mb4",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $this->initTables();
        } catch(PDOException $e) {
            die("Connexion échouée : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function initTables() {
        // Suppression des tables dans l'ordre correct
        $this->connection->exec("DROP TABLE IF EXISTS user_sessions");
        $this->connection->exec("DROP TABLE IF EXISTS attachments");
        $this->connection->exec("DROP TABLE IF EXISTS message_status");
        $this->connection->exec("DROP TABLE IF EXISTS messages");
        $this->connection->exec("DROP TABLE IF EXISTS conversation_participants");
        $this->connection->exec("DROP TABLE IF EXISTS conversations");
        $this->connection->exec("DROP TABLE IF EXISTS users");

        try {
            // Lire le fichier SQL
            $sql = file_get_contents(__DIR__ . '/../chat-app/backend/init.sql');
            
            if ($sql === false) {
                throw new Exception("Impossible de lire le fichier init.sql");
            }
            
            // Extraire les instructions de création de tables et d'index
            preg_match_all('/CREATE TABLE.*?;/s', $sql, $createTables);
            preg_match_all('/ALTER TABLE.*?;/s', $sql, $alterTables);
            
            // Exécuter les créations de tables
            foreach ($createTables[0] as $statement) {
                try {
                    $this->connection->exec($statement);
                } catch (PDOException $e) {
                    echo "Erreur lors de la création d'une table: " . $e->getMessage() . "\n";
                    echo "Requête: " . $statement . "\n";
                    throw $e;
                }
            }
            
            // Exécuter les modifications de tables
            foreach ($alterTables[0] as $statement) {
                $this->connection->exec($statement);
            }

            // Extraire et exécuter les triggers
            preg_match_all('/CREATE TRIGGER(.*?)END(?=\/\/)/s', $sql, $triggers);
            foreach ($triggers[0] as $trigger) {
                $trigger = trim($trigger);
                if (!empty($trigger)) {
                    $this->connection->exec($trigger);
                }
            }
            
        } catch (Exception $e) {
            echo "Erreur d'initialisation: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
