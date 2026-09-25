/**
 * CITY LIBRARY - Script Principal
 * Ce fichier gère les animations de compteurs, les filtres et les interactions.
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. GESTION DU COMPTEUR DE STATISTIQUES (Dashboard) ---
    // On cherche les titres h3 dans les cartes du dashboard
    const stats = document.querySelectorAll('.card h3');
    
    if (stats.length > 0) {
        stats.forEach(stat => {
            const value = +stat.innerText;
            // On vérifie que c'est bien un nombre
            if (!isNaN(value)) {
                let count = 0;
                const updateCount = () => {
                    const speed = value / 50; // Ajuste la vitesse ici
                    if (count < value) {
                        count = Math.ceil(count + speed);
                        stat.innerText = count;
                        setTimeout(updateCount, 30);
                    } else {
                        stat.innerText = value;
                    }
                };
                updateCount();
            }
        });
    }

    // --- 2. FILTRAGE DYNAMIQUE DES COLLECTIONS (Recherche) ---
    // Cette fonction peut être appelée par des boutons ou des menus
    window.filtrerCategorie = function(nomCategorie) {
        const cards = document.querySelectorAll('.collection-card');
        
        if (cards.length > 0) {
            cards.forEach(card => {
                const cat = card.getAttribute('data-category');
                if (nomCategorie === 'tous' || cat === nomCategorie) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    };

    // --- 3. SÉCURISATION DES ÉVÉNEMENTS (Correction de l'erreur Console) ---
    // Exemple : Bouton de soumission de recherche (s'il possède l'id 'searchBtn')
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            console.log("Action de recherche détectée");
        });
    }

    // Exemple : Gestion du bouton 'My Account' s'il y a une interaction JS prévue
    const accountBtn = document.querySelector('.btn-account');
    if (accountBtn) {
        accountBtn.addEventListener('mouseenter', () => {
            accountBtn.style.boxShadow = '0 0 15px var(--gold)';
        });
        accountBtn.addEventListener('mouseleave', () => {
            accountBtn.style.boxShadow = 'none';
        });
    }

});

/**
 * Petite animation CSS ajoutée dynamiquement pour le filtrage
 */
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);