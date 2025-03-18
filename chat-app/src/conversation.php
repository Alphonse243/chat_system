<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Conversation.php';

// Récupérer les informations de l'utilisateur
$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$conversationModel = new Conversation($db);
$currentConversation = $conversationModel->getMessages($_GET['conversationId']);
$currentUser = $userModel->getById($_SESSION['user_id']);
$getConversations = $userModel->getConversations($_SESSION['user_id']);


    //// DEBUG
// var_dump($currentConversation);
// die();

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
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
            <!-- Chat Section -->
            <div class="col-md-9">
                <div class="card rounded-3 border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" data-i18n="chat_room"><?= htmlspecialchars($currentUser['username']) ?></h6>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-light rounded-circle me-2"><i class="fas fa-phone"></i></button>
                            <button class="btn btn-light rounded-circle me-2"><i class="fas fa-video"></i></button>
                            <button class="btn btn-light rounded-circle"><i class="fas fa-info-circle"></i></button>
                        </div>
                    </div> 
                    <div class="card-body bg-white">
                        <div id="messages" class="messages-container mb-3" style="max-height: 500px; overflow-y: auto;">
                            

                            <div class="message sent">
                                <div class="message-content">
                                    <div class="message-text">Salut! Ça va bien, merci ! Et toi?</div>
                                    <div class="message-time">10:04</div>
                                </div>
                            </div>
                            
                            <div class="message received">
                                <div class="d-flex align-items-start">
                                    <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                    <div class="message-content">
                                        <div class="message-text">Super bien! Tu as vu les dernières mises à jour? 🚀</div>
                                        <div class="message-time">10:05</div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                foreach($currentConversation as $item){
                                    if($item['sender_id'] == $_SESSION['user_id']){
                                        $positionMessage = 'sent';
                                        $name = 'vous';
                                        $design = 'text-white';
                                    }else{
                                        $positionMessage = 'received';
                                        $name = $item['username'];
                                        $design = '';
                                    }
                                ?> 
                                    <div class="message <?= $positionMessage ?> ">
                                        <div class="d-flex align-items-start">
                                            <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                            <div class="message-content">
                                                <div class="fw-bold <?= $design ?> mb-1"> <?= $name ?> </div>
                                                <div class="message-text"><?= $item['content'] ?> 😊</div>
                                                <div class="message-time"> <?= $item['updated_at'] ?> </div> 
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                            ?>
                        </div>
                        <div id="typing-indicator" class="typing-indicator d-none">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="message-input" 
                                   class="form-control rounded-pill me-2" 
                                   data-i18n="type_message"
                                   placeholder="<?= $translator->translate('type_message') ?>" 
                                   style="background-color: #f0f2f5;">
                            <button id="send-button" class="btn btn-primary rounded-circle"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="js/jquery-2.2.4.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <script type="module" src="js/app.js"></script>
    <script type="module" src="js/languageManager.js"></script>
</body>
</html>