<?php
/**
 * Seeder principal du système de chat
 * 
 * Ce fichier orchestre l'exécution de tous les seeders de l'application.
 * Il assure l'ordre correct d'insertion des données :
 * 1. Users - pour créer les utilisateurs de base
 * 2. Messages - pour générer le contenu des conversations
 * 
 * Utilisation : php database/seeds/DatabaseSeeder.php
 */
require_once __DIR__ . '/../../vendor/autoload.php';
/**
 * Seeder principal qui exécute tous les seeders de l'application
 */
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/UsersTableSeeder.php';
require_once __DIR__ . '/MessagesTableSeeder.php';
require_once __DIR__ . '/ConversationsTableSeeder.php';
require_once __DIR__ . '/ConversationsUsersTableSeeder.php';

class DatabaseSeeder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function truncateTables() {
        // Désactiver les contraintes de clés étrangères
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Vider les tables dans l'ordre inverse des dépendances
        $this->db->exec('TRUNCATE TABLE message_status');
        $this->db->exec('TRUNCATE TABLE attachments');
        $this->db->exec('TRUNCATE TABLE messages');
        $this->db->exec('TRUNCATE TABLE conversation_participants');
        $this->db->exec('TRUNCATE TABLE conversations');
        $this->db->exec('TRUNCATE TABLE user_sessions');
        $this->db->exec('TRUNCATE TABLE users');
        
        // Réactiver les contraintes
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function run() {
        try {
            echo "Début du seeding...\n";
            
            // Nettoyer d'abord toutes les tables
            $this->truncateTables();
            echo "Tables nettoyées avec succès!\n";

            // 1. Seeder les utilisateurs en premier car ils sont référencés par les messages
            $userSeeder = new UsersTableSeeder();
            $userSeeder->run();
            echo "Users seeded successfully!\n";
            
            // 2. Seeder les conversations ensuite
            $conversationSeeder = new ConversationsTableSeeder();
            $conversationSeeder->run();
            echo "Conversations seeded successfully!\n";
            
            // 3. Seeder les conversations_users ensuite
            $conversationUserSeeder = new ConversationsUsersTableSeeder();
            $conversationUserSeeder->run();
            echo "Conversations users seeded successfully!\n";
            
            // 4. Seeder les messages ensuite car ils dépendent des utilisateurs
            $messageSeeder = new MessagesTableSeeder();
            $messageSeeder->run();
            echo "Messages seeded successfully!\n";

            echo "Seeding terminé avec succès!\n";
        } catch(Exception $e) {
            die("Erreur lors du seeding: " . $e->getMessage());
        }
    }
}

// Run seeder
$seeder = new DatabaseSeeder();
$seeder->run();
