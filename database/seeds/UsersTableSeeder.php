<?php
/**
 * Seeder pour la table users
 * 
 * Génère :
 * - 1 compte administrateur prédéfini
 * - 10 comptes utilisateurs aléatoires
 * 
 * Configuration admin par défaut :
 * - Username: admin
 * - Name: Administrator
 * - Email: admin@example.com
 * - Password: password123
 * - Status: online
 * - Avatar: null
 * - Bio: System Administrator
 * - Is Active: true
 * 
 * Données générées avec Faker :
 * - Usernames uniques
 * - Noms aléatoires
 * - Emails valides
 * - Statuts aléatoires (online/offline/away/busy)
 * - Avatars aléatoires (30% de chance d'avoir un avatar)
 * - Bios aléatoires
 * - Is Active: true
 */
class UsersTableSeeder {
    private $db;
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function run() {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, name, email, password, avatar_url, bio, status, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Admin user
        $stmt->execute([
            'admin',
            'Administrator',
            'admin@gmail.com',
            password_hash('admin', PASSWORD_DEFAULT),
            null,
            'System Administrator',
            'online',
            true
        ]);

        // Generate 50 random users
        for ($i = 0; $i < 50; $i++) {
            $stmt->execute([
                $this->faker->userName,
                $this->faker->name,
                $this->faker->email,
                password_hash('password123', PASSWORD_DEFAULT),
                $this->faker->boolean(30) ? 'avatar_' . $i . '.jpg' : null,
                $this->faker->text(200),
                $this->faker->randomElement(['online', 'offline', 'away', 'busy']),
                true
            ]);
        }
    }
}
