# Documentation du Système de Chat

## Structure du Projet

```
chat-system/
├── backend/
│   ├── config/
│   │   └── database.php      # Configuration de la base de données
│   ├── models/
│   │   ├── BaseModel.php     # Classe de base pour les modèles
│   │   ├── User.php         # Gestion des utilisateurs
│   │   ├── Conversation.php # Gestion des conversations
│   │   ├── Message.php      # Gestion des messages
│   │   └── Attachment.php   # Gestion des pièces jointes
│   └── init.sql             # Script d'initialisation de la base de données
├── chat-app/
│   ├── ...                  # Frontend files (HTML, CSS, JS)
│   └── ...
├── docs/
│   └── API.md               # API documentation
└── vendor/                   # Composer dependencies
```

## Fonctionnalités

-   **Real-time Messaging:** Permet aux utilisateurs d'échanger des messages en temps réel.
-   **Multilingual Translation System:** Supporte plusieurs langues et permet de changer de langue dynamiquement.
-   **Voice Messaging:** Permet aux utilisateurs d'envoyer et de recevoir des messages vocaux.
-   **Profile Update:** Permet aux utilisateurs de mettre à jour leurs informations de profil.

## Base de Données

### Tables Principales

-   `users` : Stockage des informations utilisateurs
-   `conversations` : Gestion des conversations privées et groupes
-   `messages` : Stockage des messages
-   `attachments` : Gestion des fichiers joints
-   `message_status`: Stockage du statut des messages pour chaque utilisateur

### Relations

-   Un utilisateur peut participer à plusieurs conversations
-   Une conversation peut contenir plusieurs messages
-   Un message peut avoir plusieurs pièces jointes
-   Chaque message a un statut pour chaque destinataire

## Modèles

### BaseModel

Classe abstraite fournissant les fonctionnalités de base pour tous les modèles.

```php
checkTable()      // Vérifie l'existence d'une table
handleError()     // Gestion des erreurs
```

### User

Gestion des utilisateurs et de l'authentification.

```php
create()          // Création d'un nouvel utilisateur
getById()         // Récupération d'un utilisateur
updateStatus()    // Mise à jour du statut
createSession()   // Création d'une session
validateSession() // Validation d'une session
```

### Conversation

Gestion des conversations individuelles et de groupe.

```php
create()          // Création d'une conversation
addParticipant()  // Ajout d'un participant
getParticipants() // Liste des participants
getMessages()     // Messages d'une conversation
```

### Message

Gestion des messages et de leur statut.

```php
create()          // Création d'un message
markAsRead()      // Marquer comme lu
edit()            // Modification d'un message
```

### Attachment

Gestion des pièces jointes.

```php
create()          // Ajout d'une pièce jointe
getByMessageId()  // Récupération des pièces jointes
```

## Dépendances

-   PHP 7.4+
-   MySQL
-   Composer
-   fakerphp/faker
-   Carbon
-   Eloquent ORM

## Sécurité

### Sessions

-   Tokens de session uniques
-   Expiration après 24h d'inactivité
-   Validation IP et User-Agent

### Mots de passe

-   Hashage avec PASSWORD_DEFAULT
-   Pas de stockage en clair

## Exemples d'Utilisation

### Création d'un utilisateur

```php
$db = Database::getInstance()->getConnection();
$userModel = new User($db);

$userData = [
    'username' => 'john_doe',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
    'avatar_url' => null,
    'bio' => 'Hello world!'
];

$userModel->create($userData);
```

### Envoi d'un message

```php
$messageModel = new Message($db);
$messageId = $messageModel->create(
    senderId: 1,
    conversationId: 1,
    content: 'Hello!',
    messageType: 'text'
);
```

## Notes d'implémentation

### Conversations privées

-   Limitées à 2 participants
-   Création automatique si inexistante
-   Pas de nom de conversation

### Messages

-   Statut de lecture automatique
-   Support de différents types (texte, image, fichier, vocal)
-   Horodatage des modifications

### Performance

-   Index sur les clés étrangères
-   Index sur les champs de recherche fréquente
-   Optimisation des requêtes avec jointures

## API

Pour plus d'informations sur l'API, veuillez consulter le fichier [API.md](./API.md).
