document.addEventListener('DOMContentLoaded', function() {
    // Rechercher le select de clients (présent uniquement dans le formulaire de vente)
    const clientSelect = document.getElementById('client-select');
    
    // Code spécifique pour sélectionner un client dans la page de vente
    if (clientSelect) {
        // Ajouter des event listeners aux résultats de recherche après qu'ils sont créés
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.target.id === 'searchResults') {
                    const resultItems = document.querySelectorAll('.search-result-item');
                    resultItems.forEach(item => {
                        // Ajouter un comportement de clic pour sélectionner le client
                        item.addEventListener('click', function() {
                            const clientId = this.querySelector('a')?.href.split('/').pop();
                            if (clientId && !isNaN(clientId)) {
                                selectClientById(clientId);
                                document.getElementById('searchResults').classList.add('d-none');
                                document.getElementById('searchClient').value = '';
                            }
                        });
                    });
                }
            });
        });
        
        // Observer les changements dans les résultats de recherche
        observer.observe(document.getElementById('searchResults'), { childList: true });
        
        // Fonction pour sélectionner un client par ID
        function selectClientById(clientId) {
            for(let i = 0; i < clientSelect.options.length; i++) {
                if (clientSelect.options[i].value == clientId) {
                    clientSelect.selectedIndex = i;
                    // Déclencher l'événement change
                    const event = new Event('change');
                    clientSelect.dispatchEvent(event);
                    return;
                }
            }
        }
        
        // Bouton pour effacer la sélection
        document.getElementById('clear-client')?.addEventListener('click', function() {
            clientSelect.selectedIndex = 0;
            const event = new Event('change');
            clientSelect.dispatchEvent(event);
        });
    }
});