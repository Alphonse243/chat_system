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
 * - Email: admin@example.com
 * - Password: password123
 * - Status: active
 * 
 * Données générées avec Faker :
 * - Usernames uniques
 * - Emails valides
 * - Statuts aléatoires (active/inactive)
 * - Dates de création sur le dernier mois
 */
class UsersTableSeeder {
    private $db;
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function run() {
        // Admin user
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, status, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'admin@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'active',
            date('Y-m-d H:i:s')
        ]);

        // Generate 10 random users
        for ($i = 0; $i < 10; $i++) {
            $username = $this->faker->userName;
            $stmt->execute([
                $username,
                $this->faker->email,
                password_hash('password123', PASSWORD_DEFAULT),
                $this->faker->randomElement(['active', 'inactive']),
                $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
            ]);
        }
    }
}
