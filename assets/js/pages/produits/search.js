import axios from 'axios';

export default class ProductSearch {
    constructor() {
        this.searchInput = document.getElementById('searchProduct');
        this.tableBody = document.querySelector('table tbody');
        this.noResultsRow = `
            <tr>
                <td colspan="6" class="text-center">Aucun produit trouvé</td>
            </tr>
        `;
        this.currentRequest = null;

        this.init();
    }

    init() {
        if (!this.searchInput) return;
        this.searchInput.addEventListener('input', this.handleSearch.bind(this));
    }

    handleSearch(e) {
        const query = e.target.value.trim();
        const feedbackElement = document.querySelector('.search-feedback');
        
        if (this.currentRequest) {
            this.currentRequest.cancel();
        }

        const cancelToken = axios.CancelToken;
        this.currentRequest = cancelToken.source();

        // Gestion du feedback
        if (query.length > 0 && query.length < 3) {
            feedbackElement.classList.remove('d-none');
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        Veuillez saisir au moins 3 caractères
                    </td>
                </tr>
            `;
            return;
        }

        feedbackElement.classList.add('d-none');

        if (query.length === 0) {
            // Recharger tous les produits
            window.location.reload();
            return;
        }

        if (query.length >= 3) {
            this.performSearch(query, this.currentRequest.token);
        }
    }

    async performSearch(query, cancelToken) {
        try {
            const response = await axios.get(`/api/produits/search?q=${encodeURIComponent(query)}`, {
                cancelToken: cancelToken,
                headers: {
                    'Accept': 'application/json'
                }
            });

            this.updateTable(response.data);
        } catch (error) {
            if (!axios.isCancel(error)) {
                console.error('Erreur de recherche:', error);
                this.tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Une erreur est survenue lors de la recherche
                        </td>
                    </tr>
                `;
            }
        }
    }

    updateTable(products) {
        if (products.length === 0) {
            this.tableBody.innerHTML = this.noResultsRow;
            return;
        }

        this.tableBody.innerHTML = products.map(produit => `
            <tr>
                <td>${produit.id}</td>
                <td>${produit.nom}</td>
                <td>${produit.prixVenteHt} €</td>
                <td>
                    ${produit.prescription === 'oui' 
                        ? '<span class="badge bg-warning">Requise</span>'
                        : '<span class="badge bg-success">Non requise</span>'
                    }
                </td>
                <td>
                    ${produit.tauxRemboursement 
                        ? `<span class="badge bg-info">${produit.tauxRemboursement}%</span>`
                        : '<span class="badge bg-secondary">Non remboursé</span>'
                    }
                </td>
                <td>
                    <div class="btn-group">
                        <a href="/produits/${produit.id}" 
                           class="btn btn-sm btn-outline-info" 
                           title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </a>
                        ${this.isAdmin() ? `
                            <a href="/produits/${produit.id}/edit" 
                               class="btn btn-sm btn-outline-primary" 
                               title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal${produit.id}" 
                                    title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');

        // Réinitialiser les modales de suppression si nécessaire
        if (this.isAdmin()) {
            this.initDeleteModals(products);
        }
    }

    isAdmin() {
        // Vérifier si l'utilisateur est admin (à adapter selon votre logique)
        return document.querySelector('[data-role="ROLE_ADMIN"]') !== null;
    }

    initDeleteModals(products) {
        // Ajouter les modales de suppression
        const modalsContainer = document.getElementById('deleteModalsContainer');
        if (!modalsContainer) return;

        modalsContainer.innerHTML = products.map(produit => `
            <div class="modal fade" id="deleteModal${produit.id}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmer la suppression</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Êtes-vous sûr de vouloir supprimer le produit :</p>
                            <p class="text-center fw-bold">${produit.nom}</p>
                            <p class="text-danger small">Cette action est irréversible.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form action="/produits/${produit.id}/delete" method="post" class="d-inline">
                                <input type="hidden" name="_token" value="${this.getCsrfToken(produit.id)}">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    getCsrfToken(productId) {
        // À adapter selon votre logique de gestion des tokens CSRF
        return document.querySelector(`input[name="_token"][value*="${productId}"]`)?.value || '';
    }
} 