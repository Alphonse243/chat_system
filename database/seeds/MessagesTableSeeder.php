<?php
/**
 * Seeder pour la table messages
 * 
 * Génère des messages aléatoires pour chaque utilisateur actif.
 * 
 * Types de contenu générés :
 * - text : phrases aléatoires
 * - image : noms de fichiers image_[1-1000].jpg
 * - file : noms de fichiers document_[1-1000].pdf
 * 
 * Paramètres de génération :
 * - 3 à 8 messages par utilisateur
 * - Statuts variés (sent/delivered/read)
 * - Dates échelonnées sur le dernier mois
 * - Messages uniquement pour les utilisateurs actifs
 */
class MessagesTableSeeder {
    private $db;
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function run() {
        // Récupération des utilisateurs
        $stmt = $this->db->query("SELECT id FROM users WHERE status = 'active'");
        $users = $stmt->fetchAll();

        $messageTypes = ['text', 'image', 'file'];
        $messageStatus = ['sent', 'delivered', 'read'];

        $stmt = $this->db->prepare("
            INSERT INTO messages (user_id, content, type, status, created_at) 
            VALUES (?, ?, ?, ?, ?)
        ");

        // Generate 50 random messages
        foreach ($users as $user) {
            $numMessages = rand(3, 8); // Random number of messages per user
            
            for ($i = 0; $i < $numMessages; $i++) {
                $type = $this->faker->randomElement($messageTypes);
                $content = $type === 'text' 
                    ? $this->faker->sentence(rand(3, 10))
                    : $type === 'image' 
                        ? 'image_' . $this->faker->numberBetween(1, 1000) . '.jpg'
                        : 'document_' . $this->faker->numberBetween(1, 1000) . '.pdf';

                $stmt->execute([
                    $user['id'],
                    $content,
                    $type,
                    $this->faker->randomElement($messageStatus),
                    $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
