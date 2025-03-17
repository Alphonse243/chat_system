<?php
session_start();
require_once __DIR__ . '/controllers/NavigationController.php';
$navController = new NavigationController();
$translator = $navController->getTranslator();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-md-6 d-none d-md-block">
                <h1 class="text-primary mb-4 facebook-title">chat-system</h1>
                <h2 class="subtitle">Chat-system helps you connect and share with the people in your life.</h2>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <form id="loginForm">
                            <div class="mb-3">
                                <input type="email" class="form-control form-control-lg" name="email" placeholder="Email address" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                            </div>
                            <!-- Google Sign-In Button -->
                            <div class="d-grid gap-2 mt-3">
                                <div id="g_id_onload"
                                     data-client_id="YOUR_GOOGLE_CLIENT_ID"
                                     data-callback="handleCredentialResponse">
                                </div>
                                <div class="g_id_signin google-btn"
                                     data-type="standard"
                                     data-theme="outline"
                                     data-size="large"
                                     data-width="100%">
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            <hr>
                            <div class="d-grid">
                                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                                    Create New Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('/chat-system/chat-app/src/controllers/AuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Erreur de connexion: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la connexion');
            });
        });

        function handleCredentialResponse(response) {
            const formData = new FormData();
            formData.append('credential', response.credential);

            fetch('/chat-system/chat-app/src/controllers/GoogleAuthController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Erreur de connexion: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la connexion');
            });
        }
    </script>
</body>
</html>
