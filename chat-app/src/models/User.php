<?php

namespace ChatApp\Models;

require_once __DIR__ . '/BaseModel.php'; // Add this line

/**
 * Modèle de gestion des utilisateurs
 * Gère toutes les opérations liées aux utilisateurs (CRUD, sessions, recherche)
 */
class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function __construct($db) {
        parent::__construct($db, $this->table);
    }

    /**
     * Crée un nouvel utilisateur
     * @param array $userData Données de l'utilisateur (username, name, email, password, avatar_url, bio, status)
     * @return bool Succès de la création
     */
    public function create(array $userData)
    {
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO {$this->table} (username, name, email, password, avatar_url, bio, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", $userData['username'], $userData['name'], $userData['email'], $userData['password'], $userData['avatar_url'], $userData['bio'], $userData['status']);
        return $stmt->execute();
    }

    /**
     * Récupère les informations d'un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|null Données de l'utilisateur
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Authentifie un utilisateur avec email et mot de passe
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array|null Données de l'utilisateur si authentifié, sinon null
     */
    public function authenticate(string $email, string $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
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
    public function findByEmail(string $email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Met à jour le statut d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $status Nouveau statut (online, offline, away, busy)
     */
    public function updateStatus(int $userId, string $status)
    {
        $sql = "UPDATE {$this->table} SET status = ?, last_seen = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $userId);
        return $stmt->execute();
    }

    /**
     * Get user conversations / private
     */
    public function getConversations(int $userId)
    {
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

    // Send message
    public function sendMessage(int $senderId, int $conversationId, string $content, string $messageType = 'text')
    {
        $sql = "INSERT INTO messages (sender_id, conversation_id, content, message_type) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $senderId, $conversationId, $content, $messageType);
        return $stmt->execute();
    }

    // Get unread messages
    public function getUnreadMessages(int $userId)
    {
        $sql = "SELECT m.* FROM messages m
                INNER JOIN message_status ms ON m.id = ms.message_id
                WHERE ms.user_id = ? AND ms.status = 'sent'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Create or get private conversation
    public function createPrivateConversation(int $userId1, int $userId2)
    {
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

    public function updateProfile(int $userId, array $data)
    {
        $sql = "UPDATE users SET name = ?, bio = ?, avatar_url = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $data['name'], $data['bio'], $data['avatar_url'], $userId);
        return $stmt->execute();
    }

    public function searchUsers(string $searchTerm, int $limit = 10)
    {
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

    public function createSession(int $userId, string $ipAddress, string $userAgent)
    {
        $sessionToken = bin2hex(random_bytes(32));
        $sql = "INSERT INTO user_sessions (user_id, ip_address, user_agent, session_token) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $ipAddress, $userAgent, $sessionToken);
        return $stmt->execute() ? $sessionToken : false;
    }

    public function validateSession(int $userId, string $sessionToken)
    {
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

    /**
     * Define the relationship with conversations.
     * This assumes you have a 'conversations' table and a foreign key relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_id');
    }
}