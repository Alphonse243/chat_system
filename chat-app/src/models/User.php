<?php

namespace ChatApp\Models;

require_once __DIR__ . '/BaseModel.php';

/**
 * Modèle de gestion des utilisateurs
 * Gère toutes les opérations liées aux utilisateurs (CRUD, sessions, recherche)
 */
class User extends BaseModel {
    protected $conn;
    protected $table = 'users';

    public function __construct($db) { 
        $this->conn = $db;
        if (!$this->checkTable()) {
            throw new Exception("Users table not found");
        }
    }

    /**
     * Vérifie l'existence de la table dans la base de données
     * @return bool True si la table existe, false sinon
     */
    public function checkTable() {
        $result = $this->conn->query("SHOW TABLES LIKE '{$this->table}'");
        return $result->num_rows > 0;
    }

    /**
     * Crée un nouvel utilisateur
     * @param array $userData Données de l'utilisateur (username, name, email, password, etc.)
     * @return bool Succès de la création
     */
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

    /**
     * Récupère les informations d'un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|null Données de l'utilisateur
     */
    public function getById($id) {
        $sql = "SELECT id, username, name, email, avatar_url, bio, status, last_seen 
                FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Authentifie un utilisateur avec email et mot de passe
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array|null Données de l'utilisateur si authentifié, sinon null
     */
    public function authenticate($email, $password) {
        $sql = "SELECT id, username, name, email, password, avatar_url, bio, status, last_seen 
                FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Trouve un utilisateur par son email
     * @param string $email Email de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public function findByEmail($email) {
        $sql = "SELECT id, username, name, email, password, avatar_url, bio, status, last_seen 
                FROM users WHERE email = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Database error in findByEmail: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Met à jour le statut d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $status Nouveau statut (online, offline, away, busy)
     */
    public function updateStatus($userId, $status) {
        $sql = "UPDATE users SET status = ?, last_seen = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $userId);
        return $stmt->execute();
    }

    // // Get user conversations
    // public function getConversations($userId) {
    //     $sql = "SELECT c.* FROM conversations c
    //             INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
    //             WHERE cp.user_id = ?";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("i", $userId);
    //     $stmt->execute();
    //     return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    // }

    // Get user conversations / private
    public function getConversations($userId) {
        $sql = "SELECT
            c.id as conversations_id,
            c.name as conversations_name,
            c.type as conversations_type,
            c.updated_at as conversations_updated_at,
            u.id as users_id,
            u.name as users_name,
            u.status as users_statuts,
            u.last_seen as users_last_seen,
            u.avatar_url as users_avatar_url 
        
        FROM conversations c
                INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
                 LEFT JOIN users u ON u.id = cp.user_id
                WHERE cp.user_id = ? AND c.type = 'private' LIMIT 0,5";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // // Get user conversations
    // public function getConversations($userId) {
    //     $sql = "SELECT
    //         c.id as conversations_id,
    //         c.name as conversations_name,
    //         c.type as conversations_type,
    //         c.updated_at as conversations_updated_at,
    //         u.id as users_id,
    //         u.name as users_name,
    //         u.status as users_statuts,
    //         u.last_seen as users_last_seen,
    //         u.avatar_url as users_avatar_url

    //         FROM conversations c
    //             INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
    //             LEFT JOIN users u ON u.id = cp.user_id
    //             WHERE cp.user_id = ? AND c.type = 'group'";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("i", $userId);
    //     $stmt->execute();
    //     return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    // }


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

    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET name = ?, bio = ?, avatar_url = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $data['name'], $data['bio'], $data['avatar_url'], $userId);
        return $stmt->execute();
    }

    public function searchUsers($searchTerm, $limit = 10) {
        $searchTerm = "%$searchTerm%";
        $sql = "SELECT id, username, name, avatar_url, status 
                FROM users 
                WHERE username LIKE ? OR name LIKE ? 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createSession($userId, $ipAddress, $userAgent) {
        $sessionToken = bin2hex(random_bytes(32));
        $sql = "INSERT INTO user_sessions (user_id, ip_address, user_agent, session_token) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $ipAddress, $userAgent, $sessionToken);
        return $stmt->execute() ? $sessionToken : false;
    }

    public function validateSession($userId, $sessionToken) {
        $sql = "SELECT id FROM user_sessions 
                WHERE user_id = ? AND session_token = ? 
                AND last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $userId, $sessionToken);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    protected function handleError($message) {
        error_log($message);
        throw new Exception($message);
    }
}
