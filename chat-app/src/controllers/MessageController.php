<?php

namespace ChatApp\Controllers;

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

session_start();
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../../backend/config/database.php';

use ChatApp\Models\Message;
use Database;
use Exception;

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Session non valide');
    }

    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }
    
    $messageModel = new Message($db);

    // Correction : Vérification de la méthode POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Received POST request to MessageController.php");
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if (empty($_POST['conversation_id'])) {
            throw new Exception('Missing conversation ID');
        }

        $senderId = $_SESSION['user_id'];
        $conversationId = $_POST['conversation_id'];
        $messageType = $_POST['message_type'] ?? 'text';
        
        // Gestion des messages texte
        if ($messageType === 'text' && isset($_POST['content'])) {
            $content = trim($_POST['content']);
            if (!empty($content)) {
                $messageId = $messageModel->create($senderId, $conversationId, $content, $messageType);
                if ($messageId) {
                    header("Location: ../conversation.php?conversationId={$conversationId}");
                    exit;
                }
            }
        }
        // Gestion des messages vocaux
        elseif ($messageType === 'voice' && isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            try {
                $audio = $_FILES['audio'];
                $baseUploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'voices';
                
                if (!file_exists($baseUploadDir)) {
                    if (!mkdir($baseUploadDir, 0777, true)) {
                        throw new Exception('Impossible de créer le dossier uploads');
                    }
                }

                $fileName = uniqid('voice_') . '.webm';
                $destination = $baseUploadDir . DIRECTORY_SEPARATOR . $fileName;

                if (!move_uploaded_file($audio['tmp_name'], $destination)) {
                    throw new Exception('Échec du téléchargement du fichier audio');
                }

                // Chemin relatif pour la base de données
                $relativePath = 'uploads/voices/' . $fileName;
                $messageId = $messageModel->create($senderId, $conversationId, $relativePath, $messageType);
                
                if (!$messageId) {
                    throw new Exception('Échec de la création du message');
                }

                header("Location: ../conversation.php?conversationId={$conversationId}");
                exit;
            } catch (Exception $e) {
                error_log("Erreur audio: " . $e->getMessage());
                throw $e;
            }
        }

        // En cas d'échec silencieux
        header("Location: ../conversation.php?conversationId={$conversationId}&error=message_failed");
        exit;
    }

} catch (Exception $e) {
    error_log("Error in MessageController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $conversationId = $_POST['conversation_id'] ?? '';
    header("Location: ../conversation.php?conversationId={$conversationId}&error=" . urlencode($e->getMessage()));
    exit;
}
