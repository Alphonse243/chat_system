<?php
/**
 * Seeder pour la table messages
 * 
 * Cette classe est responsable de générer des données de test pour le système de messagerie.
 * Elle crée des messages aléatoires pour simuler des conversations entre utilisateurs.
 * 
 * Fonctionnement détaillé:
 * 1. Connexion à la base de données et initialisation de Faker pour générer des données aléatoires
 * 2. Préparation des requêtes SQL pour l'insertion des messages et leurs statuts
 * 3. Récupération de toutes les conversations avec leurs participants
 * 4. Pour chaque conversation:
 *    - Génère entre 5 et 15 messages
 *    - Alterne entre différents types de messages (texte, image, fichier, audio)
 *    - Crée des statuts de message pour chaque participant
 * 
 * Types de contenus générés:
 * - text: Messages textuels aléatoires via Faker
 * - image: Fichiers images avec UUID unique
 * - file: Documents PDF avec UUID unique 
 * - voice: Messages vocaux avec UUID unique
 * 
 * Statuts possibles des messages:
 * - sent: Message envoyé
 * - delivered: Message livré
 * - read: Message lu
 */
class MessagesTableSeeder {
    private $db;
    private $faker;

    /**
     * Constructeur de la classe
     * Initialise la connexion à la base de données et l'instance de Faker
     * pour générer des données aléatoires en français
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->faker = Faker\Factory::create('fr_FR');
    }

    /**
     * Exécute le seeding des messages
     * Cette méthode principale gère tout le processus de génération des messages
     */
    public function run() {
        /**
         * Prépare la requête d'insertion des messages
         * @param conversation_id - ID de la conversation
         * @param sender_id - ID de l'expéditeur
         * @param content - Contenu du message (texte ou URL du fichier)
         * @param message_type - Type du message (text, image, file, voice)
         * @param file_url - URL du fichier si applicable
         * @param is_edited - Indique si le message a été modifié
         */
        $messageStmt = $this->db->prepare("
            INSERT INTO messages (conversation_id, sender_id, content, message_type, file_url, is_edited) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        /**
         * Prépare la requête d'insertion des statuts de message
         * @param message_id - ID du message
         * @param user_id - ID de l'utilisateur
         * @param status - Statut du message (sent, delivered, read)
         * @param read_at - Date/heure de lecture
         */
        $statusStmt = $this->db->prepare("
            INSERT INTO message_status (message_id, user_id, status, read_at)
            VALUES (?, ?, ?, ?)
        ");

        /**
         * Récupère toutes les conversations avec leurs participants
         * La requête regroupe les IDs des participants par conversation
         */
        $conversations = $this->db->query("
            SELECT c.id, c.type, GROUP_CONCAT(cp.user_id) as user_ids
            FROM conversations c
            JOIN conversation_participants cp ON c.id = cp.conversation_id
            GROUP BY c.id
        ")->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Pour chaque conversation, génère un nombre aléatoire de messages
         * entre 5 et 15 messages par conversation
         */
        foreach ($conversations as $conv) {
            $userIds = explode(',', $conv['user_ids']);
            $numMessages = rand(5, 15);

            /**
             * Génère chaque message individuellement
             * - Choisit aléatoirement un type de message
             * - Crée une URL de fichier unique si nécessaire
             * - Insère le message et ses statuts
             */
            for ($i = 0; $i < $numMessages; $i++) {
                $type = $this->faker->randomElement(['text', 'image', 'file', 'voice']);
                $fileUrl = null;
                
                // Si le type n'est pas texte, génère une URL de fichier unique
                if ($type !== 'text') {
                    $fileUrl = match($type) {
                        'image' => 'uploads/images/' . $this->faker->uuid . '.jpg',
                        'file' => 'uploads/files/' . $this->faker->uuid . '.pdf',
                        'voice' => 'uploads/voice/' . $this->faker->uuid . '.mp3',
                    };
                }

                $messageStmt->execute([
                    $conv['id'],
                    $userIds[array_rand($userIds)],
                    $type === 'text' ? $this->faker->sentence(rand(3, 10)) : $fileUrl,
                    $type,
                    $fileUrl,
                    0  // Changé de false à 0 pour une valeur entière valide
                ]);

                $messageId = $this->db->lastInsertId();

                // Crée des statuts de message pour tous les participants
                foreach ($userIds as $userId) {
                    $statusStmt->execute([
                        $messageId,
                        $userId,
                        $this->faker->randomElement(['sent', 'delivered', 'read']),
                        $this->faker->dateTimeBetween('-1 day')->format('Y-m-d H:i:s')  // Formatage de la date en string
                    ]);
                }
            }
        }
    }
}
