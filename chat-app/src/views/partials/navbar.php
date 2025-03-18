<?php
/**
 * Template de la barre de navigation
 * @var array $navData Données de configuration de la navigation
 * @var array $navData['homeUrl'] URL de la page d'accueil
 * @var string $navData['appName'] Nom de l'application
 * @var array $navData['menuItems'] Liste des éléments du menu
 * @var array $navData['languages'] Liste des langues disponibles
 * @var string $navData['currentLang'] Code de la langue actuelle
 * @var string $navData['userAvatar'] URL de l'avatar de l'utilisateur
 * @var string $navData['logoutText'] Texte du bouton de déconnexion
 */
?>

<!-- Barre de navigation principale - fixée en haut de la page -->
<nav class="navbar navbar-expand-lg fixed-top navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <!-- Logo et nom de l'application - Lien vers la page d'accueil -->
        <a class="navbar-brand text-primary fw-bold" href="<?= $navData['homeUrl'] ?>">
            <i class="fas fa-comments me-2"></i>
            <span data-i18n="app_name"><?= $navData['appName'] ?></span>
        </a>
        
        <!-- Bouton hamburger pour la version mobile - Active/Désactive le menu -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Conteneur principal de la navigation -->
        <div class="collapse navbar-collapse navbar-collapse-right" id="navbarNav">
            <!-- Menu principal - Liste des liens de navigation -->
            <ul class="navbar-nav me-auto">
                <?php foreach ($navData['menuItems'] as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $item['active'] ? 'active' : '' ?>" 
                           href="<?= $item['url'] ?>"
                           data-i18n="<?= $item['url'] ? str_replace('/', '', $item['url']) : 'home' ?>">
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Section utilisateur - Langue, avatar et déconnexion -->
            <div class="d-flex align-items-center">
                <!-- Menu Chat -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-primary position-relative" 
                            type="button" 
                            id="chatDropdown" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-comments"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" 
                         style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Messages récents</h6>
                        </div>
                        <div class="chat-messages">
                            <a href="#" class="dropdown-item p-2 border-bottom">
                                <div class="d-flex">
                                    <img src="<?= $navData['userAvatar'] ?>" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0">John Doe</h6>
                                            <small class="text-muted">10:07</small>
                                        </div>
                                        <p class="mb-0 small text-truncate">Il y a plein de nouvelles fonctionnalités cool!</p>
                                    </div>
                                </div>
                            </a>
                            <!-- Autres messages... -->
                        </div>
                        <div class="p-2 text-center border-top">
                            <a href="/messages" class="text-decoration-none">Voir tous les messages</a>
                        </div>
                    </div>
                </div>

                <!-- Sélecteur de langue - Menu déroulant des langues disponibles -->
                <div class="dropdown me-3">
                    <button class="btn btn-light dropdown-toggle" type="button" id="languageSelector" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i>
                        <?= $navData['languages'][$navData['currentLang']] ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($navData['languages'] as $code => $name): ?>
                            <li>
                                <a class="dropdown-item <?= $code === $navData['currentLang'] ? 'active' : '' ?>" 
                                   href="#"
                                   data-lang="<?= $code ?>">
                                    <?= $name ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Avatar de l'utilisateur -->
                <img src="<?= $navData['userAvatar'] ?>" 
                     class="rounded-circle me-2" 
                     style="width: 32px; height: 32px;"
                     alt="Avatar utilisateur">
                
                <!-- Bouton de déconnexion -->
                <a href="logout.php" class="btn btn-outline-danger btn-sm" data-i18n="logout">
                    <?= $navData['logoutText'] ?>
                </a>
            </div>
        </div>
    </div>
</nav>
<!-- Espacement pour éviter que le contenu ne soit masqué par la navbar fixe -->
<div style="margin-top: 70px;"></div>

<style>
@media (max-width: 991.98px) {
    .navbar-collapse-right {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 300px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        padding: 0;
        z-index: 1031;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        margin-top: 56px;
    }
    
    .navbar-collapse-right.show {
        transform: translateX(0);
    }

    .navbar-nav {
        padding: 1rem 0;
        width: 100%;
    }

    .navbar-nav .nav-item {
        padding: 0.5rem 1rem;
    }

    .navbar-nav .nav-link {
        padding: 0.75rem 0;
        font-size: 1.1rem;
        border-radius: 8px;
    }

    .navbar-collapse-right .d-flex {
        flex-direction: column;
        width: 100%;
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .navbar-collapse-right .d-flex > * {
        width: 100%;
        margin: 0.5rem 0 !important;
    }

    .navbar-collapse-right .btn,
    .navbar-collapse-right .dropdown-toggle {
        width: 100%;
        text-align: left;
        padding: 0.75rem 1rem;
    }

    .navbar-collapse-right .dropdown-menu {
        width: 100%;
        position: static !important;
        box-shadow: none;
        border: 1px solid #dee2e6;
        margin-top: 0.5rem;
    }

    .navbar-collapse-right img.rounded-circle {
        width: 48px;
        height: 48px;
        margin-bottom: 0.5rem;
    }

    .navbar-collapse-right .btn-outline-danger {
        margin-top: 1rem !important;
    }
}
</style>
