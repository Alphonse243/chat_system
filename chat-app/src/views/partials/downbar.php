<style>
    /* Styles améliorés pour la liste des catégories */
    .widget-category-2 {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        margin-bottom: 8px;
        border-radius: 8px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .category-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .category-link {
        display: flex;
        align-items: center;
        padding: 12px;
        text-decoration: none;
        color: #333;
    }

    .category-icon {
        width: 24px;
        height: 24px;
        margin-right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-icon img {
        width: 100%;
        height: auto;
    }

    .category-info {
        flex: 1;
    }

    .category-name {
        display: block;
        font-weight: 500;
        font-size: 14px;
        color: #212529;
    }

    .product-count {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }

    /* Style actif pour la catégorie sélectionnée */
    .category-item.active {
        background: #e9ecef;
        border-left: 3px solid #0d6efd;
    }

    .category-item.active .category-name {
        color: #0d6efd;
        font-weight: 600;
    }

    /* Styles pour le panier */
    .add-cart .in-cart {
        background-color: #28a745;
        color: white;
    }
</style>

<nav class="navbar fixed-bottom bg-white d-md-none" style="min-height: auto;">
        <div class="container-fluid px-1 py-1">
            <div class="row w-100 text-center g-0">
                <!-- Accueil -->
                <div class="col">
                    <a href="index.php" class="text-decoration-none text-secondary">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-house" style="font-size: 1.1rem;"></i>
                            <small style="font-size: 0.7rem;">Accueil</small>
                        </div>
                    </a>
                </div>
    
                <!-- Catégories -->
                <div class="col">
                    <a href="" class="text-decoration-none text-secondary">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-grid" style="font-size: 1.1rem;"></i>
                            <small style="font-size: 0.7rem;">Catégories</small>
                        </div>
                    </a>
                </div>
    
                <!-- Panier -->
                <div class="col">
                    <a href="" class="text-decoration-none text-secondary position-relative">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-cart" style="font-size: 1.1rem;"></i>
                            <small style="font-size: 0.7rem;">Panier</small>
                            <span class="position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger cart-count" style="font-size: 0.65rem; padding: 0.2em 0.4em;">
                               4
                            </span>
                        </div>
                    </a>
                </div> 
    
                <!-- Compte -->
                <div class="col">
                    <a href="profile.php" class="text-decoration-none text-secondary">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-person" style="font-size: 1.1rem;"></i>
                            <small style="font-size: 0.7rem;">Compte</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </nav> 