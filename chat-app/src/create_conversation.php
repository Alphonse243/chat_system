<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';

use ChatApp\Models\User;

$db = Database::getInstance()->getConnection();
$userModel = new User($db);

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $otherUserId = (int)$_GET['user_id'];
    $currentUserId = (int)$_SESSION['user_id'];

    // Prevent creating conversation with yourself
    if ($otherUserId === $currentUserId) {
        header('Location: index.php');
        exit;
    } 

    // Create or get private conversation
    $conversationId = $userModel->createPrivateConversation($currentUserId, $otherUserId);

    // Redirect to the conversation page
    header('Location: conversation.php?conversationId=' . $conversationId);
    exit;
} else {
    // Invalid user ID, redirect to index
    header('Location: index.php');
    exit;
}
?>
