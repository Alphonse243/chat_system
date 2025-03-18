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
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <!-- <script src="https://accounts.google.com/gsi/client" async defer></script> -->
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
                                <input type="email" value="admin@gmail.com" class="form-control form-control-lg" name="email" placeholder="Email address" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" value="admin" class="form-control form-control-lg" name="password" placeholder="Password" required>
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

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('/chat-system/chat-app/src/controllers/AuthController.php', {
                method: 'POST',
                body: formData,    <script>
                        // Sélectionne le formulaire de connexion et ajoute un écouteur d'événement pour la soumission du formulaire.
                        document.getElementById('loginForm').addEventListener('submit', function(event) {
                            // Empêche le comportement par défaut de la soumission du formulaire (rechargement de la page).
                            event.preventDefault();
                            // Crée un objet FormData pour récupérer les données du formulaire.
                            const formData = new FormData(this);
                
                            // Envoie une requête AJAX (fetch) au serveur pour authentifier l'utilisateur.
                            fetch('/chat-system/chat-app/src/controllers/AuthController.php', {
                                method: 'POST', // Utilise la méthode POST pour l'envoi des données.
                                body: formData, // Envoie les données du formulaire.
                                headers: {
                                    'Accept': 'application/json' // Indique au serveur que le client attend une réponse au format JSON.
                                }
                            })
                            // Gère la réponse du serveur.
                            .then(async response => {
                                // Récupère le type de contenu de la réponse
                                const contentType = response.headers.get('content-type');
                                //Vérifie si la reponse est de type application/json
                                if (!contentType || !contentType.includes('application/json')) {
                                    // si la réponse n'est pas au format json on lance une erreur
                                    throw new Error('Server response was not JSON');
                                }
                                // Convertit la réponse en JSON.
                                return response.json();
                            })
                            // Gère les données JSON reçues du serveur.
                            .then(data => {
                                // Si l'authentification est réussie (data.success est true).
                                if (data.success) {
                                    // Redirige l'utilisateur vers la page spécifiée dans data.redirect.
                                    window.location.href = data.redirect;
                                } else {
                                    // Si l'authentification échoue.
                                    // Crée une div pour afficher un message d'erreur.
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-danger mt-3'; // Ajoute les classes Bootstrap pour le style.
                                    alertDiv.role = 'alert'; // Ajoute le role pour les lecteurs d'écran
                                    alertDiv.textContent = data.message; // Affiche le message d'erreur reçu du serveur.
                                    
                                    // Récupère le formulaire de connexion.
                                    const form = document.getElementById('loginForm');
                                    // Supprimer l'alerte précédente si elle existe
                                    const existingAlert = form.querySelector('.alert');
                                    if (existingAlert) {
                                        existingAlert.remove();
                                    }
                                    // Insère la div d'alerte au début du formulaire.
                                    form.insertBefore(alertDiv, form.firstChild);
                                    
                                    // Supprime la div d'alerte après 3 secondes.
                                    setTimeout(() => alertDiv.remove(), 3000);
                                }
                            })
                            // Gère les erreurs de la requête fetch.
                            .catch(error => {
                                // Affiche l'erreur dans la console.
                                console.error('Error:', error);
                                // Crée une div pour afficher un message d'erreur générique.
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-danger mt-3';
                                alertDiv.role = 'alert';
                                alertDiv.textContent = 'Server error. Please try again later.';
                                
                                // Récupère le formulaire de connexion.
                                const form = document.getElementById('loginForm');
                                const existingAlert = form.querySelector('.alert');
                                if (existingAlert) {
                                    existingAlert.remove();
                                }
                                // Insère la div d'alerte au début du formulaire.
                                form.insertBefore(alertDiv, form.firstChild);
                                // Supprime la div d'alerte après 3 secondes.
                                setTimeout(() => alertDiv.remove(), 3000);
                            });
                        });
                
                        // Fonction pour gérer la réponse de Google après la connexion via Google.
                        function handleCredentialResponse(response) {
                            // Crée un objet FormData pour envoyer les données de connexion.
                            const formData = new FormData();
                            // Ajoute le token d'identification (credential) reçu de Google au formData.
                            formData.append('credential', response.credential);
                
                            // Envoie une requête AJAX (fetch) au serveur pour authentifier l'utilisateur via Google.
                            fetch('/chat-system/chat-app/src/controllers/GoogleAuthController.php', {
                                method: 'POST', // Utilise la méthode POST.
                                body: formData // Envoie les données du formulaire.
                            })
                            // Gère la réponse du serveur.
                            .then(response => response.json()) // Convertit la réponse en JSON.
                            .then(data => {
                                // Si l'authentification est réussie (data.success est true).
                                if (data.success) {
                                    // Redirige l'utilisateur vers la page spécifiée dans data.redirect.
                                    window.location.href = data.redirect;
                                } else {
                                    // Si l'authentification échoue, affiche une alerte avec le message d'erreur.
                                    alert('Erreur de connexion: ' + data.message);
                                }
                            })
                            // Gère les erreurs de la requête fetch.
                            .catch(error => {
                                // Affiche l'erreur dans la console.
                                console.error('Erreur:', error);
                                // Affiche une alerte avec un message d'erreur générique.
                                alert('Une erreur est survenue lors de la connexion');
                            });
                        }
                    </script>
                
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server response was not JSON');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger mt-3';
                    alertDiv.role = 'alert';
                    alertDiv.textContent = data.message;
                    
                    const form = document.getElementById('loginForm');
                    // Supprimer l'alerte précédente si elle existe
                    const existingAlert = form.querySelector('.alert');
                    if (existingAlert) {
                        existingAlert.remove();
                    }
                    form.insertBefore(alertDiv, form.firstChild);
                    
                    setTimeout(() => alertDiv.remove(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger mt-3';
                alertDiv.role = 'alert';
                alertDiv.textContent = 'Server error. Please try again later.';
                
                const form = document.getElementById('loginForm');
                const existingAlert = form.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                form.insertBefore(alertDiv, form.firstChild);
                setTimeout(() => alertDiv.remove(), 3000);
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
