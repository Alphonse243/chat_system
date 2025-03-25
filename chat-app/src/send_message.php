<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/Message.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Conversation.php';

try {
    $db = Database::getInstance()->getConnection();
    $messageModel = new ChatApp\Models\Message($db);
    
    $conversationId = intval($_POST['conversation_id']);
    $messageType = $_POST['message_type'];
    $senderId = intval($_SESSION['user_id']);
    $content = '';

    // Gestion des messages vocaux
    if ($messageType === 'voice' && isset($_FILES['audio'])) {
        $uploadDir = 'uploads/audio/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '_voice.webm';
        $uploadFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadFile)) {
            $content = $uploadFile;
        } else {
            throw new Exception("Erreur lors de l'upload du fichier audio");
        }
    } else {
        $content = trim($_POST['content']);
    }

    if (empty($content) || $conversationId <= 0) {
        throw new Exception("Données invalides");
    }

    $success = $messageModel->create([
        'conversation_id' => $conversationId,
        'sender_id' => $senderId,
        'content' => $content,
        'message_type' => $messageType
    ]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Message envoyé' : 'Échec de l\'envoi',
        'audioUrl' => $messageType === 'voice' ? $content : null
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
