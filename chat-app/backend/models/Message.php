<?php
/**
 * Modèle de gestion des messages
 * Gère l'envoi, la lecture et la modification des messages
 */
class Message extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'messages');
        if (!$this->checkTable()) {
            $this->handleError("Messages table not found");
        }
    }

    /**
     * Crée un nouveau message
     * @param int $senderId ID de l'expéditeur
     * @param int $conversationId ID de la conversation
     * @param string $content Contenu du message
     * @param string $messageType Type de message (text, image, file, voice)
     * @param string|null $fileUrl URL du fichier attaché
     * @return int|false ID du message ou false si échec
     */
    public function create($senderId, $conversationId, $content, $messageType = 'text', $fileUrl = null) {
        $sql = "INSERT INTO messages (sender_id, conversation_id, content, message_type, file_url) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisss", $senderId, $conversationId, $content, $messageType, $fileUrl);
        if($stmt->execute()) {
            $messageId = $this->conn->insert_id;
            $this->createMessageStatus($messageId, $conversationId);
            return $messageId;
        }
        return false;
    }

    private function createMessageStatus($messageId, $conversationId) {
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

    public function markAsRead($messageId, $userId) {
        $sql = "UPDATE message_status 
                SET status = 'read', read_at = CURRENT_TIMESTAMP 
                WHERE message_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $messageId, $userId);
        return $stmt->execute();
    }

    public function edit($messageId, $content) {
        $sql = "UPDATE messages SET content = ?, is_edited = TRUE 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $content, $messageId);
        return $stmt->execute();
    }
}
