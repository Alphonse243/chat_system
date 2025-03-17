# Système de Seeding pour Chat System

## Structure de la Base de Données

### Table Users
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table Messages
```sql
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT,
    type ENUM('text', 'image', 'file') DEFAULT 'text',
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Utilisation du Seeding

1. Assurez-vous que Composer est installé et que Faker est disponible :
```bash
composer require fakerphp/faker
```

2. Exécutez le seeding :
```bash
php database/seeds/DatabaseSeeder.php
```

## Contenu du Seeding

### Users Seeder
- Crée un utilisateur admin par défaut
  - Username: admin
  - Email: admin@example.com
  - Password: password123
- Génère 10 utilisateurs aléatoires avec Faker

### Messages Seeder
- Génère 3-8 messages par utilisateur actif
- Types de messages : text, image, file
- Statuts possibles : sent, delivered, read
- Dates générées sur le dernier mois

## Configuration

La configuration de la base de données se trouve dans `Database.php` :
- Host: localhost
- Database: chat_system
- User: root
- Password: (empty)
