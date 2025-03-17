<?php
class ConversationsUsersTableSeeder {
    private $db;
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function run() {
        // Get all users and conversations
        $users = $this->db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $conversations = $this->db->query("SELECT id, type FROM conversations")->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            INSERT INTO conversation_participants (conversation_id, user_id, role, joined_at) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($conversations as $conv) {
            if ($conv['type'] === 'private') {
                // Add 2 random users for private conversations
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
                // Add 3-6 random users for group conversations
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
