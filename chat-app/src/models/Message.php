<?php

namespace ChatApp\Models;

// require_once __DIR__ . '/BaseModel.php'; // Removed

/**
 * Modèle de gestion des messages
 * Gère toutes les opérations CRUD liées aux messages dans le système de chat.
 */
class Message extends BaseModel {
    protected $table = 'messages';
    public $timestamps = false;

    public function __construct($db) {
        parent::__construct($db, $this->table);
    }

    /**
     * Crée un nouveau message dans la conversation
     * @param int $senderId ID de l'expéditeur
     * @param int $conversationId ID de la conversation
     * @param string $content Contenu du message
     * @param string $messageType Type de message (text, image, file, voice)
     * @param string|null $fileUrl URL du fichier si le type n'est pas 'text'
     * @return bool Succès de la création
     */
    public function create(int $senderId, int $conversationId, string $content, string $messageType = 'text', ?string $fileUrl = null)
    {
        $sql = "INSERT INTO {$this->table} (sender_id, conversation_id, content, message_type, file_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissi", $senderId, $conversationId, $content, $messageType, $fileUrl);
        $result = $stmt->execute();
        
        if ($result) {
            $messageId = $this->conn->insert_id;
            $this->createMessageStatus($messageId, $conversationId);
            return true;
        }

        return false;
    }

    /**
     * Crée les statuts initiaux pour un nouveau message
     * @param int $messageId ID du message
     * @param int $conversationId ID de la conversation
     * @return bool Succès de la création des statuts
     */
    private function createMessageStatus(int $messageId, int $conversationId)
    {
        // This logic might need adjustment based on your exact requirements
        // and the structure of your database tables.
        $sql = "INSERT INTO message_status (message_id, user_id, status) 
                SELECT ?, user_id, 'sent' 
                FROM conversation_participants 
                WHERE conversation_id = ? AND user_id != (
                    SELECT sender_id FROM messages WHERE id = ?
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $messageId, $conversationId, $messageId);
        return $stmt->execute();
    }
 
    /**
     * Marque un message comme lu pour un utilisateur spécifique
     * @param int $messageId ID du message
     * @param int $userId ID de l'utilisateur
     * @return bool Succès de la mise à jour du statut
     */
    public function markAsRead(int $messageId, int $userId)
    {
        // This logic might need adjustment based on your exact requirements
        // and the structure of your database tables.
        $sql = "UPDATE message_status 
                SET status = 'read', read_at = CURRENT_TIMESTAMP 
                WHERE message_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $messageId, $userId);
        return $stmt->execute();
    }

    /**
     * Modifie le contenu d'un message existant
     * @param int $messageId ID du message
     * @param string $content Nouveau contenu du message
     * @return bool Succès de la modification
     */
    public function edit(int $messageId, string $content)
    {
        $message = static::query()->find($messageId);

        if ($message) {
            $message->content = $content;
            $message->is_edited = true;
            return $message->save();
        }

        return false;
    }

    /**
     * Récupère les messages d'une conversation privée entre deux utilisateurs
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du deuxième utilisateur
     * @param int $limit Nombre de messages à récupérer (optionnel)
     * @param int $offset Offset pour la pagination (optionnel)
     * @return array Messages de la conversation
     */
    public function getPrivateMessages(int $userId1, int $userId2, int $limit = 50, int $offset = 0)
    {
        $sql = "SELECT m.*, u.username as sender_name, u.avatar_url 
                FROM messages m 
                INNER JOIN users u ON m.sender_id = u.id
                INNER JOIN conversations c ON m.conversation_id = c.id
                INNER JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
                INNER JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
                WHERE c.type = 'private' 
                AND cp1.user_id = ? 
                AND cp2.user_id = ?
                AND cp1.user_id != cp2.user_id
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $userId1, $userId2, $limit, $offset);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }
}