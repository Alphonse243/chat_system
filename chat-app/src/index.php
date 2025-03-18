<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';

// Récupérer les informations de l'utilisateur
$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$currentUser = $userModel->getById($_SESSION['user_id']);
$getConversations = $userModel->getConversations($_SESSION['user_id']);
    //// DEBUG
// var_dump($getConversations);
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
            <!-- Users Online Section -->
            <div class="col-md-3">
                <div class="card rounded-3 border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 text-primary fw-bold" data-i18n="contacts"><?= $translator->translate('contacts') ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="current-user mb-3 p-3 border-bottom ">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($currentUser['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['username'])) ?>" 
                                     class="avatar me-2" 
                                     alt="<?= htmlspecialchars($currentUser['username']) ?>">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($currentUser['username']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></small>
                                </div>
                            </div>
                        </div>
                        <ul id="online-users" class="list-group list-group-flush current-user mb-3 p-3 border-bottom">
                            <!-- La liste des utilisateurs sera générée dynamiquement -->
                            <?php
                                foreach($getConversations as $item){
                                    ?>
                                    <div class=" btn btn-primary d-flex align-items-center mb-3">
                                        <img src="<?= htmlspecialchars($item['conversations_name'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($item['conversations_name'])) ?>" 
                                            class="avatar me-2" 
                                            alt="<?= htmlspecialchars($item['conversations_name']) ?>">
                                        <div>
                                            <div class="fw-bold text-white d-flex "><?= $item['conversations_name'] ?> <?= $item['conversations_type'] ?>  </div>
                                            <small class=" text-white  ">Last message de la conversation,......</small>
                                        </div>
                                    </div>
                                    <?php
                                }
                            ?>
                            
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Chat Section -->
            <div class="col-md-9">
                <div class="card rounded-3 border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" data-i18n="chat_room"><?= $translator->translate('chat_room') ?></h6>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-light rounded-circle me-2"><i class="fas fa-phone"></i></button>
                            <button class="btn btn-light rounded-circle me-2"><i class="fas fa-video"></i></button>
                            <button class="btn btn-light rounded-circle"><i class="fas fa-info-circle"></i></button>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        <div id="messages" class="messages-container mb-3" style="max-height: 500px; overflow-y: auto;">
                            <div class="message received">
                                <div class="d-flex align-items-start">
                                    <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                    <div class="message-content">
                                        <div class="fw-bold mb-1">John Doe</div>
                                        <div class="message-text">Hey! Comment ça va aujourd'hui? 😊</div>
                                        <div class="message-time">10:03</div>
                                    </div>
                                </div>
                            </div>

                            <div class="message sent">
                                <div class="message-content">
                                    <div class="message-text">Salut! Ça va bien, merci ! Et toi?</div>
                                    <div class="message-time">10:04</div>
                                </div>
                            </div>
                            <!-- 
                            <div class="message received">
                                <div class="d-flex align-items-start">
                                    <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                    <div class="message-content">
                                        <div class="message-text">Super bien! Tu as vu les dernières mises à jour? 🚀</div>
                                        <div class="message-time">10:05</div>
                                    </div>
                                </div>
                            </div>

                            <div class="message sent">
                                <div class="message-content">
                                    <div class="message-text">Pas encore, qu'est-ce qui est nouveau? 🤔</div>
                                    <div class="message-time">10:06</div>
                                </div>
                            </div>

                            <div class="message received">
                                <div class="d-flex align-items-start">
                                    <img src="https://ui-avatars.com/api/?name=John+Doe" class="avatar me-2" alt="John">
                                    <div class="message-content">
                                        <div class="message-text">Il y a plein de nouvelles fonctionnalités cool! Je peux te montrer ça demain au bureau si tu veux</div>
                                        <div class="message-time">10:07</div>
                                    </div>
                                </div>
                            </div>

                            <div class="message sent">
                                <div class="message-content">
                                    <div class="message-text">Parfait! On se voit demain alors 👍</div>
                                    <div class="message-time">10:08</div>
                                </div>
                            </div> -->
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