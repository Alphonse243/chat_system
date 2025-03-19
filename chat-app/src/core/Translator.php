<?php

namespace ChatApp\Core;

/**
 * Classe Translator
 * Gère le système de traduction multilingue de l'application
 * 
 * @package ChatApp\Core
 */
class Translator {
    /** @var string Langue courante */
    private $lang;
    
    /** @var array Tableau des traductions chargées */
    private $translations = [];

    /**
     * Initialise le traducteur avec une langue par défaut
     * Charge les traductions depuis la session ou utilise la langue par défaut
     * 
     * @param string $lang Code de langue par défaut
     */
    public function __construct($lang = 'fr') {
        $this->lang = $_SESSION['lang'] ?? $lang;
        $this->loadTranslations();
    }

    /**
     * Charge le fichier de traduction pour la langue courante
     * 
     * @return void
     */
    private function loadTranslations() {
        $file = __DIR__ . "/../translations/{$this->lang}.php";
        if (file_exists($file)) {
            $this->translations = require $file;
        } 
    }

    /**
     * Traduit une clé dans la langue courante
     * 
     * @param string $key Clé de traduction
     * @return string Texte traduit ou clé si traduction non trouvée
     */
    public function translate($key) {
        return $this->translations[$key] ?? $key;
    }

    /**
     * Retourne la langue courante
     * 
     * @return string Code de la langue courante
     */
    public function getCurrentLang() {
        return $this->lang;
    }

    /**
     * Change la langue courante et recharge les traductions
     * 
     * @param string $lang Nouveau code de langue
     * @return void
     */
    public function setLang($lang) {
        $this->lang = $lang;
        $this->loadTranslations();
    }
}
