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
    <style>
        /* Styles imitant WhatsApp */
        body {
            background-color: #e5ddd5; /* Fond d'écran WhatsApp */
        }
        .card {
            background-color: #fff;
            border: none;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #00a884; /* Couleur de l'en-tête WhatsApp */
            color: white;
        }
        .messages-container {
            padding: 10px;
        }
        .message {
            clear: both;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.3;
            max-width: 80%;
            word-wrap: break-word;
        }
        .sent {
            background-color: #dcf8c6; /* Bulle envoyée */
            float: right;
            text-align: right;
        }
        .received {
            background-color: #fff; /* Bulle reçue */
            float: left;
        }
        .message-time {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
            display: block;
        }
        .input-group {
            background-color: #f0f0f0;
            padding: 8px;
            border-radius: 20px;
        }
        .form-control {
            border: none;
            background-color: transparent !important;
            box-shadow: none !important;
        }
        .form-control:focus {
            outline: none !important;
        }
        .btn-light {
            background-color: #f0f0f0;
            border: none;
        }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 5px;
        }
    </style>
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
                        <div class="input-group d-flex align-items-center">
                            <input type="text" id="message-input" 
                                   class="form-control rounded-pill me-2" 
                                   data-i18n="type_message"
                                   placeholder="<?= $translator->translate('type_message') ?>" 
                                   style="background-color: #f0f2f0;">
                            
                            <!-- Bouton Micro/Envoi -->
                            <button id="record-button" class="btn btn-light rounded-circle me-2" title="<?= $translator->translate('record_voice') ?>">
                                <i class="fas fa-microphone"></i>
                            </button>
                            <button id="send-button" class="btn btn-primary rounded-circle" title="<?= $translator->translate('send') ?>" style="display:none;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="recording-indicator" class="d-none align-items-center mt-2">
                            <i class="fas fa-circle text-danger me-1"></i>
                            <span><?= $translator->translate('recording') ?>...</span>
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
    <script>
        $(document).ready(function() {
            let recording = false;
            let mediaRecorder;
            let audioChunks = [];

            $('#record-button').click(function() {
                if (!recording) {
                    startRecording();
                } else {
                    stopRecording();
                }
            });

            async function startRecording() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = event => {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        sendVoiceMessage(audioBlob);
                    };

                    mediaRecorder.start();
                    recording = true;
                    $('#record-button').html('<i class="fas fa-stop"></i>');
                    $('#recording-indicator').removeClass('d-none');
                    $('#message-input').prop('disabled', true);
                    $('#send-button').hide();
                } catch (error) {
                    console.error("Erreur d'accès au micro :", error);
                    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                        alert("L'accès au micro a été refusé. Veuillez autoriser l'accès dans les paramètres de votre navigateur.");
                    } else {
                        alert("Erreur d'accès au micro. Veuillez vérifier les permissions.");
                    }
                }
            }

            function stopRecording() {
                mediaRecorder.stop();
                recording = false;
                $('#record-button').html('<i class="fas fa-microphone"></i>');
                $('#recording-indicator').addClass('d-none');
                $('#message-input').prop('disabled', false);
                if ($('#message-input').val().trim() === '') {
                    $('#record-button').show();
                    $('#send-button').hide();
                } else {
                    $('#record-button').hide();
                    $('#send-button').show();
                }
            }

            function sendVoiceMessage(audioBlob) {
                let formData = new FormData();
                formData.append('audio', audioBlob, 'voice.webm');
                formData.append('conversation_id', '<?php echo $_GET['conversationId']; ?>');

                $.ajax({
                    url: '../backend/controllers/MessageController.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        console.log("Réponse du serveur :", response);
                        location.reload();
                    },
                    error: function(error) {
                        console.error("Erreur lors de l'envoi du message vocal :", error);
                    }
                });
            }

            $('#send-button').click(function() {
                const messageText = $('#message-input').val();
                if (messageText.trim() !== '') {
                    $.ajax({
                        url: '../backend/controllers/MessageController.php',
                        type: 'POST',
                        data: {
                            content: messageText,
                            conversation_id: '<?php echo $_GET['conversationId']; ?>',
                            message_type: 'text'
                        },
                        success: function(response) {
                            console.log("Message envoyé :", response);
                            $('#message-input').val('');
                            location.reload();
                        },
                        error: function(error) {
                            console.error("Erreur lors de l'envoi du message :", error);
                        }
                    });
                }
            });

            $('#message-input').on('input', function() {
                if ($(this).val().trim() !== '') {
                    $('#record-button').hide();
                    $('#send-button').show();
                } else {
                    $('#record-button').show();
                    $('#send-button').hide();
                }
            });
        });
    </script>
</body>
</html>