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
require_once __DIR__ . '/../../../vendor/autoload.php';
/**
 * Seeder principal qui exécute tous les seeders de l'application
 */
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/UsersTableSeeder.php';
require_once __DIR__ . '/MessagesTableSeeder.php';

class DatabaseSeeder {
    public function run() {
        try {
            echo "Début du seeding...\n";
            $userSeeder = new UsersTableSeeder();
            $messageSeeder = new MessagesTableSeeder();

            $userSeeder->run();
            echo "Users seeded successfully!\n";
            
            $messageSeeder->run();
            echo "Messages seeded successfully!\n";
        } catch(Exception $e) {
            die("Erreur lors du seeding: " . $e->getMessage());
        }
    }
}

// Run seeder
$seeder = new DatabaseSeeder();
$seeder->run();
