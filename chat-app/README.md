# Chat Application

Application de chat moderne avec architecture MVC et support multilingue.

## 📁 Structure du Projet

```
chat-app/
├── src/
│   ├── Core/           # Classes noyau (Router, Application, Translator)
│   ├── Controllers/    # Contrôleurs de l'application
│   ├── Models/         # Modèles de données
│   ├── Views/          # Templates et vues
│   └── translations/   # Fichiers de traduction
├── public/            # Point d'entrée public
├── routes/            # Définition des routes
└── bootstrap/        # Bootstrap de l'application
```

## 🚀 Installation

1. Cloner le projet:
```bash
git clone [url-du-repo]
cd chat-system/chat-app
```

2. Installer les dépendances:
```bash
composer install
```

3. Configurer l'environnement:
```bash
cp .env.example .env
# Modifier les variables dans .env selon votre environnement
```

4. Initialiser la base de données:
```bash
mysql -u root -p < database/init.sql
```

## 🏃‍♂️ Démarrage

### Méthode 1: PHP Built-in Server
```bash
php server.php
```

### Méthode 2: Batch Windows
```bash
start-server.bat
```

### Méthode 3: Laragon
1. Démarrer Laragon
2. Accéder à http://localhost:8080

## 🔧 Configuration

### Variables d'environnement
```env
APP_NAME=ChatApp
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_HOST=localhost
DB_NAME=chat_app
DB_USER=root
DB_PASS=
```

### Routes disponibles
```php
/                  # Page d'accueil
/login            # Connexion
/register         # Inscription
/chat            # Interface de chat
/profile         # Profil utilisateur
```

## 🌍 Internationalisation

Langues supportées:
- 🇫🇷 Français (fr)
- 🇬🇧 English (en)
- 🇪🇸 Español (es)
- 🇨🇳 中文 (zh)
- 🇹🇿 Kiswahili (sw)

## 💬 Fonctionnalités Chat

- Messages texte
- Messages vocaux
- Appels vocaux en temps réel (WebRTC)
- Partage de fichiers
- Statuts utilisateurs
- Conversations privées
- Conversations de groupe
- Notifications en temps réel

## 🔒 Sécurité

- Protection CSRF
- Validation des entrées
- Sessions sécurisées
- Authentification JWT
- Encryption des messages

## 🛠 Technologies

- PHP 8.0+
- MySQL 5.7+
- WebSocket
- Bootstrap 5
- JavaScript ES6+

## 📦 API WebSocket & WebRTC

### WebSocket Connection:
```javascript
const ws = new WebSocket('ws://localhost:8090');
```

### Appels vocaux (WebRTC):
```javascript
// Initialiser un appel
await callManager.startCall(userId);

// Répondre à un appel
await callManager.answerCall(callId);

// Terminer un appel
await callManager.endCall();
```

Configurations WebRTC:
```javascript
const rtcConfig = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'turn:your-turn-server.com', 
      username: 'username',
      credential: 'password'
    }
  ]
};
```

## 👥 Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

MIT License - voir [LICENSE](LICENSE) pour plus de détails.