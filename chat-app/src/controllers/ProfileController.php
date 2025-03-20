<?php

namespace ChatApp\Controllers;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../backend/config/database.php';

use ChatApp\Models\User;
use Exception;

class ProfileController {
    private $userModel;

    public function __construct() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit;
        }

        $db = \Database::getInstance()->getConnection();
        $this->userModel = new User($db);
    }

    public function handleProfileUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'name' => $_POST['name'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'status' => $_POST['status'] ?? 'online'
            ];

            try {
                if ($this->userModel->updateProfile($_SESSION['user_id'], $updateData)) {
                    $_SESSION['success_message'] = 'Profile updated successfully';
                } else {
                    $_SESSION['error_message'] = 'Failed to update profile';
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = 'An error occurred while updating the profile';
                error_log($e->getMessage());
            }
        }

        header('Location: ../profile.php');
        exit;
    }
}

// Instantiate controller and handle request
$controller = new ProfileController();
$controller->handleProfileUpdate();
