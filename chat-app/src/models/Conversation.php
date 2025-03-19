<?php

namespace ChatApp\Models;

require_once __DIR__ . '/BaseModel.php';

/**
 * Modèle de gestion des conversations
 * Gère les conversations privées et de groupe
 */
class Conversation extends BaseModel {
    protected $conn;
    protected $table = 'conversations';

    public function __construct($db) { 
        $this->conn = $db;
        if (!$this->checkTable()) {
            throw new Exception("conversations table not found");
        }
    }

    
    public function checkTable() {
        $result = $this->conn->query("SHOW TABLES LIKE '{$this->table}'");
        return $result->num_rows > 0;
    }


    /**
     * Crée une nouvelle conversation
     * @param string|null $name Nom de la conversation (pour les groupes)
     * @param string $type Type de conversation ('private' ou 'group')
     * @return int|false ID de la conversation ou false si échec
     */
    public function create($name = null, $type = 'private') {
        $sql = "INSERT INTO conversations (name, type) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $name, $type);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    /**
     * Ajoute un participant à une conversation
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @param string $role Rôle du participant ('admin' ou 'member')
     */
    public function addParticipant($conversationId, $userId, $role = 'member') {
        $sql = "INSERT INTO conversation_participants (conversation_id, user_id, role) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $conversationId, $userId, $role);
        return $stmt->execute();
    }

    public function getParticipants($conversationId) {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN conversation_participants cp ON u.id = cp.user_id 
                WHERE cp.conversation_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getMessages($conversationId, $limit = 50, $offset = 0) {
        $sql = "SELECT m.*, u.username, u.avatar_url FROM messages m 
                INNER JOIN users u ON m.sender_id = u.id 
                WHERE m.conversation_id = ? 
                ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $conversationId, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
