-- Users table - Stores user information and authentication details
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for user',
    username VARCHAR(50) NOT NULL COMMENT 'Unique username for user',
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

// Add indexes on users table
ALTER TABLE users
    ADD INDEX idx_user_email (email),
    ADD INDEX idx_user_status (status),
    ADD INDEX idx_user_username (username);

-- Conversations table - Manages chat conversations (private or group)
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for conversation',
    name VARCHAR(255) COMMENT 'Name of group conversation (NULL for private chats)',
    type ENUM('private', 'group') DEFAULT 'private' COMMENT 'Type of conversation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Conversation creation time',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last message time'
);

// Add indexes on conversations table
ALTER TABLE conversations
    ADD INDEX idx_conversation_type (type),
    ADD INDEX idx_conversation_updated (updated_at);

-- Conversation participants table - Links users to conversations
CREATE TABLE IF NOT EXISTS conversation_participants (
    conversation_id INT COMMENT 'Reference to conversation',
    user_id INT COMMENT 'Reference to participant user',
    role ENUM('admin', 'member') DEFAULT 'member' COMMENT 'User role in conversation',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When user joined conversation',
    PRIMARY KEY (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

// Add indexes on conversation_participants table
ALTER TABLE conversation_participants
    ADD INDEX idx_participant_user (user_id),
    ADD INDEX idx_participant_conversation (conversation_id);

-- Messages table - Stores all chat messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for message',
    conversation_id INT COMMENT 'Reference to conversation',
    sender_id INT COMMENT 'Reference to sender user',
    content TEXT NOT NULL COMMENT 'Message content',
    message_type ENUM('text', 'image', 'file', 'voice') DEFAULT 'text' COMMENT 'Type of message',
    file_url VARCHAR(255) COMMENT 'URL for attached file if any',
    is_edited BOOLEAN DEFAULT FALSE COMMENT 'Whether message has been edited',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Message sent time',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last edit time',
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
);

// Add indexes on messages table
ALTER TABLE messages
    ADD INDEX idx_message_conversation (conversation_id),
    ADD INDEX idx_message_sender (sender_id),
    ADD INDEX idx_message_created (created_at);

// Add additional constraints
ALTER TABLE messages
    ADD CONSTRAINT fk_message_conversation
    FOREIGN KEY (conversation_id) 
    REFERENCES conversations(id) 
    ON DELETE CASCADE;

-- Message status table - Tracks message delivery and read status
CREATE TABLE IF NOT EXISTS message_status (
    message_id INT COMMENT 'Reference to message',
    user_id INT COMMENT 'Reference to recipient user',
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent' COMMENT 'Current message status',
    read_at TIMESTAMP NULL COMMENT 'When message was read',
    PRIMARY KEY (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

// Add indexes on message_status table
ALTER TABLE message_status
    ADD INDEX idx_status_message (message_id),
    ADD INDEX idx_status_user (user_id),
    ADD INDEX idx_status_read (read_at);

-- Attachments table - Manages files shared in messages
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

// Add indexes on attachments table
ALTER TABLE attachments
    ADD INDEX idx_attachment_message (message_id),
    ADD INDEX idx_attachment_type (file_type);

ALTER TABLE conversation_participants
    ADD CONSTRAINT chk_unique_private_conversation
    CHECK (
        (SELECT COUNT(*) 
         FROM conversation_participants cp2 
         WHERE cp2.conversation_id = conversation_id) <= 2
        OR
        (SELECT type FROM conversations c 
         WHERE c.id = conversation_id) = 'group'
    );
