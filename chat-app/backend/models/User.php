<?php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user with all fields
    public function create($userData) {
        $sql = "INSERT INTO users (username, name, email, password, avatar_url, bio) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", 
            $userData['username'], 
            $userData['name'], 
            $userData['email'], 
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['avatar_url'],
            $userData['bio']
        );
        return $stmt->execute();
    }

    // Get user by ID
    public function getById($id) {
        $sql = "SELECT id, username, name, email, avatar_url, bio, status, last_seen 
                FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update user status
    public function updateStatus($userId, $status) {
        $sql = "UPDATE users SET status = ?, last_seen = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $userId);
        return $stmt->execute();
    }

    // Get user conversations
    public function getConversations($userId) {
        $sql = "SELECT c.* FROM conversations c
                INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
                WHERE cp.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Send message
    public function sendMessage($senderId, $conversationId, $content, $messageType = 'text') {
        $sql = "INSERT INTO messages (sender_id, conversation_id, content, message_type) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $senderId, $conversationId, $content, $messageType);
        return $stmt->execute();
    }

    // Get unread messages
    public function getUnreadMessages($userId) {
        $sql = "SELECT m.* FROM messages m
                INNER JOIN message_status ms ON m.id = ms.message_id
                WHERE ms.user_id = ? AND ms.status = 'sent'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Create or get private conversation
    public function createPrivateConversation($userId1, $userId2) {
        // First check if conversation exists
        $sql = "SELECT c.id FROM conversations c
                INNER JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
                INNER JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
                WHERE c.type = 'private' 
                AND cp1.user_id = ? AND cp2.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId1, $userId2);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            return $result['id'];
        }

        // Create new conversation if doesn't exist
        $this->conn->begin_transaction();
        try {
            $sql = "INSERT INTO conversations (type) VALUES ('private')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $conversationId = $this->conn->insert_id;

            $sql = "INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $conversationId, $userId1);
            $stmt->execute();
            $stmt->bind_param("ii", $conversationId, $userId2);
            $stmt->execute();

            $this->conn->commit();
            return $conversationId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
