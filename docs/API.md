# Documentation API du Système de Chat

## Authentification
Toutes les requêtes doivent inclure un token de session valide.

## Points d'entrée API

### Utilisateurs

#### Création d'un utilisateur
```http
POST /api/users
Content-Type: application/json

{
    "username": "string",
    "name": "string",
    "email": "string",
    "password": "string",
    "bio": "string"
}
```

#### Mise à jour du statut
```http
PUT /api/users/{id}/status
Content-Type: application/json

{
    "status": "online|offline|away|busy"
}
```

### Conversations

#### Création d'une conversation
```http
POST /api/conversations
Content-Type: application/json

{
    "type": "private|group",
    "name": "string|null",
    "participants": [1, 2, 3]
}
```

#### Envoi d'un message
```http
POST /api/conversations/{id}/messages
Content-Type: application/json

{
    "content": "string",
    "type": "text|image|file|voice",
    "file_url": "string|null"
}
```

## Codes d'erreur
- 400: Requête invalide
- 401: Non authentifié
- 403: Non autorisé
- 404: Ressource non trouvée
- 500: Erreur serveur

# Documentation API du System de Seeding

## Base de données

### Obtenir une instance de la base de données
```php
$db = Database::getInstance()->getConnection();
```

## Seeding API

### Exécuter tous les seeders
```bash
php database/seeds/DatabaseSeeder.php
```

### Classes disponibles

#### DatabaseSeeder
- **Méthode**: `run()`
- **Description**: Exécute tous les seeders dans l'ordre correct
- **Exemple**:
```php
$seeder = new DatabaseSeeder();
$seeder->run();
```

#### UsersTableSeeder
- **Méthode**: `run()`
- **Configuration par défaut**:
  - Admin user: admin@example.com / password123
  - Nombre d'utilisateurs générés: 10
- **Données générées**:
  ```php
  [
    'username'   => string,    // Généré par Faker
    'email'      => string,    // Format email valide
    'password'   => string,    // Hashé avec password_hash()
    'status'     => enum,      // 'active' ou 'inactive'
    'created_at' => datetime   // Dans le dernier mois
  ]
  ```

#### MessagesTableSeeder
- **Méthode**: `run()`
- **Configuration**:
  - Messages par utilisateur: 3-8
  - Types disponibles: text, image, file
- **Données générées**:
  ```php
  [
    'user_id'    => int,       // ID utilisateur existant
    'content'    => string,    // Contenu selon le type
    'type'       => enum,      // 'text', 'image', 'file'
    'status'     => enum,      // 'sent', 'delivered', 'read'
    'created_at' => datetime   // Dans le dernier mois
  ]
  ```

## Personnalisation

### Modifier le nombre d'utilisateurs
Dans `UsersTableSeeder.php`:
```php
// Changer la valeur 10 pour générer plus/moins d'utilisateurs
for ($i = 0; $i < 10; $i++) {
    // ...
}
```

### Modifier les types de messages
Dans `MessagesTableSeeder.php`:
```php
$messageTypes = ['text', 'image', 'file']; // Ajouter/modifier les types
```

### Configuration de la base de données
Dans `Database.php`:
```php
$host = 'localhost';
$db   = 'chat_system';
$user = 'root';
$pass = '';
```

## Dépendances
- PHP 7.4+
- PDO MySQL
- fakerphp/faker

## Gestion des erreurs
Toutes les erreurs sont capturées et affichées avec un message explicite :
```php
try {
    // Code du seeding
} catch(Exception $e) {
    die("Erreur lors du seeding: " . $e->getMessage());
}
```

# Documentation des Seeders

## Vue d'ensemble
Les seeders permettent de peupler la base de données avec des données de test cohérentes.

### Ordre d'exécution
1. UsersTableSeeder - Crée les utilisateurs
2. ConversationsTableSeeder - Crée les conversations
3. ConversationsUsersTableSeeder - Lie les utilisateurs aux conversations
4. MessagesTableSeeder - Génère les messages et statuts

### Dépendances
- PHP 8.1+
- Faker ^1.23
- PDO MySQL

## Classes disponibles

### DatabaseSeeder
```php
$seeder = new DatabaseSeeder();
$seeder->run(); // Exécute tous les seeders dans l'ordre
```

### UsersTableSeeder
Génère :
- 1 admin (admin@gmail.com/admin)
- 50 utilisateurs aléatoires
```php
$userSeeder = new UsersTableSeeder();
$userSeeder->run();
```

### ConversationsTableSeeder
Génère :
- 5 conversations privées
- 3 groupes de discussion
```php
$convSeeder = new ConversationsTableSeeder();
$convSeeder->run();
```

### ConversationsUsersTableSeeder
Assigne :
- 2 participants pour les conversations privées
- 3-6 participants pour les groupes (1 admin)
```php
$convUserSeeder = new ConversationsUsersTableSeeder();
$convUserSeeder->run();
```

### MessagesTableSeeder
Génère :
- 5-15 messages par conversation
- Types: texte, image, fichier, vocal
- Statuts: envoyé, livré, lu
```php
$messageSeeder = new MessagesTableSeeder();
$messageSeeder->run();
```
