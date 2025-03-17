# Système de Chat

Une application de chat en temps réel construite avec des technologies modernes.

## Description
Ce projet est une application de chat web permettant aux utilisateurs de communiquer en temps réel.

## Fonctionnalités
- Messagerie en temps réel
- Authentification des utilisateurs
- Conversations privées
- Support des chats de groupe
- Historique des messages
- Indicateurs de statut en ligne

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
- Backend : PHP 8+ (sans framework)
- Frontend : HTML, CSS, JavaScript
- Base de données : MySQL
- Serveur : Apache/Nginx

## Contribution
N'hésitez pas à contribuer à ce projet en soumettant des pull requests.

## Licence
[Informations de licence à ajouter]