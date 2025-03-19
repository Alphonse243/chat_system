# Chat Application

Application de chat moderne avec support multilingue et fonctionnalités avancées.

## Architecture du Projet

### 1. Frontend (/chat-app/src/)
- **Views**: Templates PHP pour le rendu des pages
- **CSS**: Styles modulaires (style.css, navbar.css, login.css)
- **JavaScript**: Scripts interactifs (app.js, languageManager.js, profile.js)
- **Controllers**: Gestion de la logique (AuthController, NavigationController, MessageController)
- **Models**: Modèles de données (User, Message, Conversation)

### 2. Backend
- Base de données MySQL relationnelle
- API endpoints sécurisés
- Gestion des sessions utilisateur
- Système de sécurité robuste

## Fonctionnalités Principales

### 1. Système d'Authentification
- Login/Register traditionnels
- Intégration Google Sign-In
- Gestion des sessions sécurisées
- Protection contre les attaques courantes

### 2. Messagerie en Temps Réel
- Support des messages texte
- Messagerie vocale intégrée
- Indicateurs de statut utilisateur
- Avatars dynamiques (API DiceBear)
- Historique des conversations
- Notifications en temps réel

### 3. Internationalisation (i18n)
- Support de multiple langues
  - Français (FR)
  - Anglais (EN)
  - Espagnol (ES)
  - Chinois (ZH)
  - Swahili (SW)
- Changement de langue dynamique sans rechargement
- Système de traduction extensible

### 4. Interface Utilisateur
- Design responsive (Bootstrap)
- Animations et transitions fluides
- Thème moderne inspiré de WhatsApp/Facebook
- Mode sombre/clair
- Interface intuitive

### 5. Fonctionnalités Sociales
- Profils utilisateurs personnalisables
- Gestion des contacts et amis
- Statuts d'activité
- Partage de médias

## Structure Technique

### Base de Données
- Tables principales:
  - users
  - conversations
  - messages
  - conversation_participants
  - message_status
  - attachments
  - user_sessions

### Sécurité
- Préparation des requêtes SQL
- Validation des entrées
- Protection XSS
- Gestion des sessions sécurisée
- Encryption des mots de passe

### Performance
- Mise en cache optimisée
- Requêtes SQL optimisées
- Chargement asynchrone
- Pagination des résultats

## Installation

1. Cloner le repository:
```bash
git clone [url-du-repo]
```

2. Configuration de la base de données:
```bash
cd backend
mysql -u root -p < init.sql
```

3. Installation des dépendances:
```bash
composer install
```

4. Configuration:
- Copier `.env.example` vers `.env`
- Configurer les variables d'environnement

## Développement

### Prérequis
- PHP 7.4+
- MySQL 5.7+
- Composer
- Node.js (pour le développement frontend)

### Technologies Utilisées
- Backend:
  - PHP
  - MySQL
  - Composer
- Frontend:
  - HTML5/CSS3
  - JavaScript (ES6+)
  - Bootstrap 5
  - Font Awesome

## Licence
Ce projet est sous licence MIT.