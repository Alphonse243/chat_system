<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

class GoogleAuthController {
    private $client;
    private $config;

    public function __construct() {
        session_start();
        $this->config = require_once __DIR__ . '/../config/google-config.php';
        $this->initGoogleClient();
        
        if (isset($_POST['credential'])) {
            $this->handleGoogleSignIn($_POST['credential']);
        }
    }

    private function initGoogleClient() {
        $this->client = new Google_Client();
        $this->client->setClientId($this->config['client_id']);
        $this->client->setClientSecret($this->config['client_secret']);
        $this->client->setRedirectUri($this->config['redirect_uri']);
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    private function handleGoogleSignIn($credential) {
        try {
            $payload = $this->client->verifyIdToken($credential);
            
            if ($payload) {
                $_SESSION['user_id'] = $payload['sub'];
                $_SESSION['email'] = $payload['email'];
                $_SESSION['name'] = $payload['name'];
                $_SESSION['picture'] = $payload['picture'];

                echo json_encode([
                    'success' => true,
                    'redirect' => '/chat-system/chat-app/src/index.php'
                ]);
            } else {
                throw new Exception('Invalid token');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}

new GoogleAuthController();
