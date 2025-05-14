document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchClient');
    const searchCriteria = document.getElementById('searchCriteria');
    const searchResults = document.getElementById('searchResults');
    const searchFeedback = document.querySelector('.search-feedback');
    let timeoutId = null;

    if (!searchInput || !searchResults) return;

    // Fonction pour effectuer la recherche
    function performSearch() {
        const query = searchInput.value.trim();
        const criteria = searchCriteria.value;
        
        // Vérifier si la requête est assez longue
        if (query.length < 3) {
            searchResults.classList.add('d-none');
            searchFeedback.classList.remove('d-none');
            return;
        }

        searchFeedback.classList.add('d-none');
        
        // Utiliser l'URL définie dans le template
        fetch(`${searchUrl}?query=${encodeURIComponent(query)}&criteria=${criteria}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Vider les résultats précédents
                searchResults.innerHTML = '';
                
                // Vérifier si data est un tableau (important!)
                if (!Array.isArray(data)) {
                    if (data && data.error) {
                        // Afficher le message d'erreur spécifique si disponible
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'search-result-item error';
                        errorMsg.innerHTML = `Erreur: ${data.error}`;
                        searchResults.appendChild(errorMsg);
                    } else {
                        // Fallback pour tout autre cas
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'search-result-item error';
                        errorMsg.innerHTML = 'Erreur lors de la recherche';
                        searchResults.appendChild(errorMsg);
                    }
                    searchResults.classList.remove('d-none');
                    return; // Sortir de la fonction
                }
                
                // Le reste du code pour traiter le tableau de résultats
                if (data.length === 0) {
                    // Aucun résultat
                    const noResult = document.createElement('div');
                    noResult.className = 'search-result-item no-result';
                    noResult.innerHTML = 'Aucun client trouvé';
                    searchResults.appendChild(noResult);
                } else {
                    // Afficher les résultats
                    data.forEach(client => {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'search-result-item';
                        
                        // Construction du HTML pour chaque résultat
                        resultItem.innerHTML = `
                            <div class="client-info">
                                <div class="client-name">
                                    <strong>${client.nom} ${client.prenom}</strong>
                                </div>
                                <div class="client-details">
                                    ${client.telephone ? `<span class="me-2"><i class="fas fa-phone"></i> ${client.telephone}</span>` : ''}
                                    ${client.numeroCarteVitale ? `<span><i class="fas fa-id-card"></i> ${client.numeroCarteVitale}</span>` : ''}
                                </div>
                            </div>
                            <div class="client-actions">
                                <a href="${searchUrl.substring(0, searchUrl.lastIndexOf('/'))}/${client.id}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        `;
                        
                        // Ajout d'un badge pour les chèques impayés s'il y en a
                        if (client.chequesImpayes) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-danger ms-2';
                            badge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Impayés';
                            resultItem.querySelector('.client-name').appendChild(badge);
                        }
                        
                        searchResults.appendChild(resultItem);
                    });
                }
                
                // Afficher les résultats
                searchResults.classList.remove('d-none');
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                searchResults.innerHTML = '<div class="search-result-item error">Erreur lors de la recherche</div>';
                searchResults.classList.remove('d-none');
            });
    }

    // Événement sur l'input de recherche
    searchInput.addEventListener('input', function() {
        // Effacer le timeout précédent
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        // Si le champ est vide, cacher les résultats
        if (this.value.trim() === '') {
            searchResults.classList.add('d-none');
            searchFeedback.classList.add('d-none');
            return;
        }
        
        // Si moins de 3 caractères, afficher le message
        if (this.value.trim().length < 3) {
            searchResults.classList.add('d-none');
            searchFeedback.classList.remove('d-none');
            return;
        }
        
        // Attendre que l'utilisateur arrête de taper avant de lancer la recherche
        timeoutId = setTimeout(performSearch, 300);
    });

    // Événement sur le changement de critère
    searchCriteria.addEventListener('change', function() {
        if (searchInput.value.trim().length >= 3) {
            performSearch();
        }
    });

    // Cacher les résultats quand on clique ailleurs
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.classList.add('d-none');
        }
    });

    // Empêcher la soumission du formulaire
    searchInput.closest('form')?.addEventListener('submit', function(e) {
        e.preventDefault();
    });
});