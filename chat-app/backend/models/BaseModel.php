<?php
/**
 * Classe de base abstraite pour tous les modèles
 * Fournit les fonctionnalités communes à tous les modèles
 */
abstract class BaseModel {
    /** @var mysqli Connection à la base de données */
    protected $conn;
    
    /** @var string Nom de la table associée au modèle */
    protected $tableName;

    /**
     * Constructeur du modèle de base
     * @param mysqli $db Connection à la base de données
     * @param string $tableName Nom de la table
     */
    public function __construct($db, $tableName) {
        $this->conn = $db;
        $this->tableName = $tableName;
    }

    /**
     * Vérifie l'existence de la table dans la base de données
     * @return bool True si la table existe, false sinon
     */
    public function checkTable() {
        $result = $this->conn->query("SHOW TABLES LIKE '{$this->tableName}'");
        return $result->num_rows > 0;
    }

    /**
     * Gestion des erreurs spécifiques aux tables
     * @param string $message Message d'erreur
     * @throws Exception
     */
    protected function handleError($message) {
        throw new Exception("Table {$this->tableName}: " . $message);
    }
}
