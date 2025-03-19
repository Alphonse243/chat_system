<?php

namespace ChatApp\Models;
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Conversation.php';
require_once __DIR__ . '/Message.php';
require_once __DIR__ . '/User.php';

use ChatApp\Models\Conversation;
use ChatApp\Models\Message;
use ChatApp\Models\User;

/**
 * Modèle de gestion des conversations
 * Gère les conversations privées et de groupe
 */

/**
 * Modèle de gestion des conversations
 * Gère les conversations privées et de groupe
 */
class Conversation extends BaseModel
{
    protected $table = 'conversations';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function __construct($db) {
        parent::__construct($db, $this->table);
    }

    /**
     * Crée une nouvelle conversation
     * @param array $data Données de la conversation
     * @return bool Succès de la création
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (name, type) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $data['name'], $data['type']);
        return $stmt->execute();
    }

    /**
     * Ajoute un participant à une conversation
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur à ajouter
     * @return bool Succès de l'ajout
     */
    public function addParticipant(int $conversationId, int $userId)
    {
        $sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        return $stmt->execute();
    }

    public function getParticipants(int $conversationId)
    {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN conversation_participants cp ON u.id = cp.user_id 
                WHERE cp.conversation_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Récupère les messages d'une conversation par son ID
     * @param int $conversationId ID de la conversation
     * @return array Liste des messages
     */
    public function getMessages(int $conversationId)
    {
        $sql = "SELECT m.*, u.username
                FROM messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Supprime un participant d'une conversation
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur à supprimer
     * @return bool Succès de la suppression
     */
    public function removeParticipant(int $conversationId, int $userId)
    {
        $sql = "DELETE FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        return $stmt->execute();
    }

    public function getOtherParticipant($conversationId, $currentUserId) {
        $sql = "SELECT u.username, u.id
                FROM users u
                JOIN conversation_participants cp ON u.id = cp.user_id
                WHERE cp.conversation_id = ? AND u.id != ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}