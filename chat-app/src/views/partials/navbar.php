<?php
/**
 * Template de la barre de navigation
 * @var array $navData Données de configuration de la navigation
 */
?>
<!-- Barre de navigation principale -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-3">
    <div class="container-fluid">
        <!-- Logo et nom de l'application -->
        <a class="navbar-brand text-primary fw-bold" href="<?= $navData['homeUrl'] ?>">
            <i class="fas fa-comments me-2"></i>
            <span data-i18n="app_name"><?= $navData['appName'] ?></span>
        </a>
        
        <!-- Bouton hamburger pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Contenu de la navigation -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menu principal -->
            <ul class="navbar-nav me-auto">
                <?php foreach ($navData['menuItems'] as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $item['active'] ? 'active' : '' ?>" 
                           href="<?= $item['url'] ?>"
                           data-i18n="<?= str_replace('/', '', $item['url']) || 'home' ?>">
                            <?= $item['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Section utilisateur -->
            <div class="d-flex align-items-center">
                <!-- Sélecteur de langue -->
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
                <img src="<?= $navData['userAvatar'] ?>" 
                     class="rounded-circle me-2" 
                     style="width: 32px; height: 32px;"
                     alt="Avatar utilisateur">
                <button class="btn btn-outline-danger btn-sm" data-i18n="logout"><?= $navData['logoutText'] ?></button>
            </div>
        </div>
    </div>
</nav>
