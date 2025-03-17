# Système de Chat

Une application de chat en temps réel construite avec des technologies modernes.

## Description
Ce projet est une application de chat web permettant aux utilisateurs de communiquer en temps réel.

## Fonctionnalités
- Messagerie en temps réel
- Authentification des utilisateurs
- Conversations privées et groupes
- Historique des messages
- Indicateurs de statut en ligne (online, offline, away, busy)
- Suivi de lecture des messages (envoyé, livré, lu)
- Support multi-fichiers (images, documents, audio)
- Gestion des sessions utilisateurs
- Traçabilité des connexions et activités
- Support multi-appareils
- Gestion des rôles dans les groupes (admin, membre)
- Édition des messages
- Profils utilisateurs personnalisables (avatar, bio)

## Structure de la Base de Données

### Tables Principales
1. `users` - Gestion des utilisateurs
   - Profil complet (username, email, avatar, bio)
   - Statut de présence
   - Historique d'activité

2. `conversations` - Gestion des discussions
   - Support des conversations privées
   - Support des groupes de discussion
   - Horodatage des activités

3. `messages` - Système de messagerie
   - Messages texte
   - Support des fichiers (images, documents, audio)
   - Historique des modifications

4. `attachments` - Gestion des fichiers
   - Support multi-formats
   - Métadonnées des fichiers
   - Stockage sécurisé

5. `user_sessions` - Sécurité et Connexions
   - Suivi des sessions
   - Détection des connexions suspectes
   - Support IPv4/IPv6

### Sécurité
- Mots de passe cryptés
- Gestion des sessions
- Traçabilité des connexions
- Surveillance des activités suspectes

## Structure du Projet
```
chat-system/
├── README.md        # Documentation du projet
```

## Instructions d'Installation

1. Prérequis
- Installer [Laragon](https://laragon.org/download/) (recommandé)
- PHP 8+ (inclus dans Laragon)
- MySQL (inclus dans Laragon)

2. Configuration avec Laragon
```bash
# Cloner le dépôt dans le dossier www de Laragon
cd C:\laragon\www
git clone https://github.com/Alphonse243/chat_system.git
```

3. Configuration de l'environnement
- Démarrer Laragon
- Le site sera automatiquement accessible via : http://chat-system.test
- Les extensions PHP nécessaires sont déjà activées dans Laragon

4. Configurer la base de données
- Ouvrir HeidiSQL depuis Laragon
- Créer une nouvelle base de données 'chat_system'
- Importer le fichier SQL depuis chat-app/backend/init.sql
```bash
# Si vous préférez utiliser la ligne de commande MySQL
mysql -u root -p chat_system < chat-app/backend/chat_system.sql
```

## Technologies Utilisées
- Backend : PHP 8+
- Base de données : MySQL 8+
- Frontend : HTML5, CSS3, JavaScript
- WebSocket pour le temps réel
- Support des formats de fichiers multiples
- Gestion des sessions sécurisées

## Contribution
N'hésitez pas à contribuer à ce projet en soumettant des pull requests.

## Licence
[Informations de licence à ajouter]