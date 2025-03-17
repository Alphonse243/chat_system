-- =================================================================
-- Schéma de Base de Données du Système de Chat
-- Ce script crée la structure de base de données pour une application de chat
-- incluant la gestion des utilisateurs, conversations, messages et partage de fichiers
-- =================================================================

-- Table des utilisateurs
-- Stocke toutes les informations relatives aux utilisateurs du système
-- Gère l'authentification et le profil des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for user',
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique username for user',
    name VARCHAR(255) NOT NULL COMMENT 'Full name of user',
    email VARCHAR(255) UNIQUE NOT NULL COMMENT 'User email address for authentication',
    password VARCHAR(255) NOT NULL COMMENT 'Encrypted password',
    avatar_url VARCHAR(255) COMMENT 'URL to user profile picture',
    bio TEXT COMMENT 'User biography or description',
    status ENUM('online', 'offline', 'away', 'busy') DEFAULT 'offline' COMMENT 'Current user status',
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Last time user was active',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether user account is active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp'
);

-- Ajout des index sur la table utilisateurs
-- Ces index optimisent les recherches fréquentes:
-- - Recherche par email pour la connexion
-- - Filtrage par statut pour voir les utilisateurs en ligne
-- - Recherche par nom d'utilisateur pour les mentions
ALTER TABLE users
    ADD INDEX idx_user_email (email),
    ADD INDEX idx_user_status (status),
    ADD INDEX idx_user_username (username);

-- Table des conversations
-- Gère deux types de conversations:
-- 1. Privées (entre deux utilisateurs)
-- 2. Groupes (plusieurs participants)
-- Le champ 'name' est NULL pour les conversations privées
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for conversation',
    name VARCHAR(255) COMMENT 'Name of group conversation (NULL for private chats)',
    type ENUM('private', 'group') DEFAULT 'private' COMMENT 'Type of conversation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Conversation creation time',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last message time'
);

-- Add indexes on conversations table
ALTER TABLE conversations
    ADD INDEX idx_conversation_type (type),
    ADD INDEX idx_conversation_updated (updated_at);

-- Table des participants aux conversations
-- Table de liaison entre utilisateurs et conversations
-- Permet de:
-- - Savoir qui participe à quelle conversation
-- - Gérer les rôles (admin/membre) dans les groupes
-- - Tracer quand un utilisateur a rejoint une conversation
CREATE TABLE IF NOT EXISTS conversation_participants (
    conversation_id INT COMMENT 'Reference to conversation',
    user_id INT COMMENT 'Reference to participant user',
    role ENUM('admin', 'member') DEFAULT 'member' COMMENT 'User role in conversation',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When user joined conversation',
    PRIMARY KEY (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes on conversation_participants table
ALTER TABLE conversation_participants
    ADD INDEX idx_participant_user (user_id),
    ADD INDEX idx_participant_conversation (conversation_id);

-- Table des messages
-- Stocke tous les messages échangés
-- Supporte différents types de messages (texte, image, fichier, vocal)
-- Garde une trace des modifications avec is_edited et updated_at
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for message',
    conversation_id INT NOT NULL COMMENT 'Reference to conversation',
    sender_id INT NOT NULL COMMENT 'Reference to sender user',
    content TEXT NOT NULL COMMENT 'Message content',
    message_type ENUM('text', 'image', 'file', 'voice') DEFAULT 'text' COMMENT 'Type of message',
    file_url VARCHAR(255) COMMENT 'URL for attached file if any',
    is_edited BOOLEAN DEFAULT FALSE COMMENT 'Whether message has been edited',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Message sent time',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last edit time',
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Add indexes on messages table
ALTER TABLE messages
    ADD INDEX idx_message_conversation (conversation_id),
    ADD INDEX idx_message_sender (sender_id),
    ADD INDEX idx_message_created (created_at);

-- Table des statuts des messages
-- Permet de suivre l'état de chaque message pour chaque destinataire
-- États possibles: envoyé, livré, lu
-- Stocke le moment où le message a été lu
CREATE TABLE IF NOT EXISTS message_status (
    message_id INT NOT NULL COMMENT 'Reference to message',
    user_id INT NOT NULL COMMENT 'Reference to recipient user',
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent' COMMENT 'Current message status',
    read_at TIMESTAMP NULL COMMENT 'When message was read',
    PRIMARY KEY (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes on message_status table
ALTER TABLE message_status
    ADD INDEX idx_status_message (message_id),
    ADD INDEX idx_status_user (user_id),
    ADD INDEX idx_status_read (read_at);

-- Table des pièces jointes
-- Gère tous les fichiers partagés dans les messages
-- Stocke les métadonnées des fichiers (nom, type, taille)
-- Garde l'URL d'accès au fichier sur le serveur
CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for attachment',
    message_id INT COMMENT 'Reference to parent message',
    file_name VARCHAR(255) NOT NULL COMMENT 'Original file name',
    file_type VARCHAR(50) NOT NULL COMMENT 'MIME type of file',
    file_size INT NOT NULL COMMENT 'File size in bytes',
    file_url VARCHAR(255) NOT NULL COMMENT 'URL to access file',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Upload timestamp',
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
);

-- Add indexes on attachments table
ALTER TABLE attachments
    ADD INDEX idx_attachment_message (message_id),
    ADD INDEX idx_attachment_type (file_type);

-- Table des sessions utilisateurs
-- Sécurité et traçabilité des connexions
-- Permet de:
-- - Suivre les connexions des utilisateurs
-- - Détecter les activités suspectes
-- - Gérer les sessions multiples sur différents appareils
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for session',
    user_id INT NOT NULL COMMENT 'Reference to user',
    ip_address VARCHAR(45) NOT NULL COMMENT 'User IP address (IPv4 or IPv6)',
    user_agent VARCHAR(255) COMMENT 'User browser/app information',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last activity timestamp',
    session_token VARCHAR(255) NOT NULL COMMENT 'Unique session identifier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Session start timestamp',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes on user_sessions table
ALTER TABLE user_sessions
    ADD INDEX idx_session_user (user_id),
    ADD INDEX idx_session_ip (ip_address),
    ADD INDEX idx_session_token (session_token),
    ADD INDEX idx_session_activity (last_activity);

-- ================================================================
-- Triggers de Gestion des Conversations
-- Ces déclencheurs assurent que:
-- 1. Les conversations privées restent limitées à 2 participants
-- 2. Les conversations de groupe peuvent avoir plus de participants
-- ================================================================

-- Trigger avant insertion d'un participant
-- Vérifie si la conversation est privée
-- Si oui, empêche l'ajout d'un 3ème participant
CREATE TRIGGER before_participant_insert 
BEFORE INSERT ON conversation_participants
FOR EACH ROW
BEGIN
    DECLARE participant_count INT;
    DECLARE conv_type VARCHAR(10);
    
    SELECT type INTO conv_type 
    FROM conversations 
    WHERE id = NEW.conversation_id;
    
    IF conv_type = 'private' THEN
        SELECT COUNT(*) INTO participant_count
        FROM conversation_participants
        WHERE conversation_id = NEW.conversation_id;
        
        IF participant_count >= 2 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Private conversations cannot have more than 2 participants';
        END IF;
    END IF;
END//

-- Trigger avant mise à jour d'un participant
-- Même logique que pour l'insertion
-- Empêche la modification qui ajouterait un 3ème participant
CREATE TRIGGER before_participant_update
BEFORE UPDATE ON conversation_participants
FOR EACH ROW
BEGIN
    DECLARE participant_count INT;
    DECLARE conv_type VARCHAR(10);
    
    SELECT type INTO conv_type 
    FROM conversations 
    WHERE id = NEW.conversation_id;
    
    IF conv_type = 'private' THEN
        SELECT COUNT(*) INTO participant_count
        FROM conversation_participants
        WHERE conversation_id = NEW.conversation_id;
        
        IF participant_count >= 2 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Private conversations cannot have more than 2 participants';
        END IF;
    END IF;
END//

-- =================================================================
-- Notes sur les Index:
-- 1. Index primaires sur tous les champs ID
-- 2. Index sur les clés étrangères pour optimiser les jointures
-- 3. Index supplémentaires pour les recherches courantes
-- 4. Index sur les champs de statut et type pour le filtrage
-- =================================================================

DELIMITER ;
