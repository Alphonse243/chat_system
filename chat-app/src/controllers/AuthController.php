<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            return;
        }

        $userData = $this->user->findByEmail($email);
        
        if (!$userData || !password_verify($password, $userData['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            return;
        }

        // Start session and store user data
        session_start();
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['username'] = $userData['username'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/chat-system/chat-app/src/index.php'
        ]);
    }
}

// Handle the request
$authController = new AuthController();
$authController->login();
