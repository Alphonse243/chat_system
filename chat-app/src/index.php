<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../backend/config/database.php';
require_once __DIR__ . '/models/User.php';

use ChatApp\Models\User;
use ChatApp\Controllers\NavigationController;

// Récupérer les informations de l'utilisateur
$db = Database::getInstance()->getConnection();
$userModel = new User($db); // Pass the database connection to the User model
$currentUser = $userModel->getById($_SESSION['user_id']);
$getConversations = $userModel->getConversations($_SESSION['user_id']);
    
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
                            <!-- La liste des conversation privée sera générée dynamiquement -->
                            <?php
                                foreach($getConversations as $item){
                                    ?>
                                    <a href="conversation.php?conversationId=<?= htmlspecialchars($item['conversations_id']) ?>"> 
                                        <div class=" btn btn-primary d-flex align-items-center mb-3">
                                            <img src="<?= htmlspecialchars($currentUser['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['username'])) ?>" 
                                                class="avatar me-2" 
                                                alt="<?= htmlspecialchars($item['conversations_name']) ?>">
                                            <div>
                                                <div class="fw-bold text-white d-flex "><?= $item['conversations_name'] ?> <?= $item['conversations_type'] ?>  </div>
                                                <small class=" text-white  ">Last message de la conversation,......</small>
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