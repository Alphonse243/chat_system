<?php
namespace ChatApp\Controllers;

require_once __DIR__ . '/../../backend/config/database.php';
use Database;
use ChatApp\Models\User;


class UserController {
    

    protected $table = 'users';

    public function __construct($db) {
        parent::__construct($db, $this->table);
    }

    /**
     * Retourne l'instance du translator
     * @return Translator
     */
    public function getTranslator() {
        return $this->translator;
    }

}
