<?php
/**
 * Modèle de gestion des messages
 * 
 * Cette classe gère toutes les opérations CRUD liées aux messages dans le système de chat.
 * 
 * Fonctionnalités principales:
 * 1. Création de nouveaux messages avec support multi-format
 * 2. Gestion des statuts de lecture
 * 3. Modification des messages existants
 * 
 * Structure des messages:
 * - sender_id: ID de l'expéditeur
 * - conversation_id: ID de la conversation
 * - content: Contenu du message
 * - message_type: Type de message (text, image, file, voice)
 * - file_url: Lien vers le fichier si applicable
 * - is_edited: Indicateur si le message a été modifié
 * 
 * Gestion des statuts:
 * - Les statuts sont automatiquement créés pour tous les participants
 * - Le statut initial est 'sent'
 * - Les statuts peuvent évoluer vers 'delivered' et 'read'
 */
class Message extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'messages');
        if (!$this->checkTable()) {
            $this->handleError("Messages table not found");
        }
    }

    /**
     * Crée un nouveau message dans la conversation
     * 
     * @param int $senderId ID de l'expéditeur
     * @param int $conversationId ID de la conversation
     * @param string $content Contenu du message
     * @param string $messageType Type de message (text, image, file, voice)
     * @param string|null $fileUrl URL du fichier si le type n'est pas 'text'
     * 
     * Processus:
     * 1. Insertion du message dans la table messages
     * 2. Création automatique des statuts pour tous les participants
     * 3. Exclusion du créateur du message des statuts
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

    /**
     * Crée les statuts initiaux pour un nouveau message
     * 
     * Crée automatiquement des entrées 'sent' pour tous les participants
     * de la conversation, sauf l'expéditeur du message
     */
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

    /**
     * Marque un message comme lu pour un utilisateur spécifique
     * Met à jour le statut en 'read' et enregistre la date/heure de lecture
     */
    public function markAsRead($messageId, $userId) {
        $sql = "UPDATE message_status 
                SET status = 'read', read_at = CURRENT_TIMESTAMP 
                WHERE message_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $messageId, $userId);
        return $stmt->execute();
    }

    /**
     * Modifie le contenu d'un message existant
     * Met à jour le contenu et marque le message comme édité
     */
    public function edit($messageId, $content) {
        $sql = "UPDATE messages SET content = ?, is_edited = TRUE 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $content, $messageId);
        return $stmt->execute();
    }
}
