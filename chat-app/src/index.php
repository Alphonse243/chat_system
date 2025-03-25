<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Message.php';
require_once __DIR__ . '/models/Conversation.php';
use ChatApp\Models\User;
use ChatApp\Models\Message;
use ChatApp\Models\Conversation;
use ChatApp\Controllers\NavigationController;

// Récupérer les informations de l'utilisateur
$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$messageModel = new Message($db);
$conversationModel = new Conversation($db);
$currentUser = $userModel->getById($_SESSION['user_id']);
$getConversations = $userModel->getConversations($_SESSION['user_id']);
    
if (!$currentUser) {
    session_destroy(); 
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipientId = $_POST['recipient_id'];

    // Create a private conversation
    $conversationId = $userModel->createPrivateConversation($_SESSION['user_id'], $recipientId);

    // Send a default message
    $messageContent = "Hello, this is the first message!";
    $messageModel->create($_SESSION['user_id'], $conversationId, $messageContent);

    // Redirect to the conversation page
    header("Location: conversation.php?conversationId=" . $conversationId);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Chat Application</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .avatar-container {
            position: relative;
            display: inline-block;
        }
        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .status-online {
            background-color:rgb(0, 138, 32);
        }
        .status-offline {
            background-color: #ccc;
        }
    </style>
</head>
<body style="background-color: #f0f2f5;">
    <?php
    require_once __DIR__ . '/controllers/NavigationController.php';
    $navController = new NavigationController();
    $translator = $navController->getTranslator(); 
    $navController->renderNavbar();
    ?>

    <div class="container-fluid py-3">
        <div class="row g-3">
            <!-- Users Online Section -->
            <div class="col-md-3">
                <div class="card rounded-3 border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 text-primary fw-bold" data-i18n="conversation"><?= $translator->translate('Conversations') ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="current-user mb-3 p-3 border-bottom ">
                            
                                <div class="d-flex align-items-center">
                                    <a href="profile.php">
                                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($currentUser['username']) ?>" 
                                            class="avatar me-2" 
                                            alt="<?= htmlspecialchars($currentUser['username']) ?>">
                                    </a>
                                    <div class="" >
                                        <div class="fw-bold"><?= htmlspecialchars($currentUser['username']) ?></div>
                                        <small class="text-muted  "><?= htmlspecialchars($currentUser['email']) ?></small>
                                    </div>
                                </div>
                               
                            
                        </div>
                        <ul id="online-users" class="list-group list-group-flush current-user mb-3 p-3 border-bottom">
                            <!-- La liste des conversation privée sera générée dynamiquement -->
                            <?php
                                foreach($getConversations as $item){
                                    // Récupérer l'autre participant de la conversation
                                    $otherParticipant = $conversationModel->getOtherParticipant($item['conversations_id'], $_SESSION['user_id']);
                                    $participantName = $otherParticipant ? htmlspecialchars($otherParticipant['username']) : 'Conversation';
                                    $lastMessage = $conversationModel->getLastMessage($item['conversations_id']);
                                    
                                    // Formater le message selon son type
                                    $messagePreview = '';
                                    if ($lastMessage) {
                                        switch($lastMessage['message_type']) {
                                            case 'image':
                                                $messagePreview = '📷 Photo';
                                                break;
                                            case 'file':
                                                $messagePreview = '📎 Fichier';
                                                break;
                                            case 'voice':
                                                $messagePreview = '🎤 Message vocal';
                                                break;
                                            default:
                                                $messagePreview = htmlspecialchars(substr($lastMessage['content'], 0, 30)) . (strlen($lastMessage['content']) > 30 ? '...' : '');
                                        }
                                    }
                                    ?>  
                                    <a href="conversation.php?conversationId=<?= htmlspecialchars($item['conversations_id']) ?>">
                                        <div class="btn btn-primary d-flex align-items-center mb-3">
                                            <div class="avatar-container">
                                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($participantName) ?>" 
                                                    class="avatar me-2" 
                                                    alt="<?= $participantName ?>">
                                                <span class="status-indicator <?= $otherParticipant['status'] === 'online' ? 'status-online' : 'status-offline' ?>"></span>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-white d-flex"><?= $participantName ?></div>
                                                <small class="text-white"><?= $messagePreview ?: 'Aucun message' ?></small>
                                            </div>
                                        </div>
                                    </a>
                                    <?php
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-8">
                <div class="card rounded-3 border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 text-primary fw-bold" data-i18n="contacts">Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul id="online-users" class="list-group list-group-flush current-user mb-3 p-3 border-bottom">
                            <!-- La liste des utilisateur  sera générée dynamiquement -->
                            <?php
                                $users = $userModel->getAllUsers();
                                foreach($users as $user){
                                    if ($user['id'] != $_SESSION['user_id']) { // Ne pas afficher l'utilisateur actuel
                                    ?>       
                                        <div class="btn btn-primary d-flex align-items-center mb-3">
                                            <div class="avatar-container">
                                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($user['username']) ?>" 
                                                    class="avatar me-2" 
                                                    alt="<?= htmlspecialchars($user['username']) ?>">
                                                <span class="status-indicator <?= $user['status'] === 'online' ? 'status-online' : 'status-offline' ?>"></span>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-white d-flex "><?= $user['username'] ?>  </div>
                                                <small class="text-white"><?= $user['email'] ?></small>
                                                <div class="mt-2">
                                                    <a href="create_conversation.php?user_id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-light me-2">Create Conversation</a>
                                                    <a href="view_profile.php?user_id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-info">View Profile</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <script type="module" src="js/app.js"></script>
    <script type="module" src="js/languageManager.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-2.2.4.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            if (elements && elements.length > 0) {
                elements.forEach(element => {
                    if (element) {
                        const delay = element.style.getPropertyValue('--delay') || '0s';
                        element.style.animationDelay = delay;
                    }
                });
            }
        });
    </script>
</body>
</html>