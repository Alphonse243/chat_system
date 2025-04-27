<?php
session_start();
use ChatApp\Controllers\NavigationController;
use ChatApp\Models\User;

require_once __DIR__ . '/controllers/NavigationController.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ .'/../backend/config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$viewedUser = $userModel->getById($_GET['user_id']);

if (!$viewedUser) {
    header('Location: index.php');
    exit;
}

$navController = new NavigationController();
$translator = $navController->getTranslator();

$conversationModel = new ChatApp\Models\Conversation($db);
$existingConversation = $conversationModel->getConversationBetweenUsers($_SESSION['user_id'], $viewedUser['id']);
$userConversations = $userModel->getConversations($viewedUser['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewedUser['username']) ?>'s Profile</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-nav {
            border-top: 1px solid #ddd;
            background: #fff;
            padding: 0;
        }

        .profile-nav .nav-link {
            padding: 15px 25px;
            color: #65676B;
            font-weight: 600;
        }

        .profile-nav .nav-link.active {
            color: #1876F2;
            border-bottom: 3px solid #1876F2;
        }

        .profile-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            color: #050505;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .friend-item {
            position: relative;
        }

        .status-indicator {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .status-online {
            background-color: #31a24c;
        }

        .status-offline {
            background-color: #ccc;
        }
    </style>
</head>
<body style="background-color: #f0f2f5;">
    <?php
        $navController->renderNavbar();
        include 'views/partials/downbar.php'
    ?>

    <div class="container-fluid px-4">
        <!-- Profile Card -->
        <div class="text-center col-lg-6 mx-auto mb-4">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($viewedUser['username']) ?>" 
                 class="rounded-circle mb-3" 
                 alt="<?= htmlspecialchars($viewedUser['username']) ?>"
                 style="width: 150px; height: 150px;">
            <h3 class="card-title"><?= htmlspecialchars($viewedUser['username']) ?></h3>
            <p class="text-muted"><?= htmlspecialchars($viewedUser['bio']) ?></p>
            <div class="status-badge mb-3">
                <span class="badge <?= $viewedUser['status'] === 'online' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= ucfirst($viewedUser['status']) ?>
                </span>
            </div>
            <a href="create_conversation.php?user_id=<?= htmlspecialchars($viewedUser['id']) ?>" 
               class="btn btn-primary">
                Send Message
            </a>
        </div>

        <!-- Navigation -->
        <ul class="nav profile-nav justify-content-center mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#"><?= $translator->translate('Posts') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('About') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('Friends') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('Photos') ?></a>
            </li>
        </ul>

        <div class="row">
            <!-- About & Friends Section -->
            <div class="col-md-3 ">
                <div class="profile-section">
                    <h2 class="section-title"><?= $translator->translate('About') ?></h2>
                    <div class="mb-3">
                        <i class="fas fa-briefcase me-2"></i> <?= $translator->translate('Works at') ?> Company Name
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-graduation-cap me-2"></i> <?= $translator->translate('Studied at') ?> University
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-home me-2"></i> <?= $translator->translate('Lives in') ?> City
                    </div>
                </div>

                <div class="profile-section">
                    <h2 class="section-title"><?= $translator->translate('Friends') ?></h2>
                    <div class="friends-grid">
                        <?php 
                        foreach($userConversations as $conv): 
                            $otherParticipant = $conversationModel->getOtherParticipant($conv['conversations_id'], $viewedUser['id']);
                            if($otherParticipant):
                        ?>
                        <div class="friend-item ">
                            <a href="view_profile.php?user_id=<?= htmlspecialchars($otherParticipant['id']) ?>" 
                               class="text-decoration-none  ">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($otherParticipant['username']) ?>" 
                                     alt="<?= htmlspecialchars($otherParticipant['username']) ?>"
                                     title="<?= htmlspecialchars($otherParticipant['username']) ?>">
                                <span class="status-indicator <?= $otherParticipant['status'] === 'online' ? 'status-online' : 'status-offline' ?>"></span>
                                <div class="text-center mt-2 text-dark ">
                                    <h6><?= htmlspecialchars($otherParticipant['username']) ?></h6>
                                    <a class="text-decoration-none btn btn-primary" href="conversation.php?conversationId=<?= htmlspecialchars($conv['conversations_id']) ?>">Message</a>
                                </div>
                            </a>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Posts Section -->
            <div class="col-md-9">
                <div class="profile-section">
                    <div class="posts-container">
                        <!-- Example post -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($viewedUser['username']) ?></h6>
                                <p class="card-text">Sample post content</p>
                                <small class="text-muted">Posted on January 1, 2024</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-2.2.4.min.js"></script>
</body>
</html>
