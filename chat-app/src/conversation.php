<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Conversation.php';
require '../../vendor/autoload.php';
use Carbon\Carbon;

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
<body>
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
                            <button class="btn btn-light rounded-circle me-2" title="<?= $translator->translate('phone') ?>"><i class="fas fa-phone"></i></button>
                            <button class="btn btn-light rounded-circle me-2" title="<?= $translator->translate('video') ?>"><i class="fas fa-video"></i></button>
                            <button class="btn btn-light rounded-circle" title="<?= $translator->translate('info') ?>"><i class="fas fa-info-circle"></i></button>
                        </div>
                    </div> 
                    <div class="card-body bg-white">
                        <div id="messages" class="messages-container mb-3" style="max-height: 500px; overflow-y: auto;">
                        
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
                                    $messageCreatedAt = Carbon::parse($item['updated_at']);
                                    Carbon::setlocale('fr');
                                    $FormattedDate = $messageCreatedAt->translatedFormat('L d F Y') ;
                                ?> 
                                    <div class="message <?= $positionMessage ?> ">
                                        <div class="d-flex align-items-start">
                                            <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                            <div class="message-content">
                                                <div class="fw-bold <?= $design ?> mb-1"> <?= $name ?> </div>
                                                <?php if ($item['message_type'] == 'image'): ?>
                                                    <!-- Affichage d'une image si le type de message est 'image' -->
                                                    <img src="<?= htmlspecialchars($item['content']) ?>" alt="Image" width="200" class="img-thumbnail">
                                                <?php elseif ($item['message_type'] == 'file'): ?>
                                                    <!-- Affichage d'un lien de téléchargement si le type de message est 'file' -->
                                                    <a href="<?= htmlspecialchars($item['content']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Télécharger le fichier</a>
                                                <?php elseif ($item['message_type'] == 'voice'): ?>
                                                    <!-- Affichage d'un lecteur audio si le type de message est 'voice' -->
                                                    <audio controls class="w-100">
                                                        <source src="<?= htmlspecialchars($item['content']) ?>" type="audio/ogg">
                                                        <source src="<?= htmlspecialchars($item['content']) ?>" type="audio/mpeg">
                                                        Votre navigateur ne supporte pas l'élément audio.
                                                    </audio>
                                                <?php else: ?>
                                                    <!-- Affichage du texte du message par défaut -->
                                                    <div class="message-text"><?= $item['content'] ?> 😊</div>
                                                <?php endif; ?>
                                                <!-- Affichage de la date et de l'heure du message -->
                                                <div class="message-time"><?= ucfirst($FormattedDate) ?>  <?= $messageCreatedAt->diffForHumans() ?></div> 
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
                            <button id="send-button" class="btn btn-primary rounded-circle" title="<?= $translator->translate('send') ?>"><i class="fas fa-paper-plane"></i></button>
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