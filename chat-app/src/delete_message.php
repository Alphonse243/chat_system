<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/Message.php';

try {
    $db = Database::getInstance()->getConnection();
    $messageModel = new ChatApp\Models\Message($db);
    
    $messageId = intval($_POST['message_id']);
    $userId = intval($_SESSION['user_id']);

    if ($messageId <= 0) {
        throw new Exception("ID de message invalide");
    }

    // Récupérer les informations du message avant la suppression
    $messageInfo = $messageModel->getById($messageId);
    
    if ($messageInfo && $messageInfo['message_type'] === 'voice') {
        $audioFile = __DIR__ . '/' . $messageInfo['content'];
        if (file_exists($audioFile)) {
            unlink($audioFile);
        }
    }

    $success = $messageModel->delete($messageId, $userId);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Message supprimé' : 'Échec de la suppression'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
