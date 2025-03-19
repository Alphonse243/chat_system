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
    protected $conn;
    protected $table = 'messages';

    public function __construct($db) {
        parent::__construct($db, 'messages');
        if (!$this->checkTable()) {
            $this->handleError("Messages table not found");
        }
    }

    protected function checkTable() {
        $result = $this->conn->query("SHOW TABLES LIKE '{$this->table}'");
        return $result->num_rows > 0;
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

    /**
     * Récupère les messages d'une conversation privée entre deux utilisateurs
     * 
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du deuxième utilisateur
     * @param int $limit Nombre de messages à récupérer (optionnel)
     * @param int $offset Offset pour la pagination (optionnel)
     * @return array Messages de la conversation
     */
    public function getPrivateMessages($userId1, $userId2, $limit = 50, $offset = 0) {
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
