<?php
require_once __DIR__ . '/../core/Translator.php';

/**
 * Contrôleur gérant la navigation principale de l'application
 * Responsable de la logique liée à la barre de navigation
 */
class NavigationController {
    private $translator; 

    public function __construct() {
        // Détection de la langue (à implémenter selon vos besoins)
        $userLang = 'fr'; // Par défaut en français
        $this->translator = new Translator($userLang);
    }

    /**
     * Retourne l'instance du translator
     * @return Translator
     */
    public function getTranslator() {
        return $this->translator;
    }

    /**
     * Récupère les données nécessaires pour la barre de navigation
     * 
     * @return array Données de configuration de la navigation
     */
    public function getNavigationData() {
        // Configuration de base de la navigation
        return [
            'appName' => $this->translator->translate('app_name'),
            'homeUrl' => 'index.php',
            'menuItems' => [
                // Items du menu principal
                [
                    'label' => $this->translator->translate('home'),
                    'url' => 'index.php',
                    'active' => true
                ],
                [
                    'label' => $this->translator->translate('profile'),
                    'url' => 'profile.php',
                    'active' => false 
                ],
                [
                    'label' => $this->translator->translate('login'),
                    'url' => 'login.php',
                    'active' => false 
                ],
                [
                    'label' => $this->translator->translate('settings'),
                    'url' => 'settings.php',
                    'active' => false
                ],
                [
                    'label' => $this->translator->translate('conversation'),
                    'url' => 'conversation.php',
                    'active' => false
                ]
            ],
            // Informations de l'utilisateur connecté
            'userAvatar' => 'https://ui-avatars.com/api/?name=User',
            'logoutText' => $this->translator->translate('logout'),
            'languages' => [
                'fr' => 'Français',
                'en' => 'English',
                'es' => 'Español',
                'zh' => '中文',
                'sw' => 'Kiswahili'
            ],
            'currentLang' => $this->translator->getCurrentLang()
        ];
    }

    /**
     * Affiche la barre de navigation
     * Inclut le template avec les données nécessaires
     */
    public function renderNavbar() {
        $navData = $this->getNavigationData();
        include __DIR__ . '/../views/partials/navbar.php';
    }
}
