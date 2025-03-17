<?php

/**
 * Seeder pour la table conversation_participants
 * Permet de générer des participants aléatoires pour les conversations
 */
class ConversationsUsersTableSeeder {
    /** @var PDO Instance de la connexion à la base de données */
    private $db;
    
    /** @var \Faker\Generator Instance du générateur de données factices */
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    /**
     * Exécute le seeding des participants aux conversations
     * - Pour les conversations privées : ajoute 2 utilisateurs aléatoires
     * - Pour les conversations de groupe : ajoute 3 à 6 utilisateurs avec un admin
     */
    public function run() {
        // Récupération des IDs de tous les utilisateurs et conversations
        $users = $this->db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $conversations = $this->db->query("SELECT id, type FROM conversations")->fetchAll(PDO::FETCH_ASSOC);

        // Préparation de la requête d'insertion
        $stmt = $this->db->prepare("
            INSERT INTO conversation_participants (conversation_id, user_id, role, joined_at) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($conversations as $conv) {
            if ($conv['type'] === 'private') {
                // Conversations privées : exactement 2 participants
                $randomUsers = array_rand(array_flip($users), 2);
                foreach ($randomUsers as $userId) {
                    $stmt->execute([
                        $conv['id'],
                        $userId,
                        'member',
                        $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
                    ]);
                }
            } else {
                // Conversations de groupe : entre 3 et 6 participants
                // Le premier utilisateur ajouté devient automatiquement admin
                $randomUsers = array_rand(array_flip($users), rand(3, 6));
                $isFirst = true;
                foreach ($randomUsers as $userId) {
                    $stmt->execute([
                        $conv['id'],
                        $userId,
                        $isFirst ? 'admin' : 'member', // Premier utilisateur est admin
                        $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
                    ]);
                    $isFirst = false;
                }
            }
        }
    }
}
