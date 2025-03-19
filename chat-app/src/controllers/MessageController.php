<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

session_start();
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../../backend/config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }
    

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => '/chat-system/chat-app/src/index.php'
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
