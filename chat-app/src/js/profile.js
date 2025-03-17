document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    const profileImage = document.getElementById('profile-image');
    
    // Gestion des animations
    initializeAnimations();
    
    // Gestion du formulaire
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // TODO: Ajouter la logique de soumission du formulaire
        });
    }
    
    // Gestion de l'upload d'image
    if (profileImage) {
        profileImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.profile-image-container img');
                    if (img) {
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Gestion du changement de couverture
    const coverImage = document.querySelector('.cover-image');
    if (coverImage) {
        coverImage.addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = e => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => coverImage.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            };
            input.click();
        });
    }

    // Gestion de la navigation
    const navLinks = document.querySelectorAll('.profile-nav .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            // TODO: Implémenter le changement de section
        });
    });
});

function initializeAnimations() {
    const elements = document.querySelectorAll('.fade-in');
    elements.forEach(element => {
        const delay = element.style.getPropertyValue('--delay') || '0s';
        element.style.animationDelay = delay;
    });
}
