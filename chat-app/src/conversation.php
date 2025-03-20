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
$userModel = new ChatApp\Models\User($db);
$conversationModel = new ChatApp\Models\Conversation($db); // Pass the database connection
$currentConversation = $conversationModel->getMessages($_GET['conversationId']);
$otherParticipant = $conversationModel->getOtherParticipant($_GET['conversationId'], $_SESSION['user_id']);
$currentUser = $userModel->getById($_SESSION['user_id']);
$getConversations = $userModel->getConversations($_SESSION['user_id']);

use ChatApp\Controllers\NavigationController;

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
            background-color: #31a24c;
        }
        .status-offline {
            background-color: #ccc;
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
                        <div class="d-flex align-items-center">
                         <a href="view_profile.php?user_id=<?= urlencode($otherParticipant['id']) ?>">
                            <div class="avatar-container">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($otherParticipant['username']) ?>" 
                                    class="avatar me-2" 
                                    alt="<?= htmlspecialchars($otherParticipant['username']) ?>">
                                <span class="status-indicator <?= $otherParticipant['status'] === 'online' ? 'status-online' : 'status-offline' ?>"></span>
                            </div>
                         </a>
                            <h6 class="mb-0 fw-bold" data-i18n="chat_room"><?= htmlspecialchars($otherParticipant['username']) ?></h6>
                         
                        </div>
                        <div class="d-flex align-items-center">
                            <button id="start-call" class="btn btn-light rounded-circle me-2" title="<?= $translator->translate('phone') ?>">
                                <i class="fas fa-phone"></i>
                            </button>
                            <div id="call-status" class="d-none">
                                <span class="badge bg-success me-2">Appel en cours</span>
                                <button id="end-call" class="btn btn-danger btn-sm rounded-circle">
                                    <i class="fas fa-phone-slash"></i>
                                </button>
                            </div>
                            <audio id="remote-audio" autoplay></audio>
                            <audio id="local-audio" muted autoplay></audio>
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
                                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($name) ?>" 
                                                class="avatar me-2" 
                                                alt="<?= htmlspecialchars($name) ?>">
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
                        
                        <form id="message-form" action="chat-app/src/controllers/MessageController.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                            <input type="hidden" name="conversation_id" value="<?php echo $_GET['conversationId']; ?>">
                            <input type="hidden" name="message_type" id="message-type" value="text">
                            <div class="input-group d-flex align-items-center">
                                <input type="text" id="message-input" name="content"
                                       class="form-control rounded-pill me-2"
                                       data-i18n="type_message"
                                       placeholder="<?= $translator->translate('type_message') ?>"
                                       style="background-color: #f0f2f0;">
                            
                                <!-- Bouton Micro/Envoi -->
                                <button id="record-button" class="btn btn-light rounded-circle me-2" type="button" title="<?= $translator->translate('record_voice') ?>">
                                    <i class="fas fa-microphone"></i>
                                </button>
                                <button id="send-button" class="btn btn-primary rounded-circle" type="submit" title="<?= $translator->translate('send') ?>" style="display:none;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <input type="file" id="audio-file" name="audio" style="display: none;">
                            </div>
                        </form>
                        
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
    <script src="/chat-system/chat-app/public/js/callManager.js"></script>
    <script>
        $(document).ready(function() {
            let ws = null;
            let callManager = null;
            let reconnectAttempts = 0;
            const maxReconnectAttempts = 5;
            const reconnectInterval = 5000;
            const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;

            function connectWebSocket() {
                if (ws) {
                    ws.close();
                }

                ws = new WebSocket('ws://localhost:8090');
                
                ws.onerror = function(error) {
                    console.error('WebSocket Error:', error);
                    $('#start-call').prop('disabled', true);
                    if (reconnectAttempts < maxReconnectAttempts) {
                        setTimeout(connectWebSocket, reconnectInterval);
                        reconnectAttempts++;
                    }
                };

                ws.onopen = function() {
                    console.log('WebSocket Connected');
                    // Envoyer l'ID de l'utilisateur après la connexion
                    ws.send(JSON.stringify({
                        type: 'identify',
                        userId: currentUserId
                    }));
                    $('#start-call').prop('disabled', false);
                    reconnectAttempts = 0;
                    callManager = new CallManager(ws);
                };

                // Déplacer onmessage ici
                ws.onmessage = async function(event) {
                    console.log('WebSocket message received:', event.data);
                    try {
                        const data = JSON.parse(event.data);
                        
                        switch(data.type) {
                            case 'connection':
                                console.log('Connection established, ID:', data.id);
                                break;
                            case 'call-offer':
                                console.log('Received call offer from:', data.from);
                                if (data.target && data.target.toString() === currentUserId.toString()) {
                                    // Utiliser un son depuis un CDN
                                    const ringtone = new Audio('https://cdn.pixabay.com/download/audio/2022/03/24/audio_c8c8a73467.mp3');
                                    ringtone.loop = true; // Le son se répète jusqu'à la réponse
                                    
                                    try {
                                        await ringtone.play();
                                        const callerName = "<?= htmlspecialchars($otherParticipant['username']) ?>";
                                        
                                        if (confirm(`Appel entrant de ${callerName}. Accepter?`)) {
                                            ringtone.pause(); // Arrêter la sonnerie si l'appel est accepté
                                            await callManager.answerCall(data.from, data.offer);
                                            $('#call-status').removeClass('d-none');
                                            $('#start-call').addClass('d-none');
                                        } else {
                                            ringtone.pause(); // Arrêter la sonnerie si l'appel est refusé
                                            ws.send(JSON.stringify({
                                                type: 'call-rejected',
                                                target: data.from
                                            }));
                                        }
                                    } catch (error) {
                                        console.error('Error handling call:', error);
                                    }
                                }
                                break;
                            case 'call-answer':
                                console.log('Call answered');
                                await callManager.handleAnswer(data.answer);
                                break;
                            case 'call-rejected':
                                alert('L\'appel a été refusé');
                                $('#call-status').addClass('d-none');
                                $('#start-call').removeClass('d-none');
                                break;
                            case 'call-error':
                                alert(data.message);
                                $('#call-status').addClass('d-none');
                                $('#start-call').removeClass('d-none');
                                break;
                            case 'ice-candidate':
                                if (callManager && callManager.peerConnection) {
                                    console.log('ICE candidate received');
                                    await callManager.addIceCandidate(data.candidate);
                                }
                                break;
                            default:
                                console.log('Message type:', data.type);
                        }
                    } catch (error) {
                        console.error('Error handling WebSocket message:', error);
                    }
                };

                ws.onclose = function() {
                    console.log('WebSocket Disconnected');
                    $('#start-call').prop('disabled', true);
                    callManager = null;
                    
                    if (reconnectAttempts < maxReconnectAttempts) {
                        setTimeout(connectWebSocket, reconnectInterval);
                        reconnectAttempts++;
                    }
                };

                return ws;
            }

            // Initialiser la connexion WebSocket
            connectWebSocket();
            
            // Gestion des appels
            $('#start-call').click(async function() {
                if (!ws || ws.readyState !== WebSocket.OPEN) {
                    alert('La connexion au serveur n\'est pas établie. Réessayez dans quelques instants.');
                    return;
                }
                
                if (!callManager) {
                    alert('Le gestionnaire d\'appels n\'est pas initialisé. Réessayez dans quelques instants.');
                    return;
                }
                
                try {
                    const userId = <?= json_encode($otherParticipant['id']) ?>;
                    await callManager.startCall(userId);
                    $('#call-status').removeClass('d-none');
                    $(this).addClass('d-none');
                } catch (error) {
                    console.error('Call error:', error);
                    alert('Erreur lors du démarrage de l\'appel: ' + error.message);
                }
            });

            // Gestionnaire de fin d'appel amélioré
            $('#end-call').click(function() {
                if (callManager) {
                    callManager.endCall();
                    ws.send(JSON.stringify({
                        type: 'call-ended',
                        target: <?= json_encode($otherParticipant['id']) ?>
                    }));
                }
                $('#call-status').addClass('d-none');
                $('#start-call').removeClass('d-none');
            });
        });
    </script>
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
                        // const audioUrl = URL.createObjectURL(audioBlob);
                        // sendVoiceMessage(audioBlob);
                        
                        // Set message type to voice
                        $('#message-type').val('voice');

                        // Create a file from the audio blob
                        const audioFile = new File([audioBlob], "voice.webm", { type: "audio/webm" });

                        // Create a new DataTransfer object
                        const dataTransfer = new DataTransfer();

                        // Add the file to the DataTransfer object
                        dataTransfer.items.add(audioFile);

                        // Get the file input element
                        const fileInput = document.getElementById('audio-file');

                        // Set the files property of the file input element to the DataTransfer object's files
                        fileInput.files = dataTransfer.files;

                        // Submit the form
                        $('#message-form').submit();
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

            $('#message-input').on('input', function() {
                if ($(this).val().trim() !== '') {
                    $('#record-button').hide();
                    $('#send-button').show();
                } else {
                    $('#record-button').show();
                    $('#send-button').hide();
                }
            });

            // Prevent the form from submitting multiple times
            $('#message-form').submit(function() {
                $(this).submit(function() {
                    return false;
                });
                return true;
            });
        });

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