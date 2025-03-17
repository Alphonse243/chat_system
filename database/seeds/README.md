# Documentation des Seeders

## Configuration

### 1. Connexion Base de données
```php
// Database.php
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'chat_system';
```

### 2. Paramètres des Seeders

#### UsersTableSeeder
- ADMIN_EMAIL: admin@gmail.com
- ADMIN_PASSWORD: admin
- USERS_COUNT: 50

#### ConversationsTableSeeder
- PRIVATE_CONVERSATIONS: 5
- GROUP_CONVERSATIONS: 3

#### MessagesTableSeeder
- MIN_MESSAGES: 5
- MAX_MESSAGES: 15
- MESSAGE_TYPES: ['text', 'image', 'file', 'voice']

## Exécution

1. Composer install
```bash
composer install
```

2. Initialisation base de données
```bash
php database/Database.php
```

3. Exécution des seeders
```bash
php database/seeds/DatabaseSeeder.php
```

## Structure des données générées

### Utilisateurs
- Admin prédéfini
- Utilisateurs avec données aléatoires
- Statuts variés (online, offline, away, busy)
- Avatars aléatoires (30% de chance)

### Conversations
- Privées: 2 participants
- Groupes: 3-6 participants, 1 admin
- Messages variés avec timestamps

### Messages
- Contenu généré par Faker
- Types variés avec fichiers
- Statuts de lecture aléatoires
- Horodatage sur le dernier mois

## Extensions
Voir la documentation API complète pour plus de détails sur la personnalisation des seeders.
