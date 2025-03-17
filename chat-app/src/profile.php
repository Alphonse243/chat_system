<?php
session_start();
require_once __DIR__ . '/controllers/NavigationController.php';
$navController = new NavigationController();
$translator = $navController->getTranslator();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Modern Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-image-container {
            transition: transform 0.3s ease;
        }

        .profile-image-container:hover {
            transform: scale(1.05);
        }

        .form-control, .form-select {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            transform: translateY(-2px);
        }

        .btn-primary {
            transition: all 0.3s ease;
            transform: scale(1);
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container.py-5 {
                padding: 1rem !important;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .profile-image-container img {
                width: 120px !important;
                height: 120px !important;
            }

            .btn-primary {
                width: 100%;
                margin-top: 1rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .profile-image-container img {
                width: 100px !important;
                height: 100px !important;
            }

            .card {
                border-radius: 0 !important;
                margin: -1rem;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control, .form-select {
                font-size: 0.9rem;
                padding: 0.5rem;
            }
        }

        /* Amélioration de l'accessibilité tactile */
        @media (hover: none) {
            .form-control:focus, .form-select:focus {
                transform: none;
            }

            .btn-primary:active {
                transform: scale(0.98);
            }

            .profile-image-container:active {
                transform: scale(0.98);
            }
        }

        /* Supprimer ces styles */
        .cover-container,
        .cover-image,
        .profile-picture {
            display: none;
        }

        .profile-nav {
            border-top: 1px solid #ddd;
            background: #fff;
            padding: 0;
        }

        .profile-nav .nav-link {
            padding: 15px 25px;
            color: #65676B;
            font-weight: 600;
        }

        .profile-nav .nav-link.active {
            color: #1876F2;
            border-bottom: 3px solid #1876F2;
        }

        .profile-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            color: #050505;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .friend-item img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body style="background-color: #f0f2f5;">
    <?php
    $navController->renderNavbar();
    ?>

    <div class="container-fluid px-4">
        <div class="text-center mb-4 fade-in">
            <h1 class="mb-1 mt-5">John Doe</h1>
            <p class="text-muted">Frontend Developer</p>
        </div>

        <ul class="nav profile-nav justify-content-center mb-4 fade-in" style="--delay: 0.2s">
            <li class="nav-item">
                <a class="nav-link active" href="#"><?= $translator->translate('Posts') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('About') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('Friends') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><?= $translator->translate('Photos') ?></a>
            </li>
        </ul>

        <div class="row">
            <div class="col-md-3 fade-in" style="--delay: 0.4s">
                <div class="profile-section">
                    <h2 class="section-title"><?= $translator->translate('About') ?></h2>
                    <div class="mb-3">
                        <i class="fas fa-briefcase me-2"></i> <?= $translator->translate('Works at') ?> Facebook
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-graduation-cap me-2"></i> <?= $translator->translate('Studied at') ?> MIT
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-home me-2"></i> <?= $translator->translate('Lives in') ?> New York
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-heart me-2"></i> <?= $translator->translate('Single') ?>
                    </div>
                </div>

                <div class="profile-section">
                    <h2 class="section-title"><?= $translator->translate('Friends') ?></h2>
                    <div class="friends-grid">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                        <div class="friend-item">
                            <img src="https://ui-avatars.com/api/?name=Friend+<?= $i ?>" alt="Friend <?= $i ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9 fade-in" style="--delay: 0.6s">
                <div class="profile-section mb-4">
                    <form id="post-form">
                        <textarea class="form-control mb-3" rows="3" placeholder="<?= $translator->translate("What's on your mind?") ?>"></textarea>
                        <button type="submit" class="btn btn-primary"><?= $translator->translate('Post') ?></button>
                    </form>
                </div>

                <!-- Posts Section -->
                <div class="profile-section">
                    <!-- existing posts content -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <script type="module" src="js/app.js"></script>
    <script type="module" src="js/languageManager.js"></script>
    <script src="js/profile.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            if (elements && elements.length > 0) {
                elements.forEach(element => {
                    if (element) {
                        const delay = element.style.getPropertyValue('--delay') || '0s';
                        element.style.animationDelay = delay;
                    }
                });
            }
        });
    </script>
</body>
</html>
