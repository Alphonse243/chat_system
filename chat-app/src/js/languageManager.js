document.addEventListener('DOMContentLoaded', function() {
    const languageLinks = document.querySelectorAll('[data-lang]');
    
    languageLinks.forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const lang = this.dataset.lang;
            
            try {
                // Change la langue
                const response = await fetch('/chat-system/chat-app/src/ajax/change-language.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ lang: lang })
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const translationsResponse = await fetch(`/chat-system/chat-app/src/ajax/get-translations.php?lang=${lang}`);
                const result = await translationsResponse.json();

                if (!result.success) throw new Error(result.error);

                const translations = result.data;

                // Mise à jour des éléments traduits
                document.querySelectorAll('[data-i18n]').forEach(element => {
                    const key = element.getAttribute('data-i18n');
                    if (translations[key]) {
                        if (element.tagName.toLowerCase() === 'input') {
                            element.placeholder = translations[key];
                        } else {
                            element.innerHTML = translations[key];
                        }
                    }
                });

                // Mise à jour du sélecteur de langue
                document.querySelector('#languageSelector').innerHTML = 
                    `<i class="fas fa-globe me-1"></i> ${this.textContent}`;

            } catch (error) {
                console.error('Translation error:', error);
            }
        });
    });
});
