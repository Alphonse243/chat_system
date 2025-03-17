<?php
class ConversationsTableSeeder {
    private $db;
    private $faker;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    public function run() {
        $stmt = $this->db->prepare("
            INSERT INTO conversations (name, type, created_at) 
            VALUES (?, ?, ?)
        ");

        // Generate 5 private conversations
        for ($i = 0; $i < 5; $i++) {
            $stmt->execute([
                null,
                'private',
                $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
            ]);
        }

        // Generate 3 group conversations
        for ($i = 0; $i < 3; $i++) {
            $stmt->execute([
                $this->faker->words(3, true),
                'group',
                $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s')
            ]);
        }
    }
}
