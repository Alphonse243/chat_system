<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

session_start();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../backend/config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        throw new Exception('Database connection failed');
    }
    
    $user = new User($db);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    $userData = $user->findByEmail($email);
    
    if (!$userData || !password_verify($password, $userData['password'])) {
        throw new Exception('Invalid credentials');
    }

    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['username'] = $userData['username'];

    $sessionToken = $user->createSession(
        $userData['id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    );

    if ($sessionToken) {
        setcookie('session_token', $sessionToken, time() + 86400, '/');
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
