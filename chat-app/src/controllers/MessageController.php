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
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }
    
    $messageModel = new Message($db);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Received POST request to MessageController.php");
        error_log("POST data: " . json_encode($_POST));
        error_log("FILES data: " . json_encode($_FILES));

        if (isset($_POST['content']) && isset($_POST['conversation_id']) && isset($_POST['message_type'])) {
            $senderId = $_SESSION['user_id'];
            $conversationId = $_POST['conversation_id'];
            $content = $_POST['content'];
            $messageType = $_POST['message_type'];

            if ($messageType === 'text') {
                $messageId = $messageModel->create($senderId, $conversationId, $content, $messageType);

                if ($messageId) {
                    // Redirect to chat page on success
                    header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&message=Message sent successfully");
                    exit;
                } else {
                    error_log("Failed to create text message in database.");
                    // Redirect with error message
                    header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=Failed to send message");
                    exit;
                }
            } elseif ($messageType === 'voice') {
                if (isset($_FILES['audio'])) {
                    $audio = $_FILES['audio'];

                    // Validate file upload
                    if ($audio['error'] === UPLOAD_ERR_OK) {
                        $tmp_name = $audio["tmp_name"];
                        $name = basename($audio["name"]);

                        // Ensure a directory for uploads exists
                        $uploadDir = __DIR__ . '/../uploads/voices/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $destination = $uploadDir . uniqid() . '_' . $name;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $messageId = $messageModel->create($senderId, $conversationId, $destination, $messageType);

                            if ($messageId) {
                                // Redirect to chat page on success
                                header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&message=Voice message sent successfully");
                                exit;
                            } else {
                                error_log("Failed to create voice message in database.");
                                // Redirect with error message
                                header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=Failed to send voice message");
                                exit;
                            }
                        } else {
                            http_response_code(500);
                            error_log("Failed to move uploaded file. Error: " . print_r(error_get_last(), true));
                            // Redirect with error message
                            header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=Failed to move uploaded file");
                            exit;
                        }
                    } else {
                        http_response_code(400);
                        error_log("File upload error: " . $audio['error']);
                        // Redirect with error message
                        header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=File upload error: " . $audio['error']);
                        exit;
                    }
                } else {
                    http_response_code(400);
                    error_log("No audio file provided.");
                    // Redirect with error message
                    header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=No audio file provided");
                    exit;
                }
            } else {
                http_response_code(400);
                error_log("Invalid message type: " . $messageType);
                // Redirect with error message
                header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=Invalid message type");
                exit;
            }
        } else {
            http_response_code(400);
            error_log("Missing parameters.");
            // Redirect with error message
            header("Location: /chat-app/chat.php?conversation_id=" . $conversationId . "&error=Missing parameters");
            exit;
        }
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
