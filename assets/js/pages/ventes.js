document.addEventListener('DOMContentLoaded', function() {
    // Capture le token CSRF dès le début et le stocke dans une variable
    const csrfToken = document.getElementById('venteForm')?.getAttribute('data-csrf-token') || 
                     document.querySelector('input[name="_token"]')?.value || '';
    
    // Désactivation du zoom tactile
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) event.preventDefault();
    }, {
        passive: false
    });

    document.addEventListener('touchmove', function(event) {
        if (event.touches.length > 1) event.preventDefault();
    }, {
        passive: false
    });

    // Empêcher le zoom sur double-tap pour le bouton ajouter
    document.getElementById('ajouter-produit').addEventListener('touchend', function(event) {
        event.preventDefault();
        setTimeout(() => this.click(), 100);
    }, {
        passive: false
    });

    // Initialisation des variables globales
    const produitsDiv = document.getElementById('produits');
    const ajouterProduitBtn = document.getElementById('ajouter-produit');
    const montantTotalSpan = document.getElementById('montant_total');
    const montantAReglerSpan = document.getElementById('montant_a_regler');
    const montantRestantSpan = document.getElementById('montant_restant');
    const montantARembourserSpan = document.getElementById('montant_a_rembourser');
    const montantTotalInput = document.createElement('input');
    const montantAReglerInput = document.createElement('input');
    const modesPaiement = document.querySelectorAll('input[name="mode_encaissement[]"]');
    const montantsPaiement = document.querySelectorAll('.montant-paiement');
    const clientSelect = document.getElementById('client-select');
    const venteForm = document.getElementById('venteForm');

    // Configuration des inputs cachés
    montantTotalInput.type = 'hidden';
    montantTotalInput.name = 'montant_total';
    montantAReglerInput.type = 'hidden';
    montantAReglerInput.name = 'montant_a_regler';
    venteForm.appendChild(montantTotalInput);
    venteForm.appendChild(montantAReglerInput);

    // Fonction de calcul de la monnaie
    function calculerMonnaie() {
        const montantEspeces = document.querySelector('input[name="montant_especes"]');
        const montantPayeEspeces = document.querySelector('input[name="montant_paye_especes"]');
        const monnaieSpan = document.querySelector('.monnaie-montant');

        const montantDu = parseFloat(montantEspeces.value) || 0;
        const montantPaye = parseFloat(montantPayeEspeces.value) || 0;
        const monnaie = montantPaye - montantDu;

        monnaieSpan.textContent = monnaie.toFixed(2) + '€';
        monnaieSpan.style.color = monnaie >= 0 ? '#28a745' : '#dc3545';
    }

    // Fonction de mise à jour du montant restant
    function updateMontantRestant() {
        const montantARegler = parseFloat(montantAReglerSpan.textContent) || 0;
        let totalPaye = 0;
        montantsPaiement.forEach(input => {
            if (!input.disabled) {
                totalPaye += parseFloat(input.value) || 0;
            }
        });
        const montantRestant = montantARegler - totalPaye;
        montantRestantSpan.textContent = Math.max(0, montantRestant).toFixed(2) + '€';
    }

    // Gestion des modes de paiement
    modesPaiement.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            const montantInput = montantsPaiement[index];
            montantInput.disabled = !this.checked;

            if (this.checked) {
                if (this.value === 'especes') {
                    const montantPayeEspeces = document.querySelector('input[name="montant_paye_especes"]');
                    montantPayeEspeces.disabled = false;
                    montantInput.value = montantAReglerSpan.textContent.replace('€', '');
                    montantPayeEspeces.value = montantInput.value;
                    calculerMonnaie();
                } else {
                    const montantRestant = parseFloat(montantRestantSpan.textContent) || 0;
                    montantInput.value = montantRestant.toFixed(2);
                }

                if (this.value === 'cheque') {
                    const numeroCheque = document.querySelector('input[name="numero_cheque"]');
                    numeroCheque.disabled = false;
                }
            } else {
                montantInput.value = '';
                if (this.value === 'especes') {
                    const montantPayeEspeces = document.querySelector('input[name="montant_paye_especes"]');
                    montantPayeEspeces.disabled = true;
                    montantPayeEspeces.value = '';
                    document.querySelector('.monnaie-montant').textContent = '0.00€';
                } else if (this.value === 'cheque') {
                    const numeroCheque = document.querySelector('input[name="numero_cheque"]');
                    numeroCheque.value = '';
                    numeroCheque.disabled = true;
                }
            }
            updateMontantRestant();
        });
    });

    // Event listeners pour les montants
    document.querySelector('input[name="montant_especes"]').addEventListener('input', function() {
        const montantPayeEspeces = document.querySelector('input[name="montant_paye_especes"]');
        montantPayeEspeces.value = this.value;
        calculerMonnaie();
        updateMontantRestant();
    });

    document.querySelector('input[name="montant_paye_especes"]').addEventListener('input', function() {
        calculerMonnaie();
        updateMontantRestant();
    });

    montantsPaiement.forEach(input => {
        if (input.name !== 'montant_especes') {
            input.addEventListener('input', updateMontantRestant);
        }
    });

    // Fonction pour mettre à jour les produits disponibles
    function updateAvailableProducts() {
        const selectedProducts = Array.from(document.querySelectorAll('.produit-select'))
            .map(select => select.value)
            .filter(value => value !== '');
        document.querySelectorAll('.produit-select').forEach(select => {
            Array.from(select.options).forEach(option => {
                if (selectedProducts.includes(option.value) && option.value !== select.value) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    }

    // Ajout d'une nouvelle ligne de produit
    ajouterProduitBtn.addEventListener('click', function(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        }
        const produitsContainer = document.querySelector('.produits-container');
        const nouvelleLigne = produitsContainer.querySelector('.produit-ligne').cloneNode(true);

        // Réinitialiser les valeurs
        nouvelleLigne.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('produit-search')) {
                input.value = '';
            } else if (input.closest('.ordonnance-fields')) {
                input.value = '';
                input.removeAttribute('data-toggle');
                input.removeAttribute('title');
            } else if (input.classList.contains('quantite')) {
                input.value = '1';
            }
        });
        nouvelleLigne.querySelector('select').value = '';
        nouvelleLigne.querySelector('.prix-total').textContent = '';
        nouvelleLigne.querySelector('.montant-a-rembourser').textContent = '';
        nouvelleLigne.querySelector('.ordonnance-fields').style.display = 'none';

        attachEventListeners(nouvelleLigne);
        produitsContainer.appendChild(nouvelleLigne);
        updateAvailableProducts();
        return false;
    }, {
        passive: false,
        capture: true
    });

    // Fonction pour attacher les événements à une ligne de produit
    function attachEventListeners(ligne) {
        const select = ligne.querySelector('.produit-select');
        const searchInput = ligne.querySelector('.produit-search');
        const quantiteInput = ligne.querySelector('.quantite');
        const supprimerBtn = ligne.querySelector('.supprimer-produit');
        const ordonnanceFields = ligne.querySelector('.ordonnance-fields');

        // Initialiser la recherche de produits
        if (searchInput) {
            const resultsContainer = document.createElement('div');
            resultsContainer.className = 'produit-results-container';
            searchInput.parentNode.appendChild(resultsContainer);
            
            // Version simplifiée de recherche de produits
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = select.querySelectorAll('option');
                
                // Filtrer les options qui correspondent au terme de recherche
                let filteredOptions = Array.from(options).filter(option => {
                    const text = option.textContent.toLowerCase();
                    return text.includes(searchTerm);
                });
                
                // Afficher les résultats si on a du texte et des résultats
                if (searchTerm && filteredOptions.length > 0) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'block';
                    
                    filteredOptions.forEach(option => {
                        if (option.value) { // Ignore l'option vide
                            const resultItem = document.createElement('div');
                            resultItem.className = 'produit-result-item';
                            resultItem.textContent = option.textContent;
                            resultItem.dataset.value = option.value;
                            
                            resultItem.addEventListener('click', () => {
                                select.value = option.value;
                                searchInput.value = option.textContent;
                                resultsContainer.style.display = 'none';
                                
                                // Déclencher l'événement change sur le select
                                const event = new Event('change');
                                select.dispatchEvent(event);
                            });
                            
                            resultsContainer.appendChild(resultItem);
                        }
                    });
                } else {
                    resultsContainer.style.display = 'none';
                }
            });
            
            // Cacher les résultats quand on clique ailleurs
            document.addEventListener('click', function(event) {
                if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                    resultsContainer.style.display = 'none';
                }
            });
        }

        // Événements de changement de produit
        select.addEventListener('change', () => {
            updatePrixTotal(ligne);
            updateAvailableProducts();
            const selectedOption = select.options[select.selectedIndex];
            ordonnanceFields.style.display =
                selectedOption && selectedOption.dataset.prescription === 'oui' ? 'block' : 'none';
        });

        // Événement de changement de quantité
        quantiteInput.addEventListener('input', function() {
            let quantiteAchetee = parseInt(this.value) || 0;
            if (quantiteAchetee < 1) {
                quantiteAchetee = 1;
                this.value = 1;
            }
            updatePrixTotal(ligne);
        });

        // Événement de suppression
        supprimerBtn.addEventListener('click', () => {
            const toutesLesLignes = document.querySelectorAll('.produit-ligne');
            if (toutesLesLignes.length === 1) {
                resetLigne(ligne);
            } else {
                ligne.remove();
            }
            updateTotals();
            updateAvailableProducts();
        });
    }

    // Fonction de réinitialisation d'une ligne
    function resetLigne(ligne) {
        const select = ligne.querySelector('.produit-select');
        const quantiteInput = ligne.querySelector('.quantite');
        const ordonnanceFields = ligne.querySelector('.ordonnance-fields');
        const searchInput = ligne.querySelector('.produit-search');

        select.value = '';
        quantiteInput.value = '1';
        if (searchInput) searchInput.value = '';
        ligne.querySelector('.prix-total').textContent = '';
        ligne.querySelector('.montant-a-rembourser').textContent = '';
        ordonnanceFields.style.display = 'none';
        ordonnanceFields.querySelectorAll('input').forEach(input => input.value = '');
    }

    // Fonction de mise à jour du prix total
    function updatePrixTotal(ligne) {
        const select = ligne.querySelector('.produit-select');
        const quantiteInput = ligne.querySelector('.quantite');
        const prixTotalSpan = ligne.querySelector('.prix-total');
        const montantARembourserSpan = ligne.querySelector('.montant-a-rembourser');

        if (select.value) {
            const selectedOption = select.options[select.selectedIndex];
            const prix = parseFloat(selectedOption.dataset.prix);
            const quantite = parseInt(quantiteInput.value) || 0;
            const tauxRemboursement = parseFloat(selectedOption.dataset.taux) || 0;

            const total = prix * quantite;
            const remboursement = total * (tauxRemboursement / 100);

            prixTotalSpan.textContent = total.toFixed(2) + '€';
            montantARembourserSpan.textContent = remboursement.toFixed(2) + '€';
        } else {
            prixTotalSpan.textContent = '';
            montantARembourserSpan.textContent = '';
        }
        updateTotals();
    }

    // Fonction de mise à jour des totaux
    function updateTotals() {
        let total = 0;
        let totalRemboursement = 0;

        document.querySelectorAll('.produit-ligne').forEach(ligne => {
            const prixText = ligne.querySelector('.prix-total').textContent;
            const remboursementText = ligne.querySelector('.montant-a-rembourser').textContent;

            if (prixText) total += parseFloat(prixText);
            if (remboursementText) totalRemboursement += parseFloat(remboursementText);
        });

        montantTotalSpan.textContent = total.toFixed(2) + '€';
        montantARembourserSpan.textContent = totalRemboursement.toFixed(2) + '€';
        montantAReglerSpan.textContent = (total - totalRemboursement).toFixed(2) + '€';

        montantTotalInput.value = total.toFixed(2);
        montantAReglerInput.value = (total - totalRemboursement).toFixed(2);

        updateMontantRestant();
    }

    // Fonctions de validation
    function validateProducts() {
        let valid = true;
        document.querySelectorAll('.produit-ligne').forEach(ligne => {
            const select = ligne.querySelector('.produit-select');
            const quantite = ligne.querySelector('.quantite');
            if (!select.value || parseInt(quantite.value) < 1) {
                valid = false;
            }
        });
        return valid;
    }

    function validateOrdonnances() {
        let valid = true;
        document.querySelectorAll('.produit-ligne').forEach(ligne => {
            const select = ligne.querySelector('.produit-select');
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.prescription === 'oui') {
                const numeroOrdonnance = ligne.querySelector('input[name="numero_ordonnance[]"]');
                const numeroOrdre = ligne.querySelector('input[name="numero_ordre[]"]');
                if (!numeroOrdonnance.value || !numeroOrdre.value) valid = false;
            }
        });
        return valid;
    }

    function validatePayment() {
        let montantPaye = 0;
        montantsPaiement.forEach(input => {
            if (!input.disabled) {
                montantPaye += parseFloat(input.value) || 0;
            }
        });
        const montantARegler = parseFloat(montantAReglerSpan.textContent);
        return Math.abs(montantPaye - montantARegler) < 0.01;
    }

    function validateCheque() {
        const chequePaiement = document.querySelector('input[name="mode_encaissement[]"][value="cheque"]');
        const numeroCheque = document.querySelector('input[name="numero_cheque"]');
        if (chequePaiement && chequePaiement.checked) {
            return numeroCheque.value.trim() !== '';
        }
        return true;
    }

    function validateChequePayment() {
        const chequePaiement = document.querySelector('input[name="mode_encaissement[]"][value="cheque"]');
        if (!chequePaiement || !chequePaiement.checked) return true;

        if (clientSelect.value === "0") {
            alert("Un client de passage ne peut pas régler par chèque.");
            return false;
        }

        const selectedOption = clientSelect.options[clientSelect.selectedIndex];
        if (selectedOption.dataset.chequesImpayes === "true") {
            alert("Ce client a des chèques impayés et ne peut pas régler par chèque.");
            return false;
        }
        return true;
    }

    function validateForm() {
        if (!validateProducts()) {
            alert('Veuillez sélectionner au moins un produit avec une quantité valide.');
            return false;
        }
        if (!validateOrdonnances()) {
            alert("Veuillez remplir tous les champs d'ordonnance pour les produits qui en nécessitent.");
            return false;
        }
        if (!validateChequePayment()) return false;
        if (!validatePayment()) {
            alert('Le montant payé doit être égal au montant à régler.');
            return false;
        }
        if (!validateCheque()) {
            alert('Veuillez saisir un numéro de chèque valide.');
            return false;
        }
        return true;
    }

    // Initialisation et soumission du formulaire
    document.querySelectorAll('.produit-ligne').forEach(ligne => {
        attachEventListeners(ligne);
    });
    updateAvailableProducts();
    
    // Recherche client
    const clientSearchInput = document.getElementById('client-search');
    if (clientSearchInput) {
        clientSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (searchTerm.length < 2) return;
            
            // Appel AJAX pour chercher un client
            fetch(`/ventes/api/search-client?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        // Mettre à jour le select avec les résultats
                        const clientSelect = document.getElementById('client');
                        
                        // Conserver l'option "Client de passage"
                        const clientDePassage = clientSelect.querySelector('option[value="0"]');
                        clientSelect.innerHTML = '';
                        clientSelect.appendChild(clientDePassage);
                        
                        // Ajouter les clients trouvés
                        data.results.forEach(client => {
                            const option = document.createElement('option');
                            option.value = client.id;
                            option.textContent = client.nom + ' ' + client.prenom;
                            option.dataset.chequesImpayes = client.cheques_impayes ? 'true' : 'false';
                            clientSelect.appendChild(option);
                        });
                        
                        // Sélectionner le premier résultat
                        if (clientSelect.options.length > 1) {
                            clientSelect.selectedIndex = 1;
                        }
                    }
                })
                .catch(error => console.error('Erreur lors de la recherche de clients:', error));
        });
    }
    
    // Soumission du formulaire
    venteForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        // Préparation des données pour la soumission
        const produitsData = [];
        document.querySelectorAll('.produit-ligne').forEach(ligne => {
            const select = ligne.querySelector('.produit-select');
            if (select.value) {
                const produitId = select.value;
                const quantite = parseInt(ligne.querySelector('.quantite').value) || 1;
                
                const ordonnanceData = {};
                if (select.options[select.selectedIndex].dataset.prescription === 'oui') {
                    const ordonnanceFields = ligne.querySelector('.ordonnance-fields');
                    ordonnanceData.numero = ordonnanceFields.querySelector('input[name="numero_ordonnance[]"]').value;
                    ordonnanceData.numero_ordre = ordonnanceFields.querySelector('input[name="numero_ordre[]"]').value;
                    
                    // Gestion de l'image d'ordonnance (sera traitée côté serveur)
                    const imageInput = ordonnanceFields.querySelector('input[type="file"]');
                    if (imageInput.files.length > 0) {
                        ordonnanceData.image = true;
                    }
                }
                
                produitsData.push({
                    id: produitId,
                    quantite: quantite,
                    ordonnance: Object.keys(ordonnanceData).length > 0 ? ordonnanceData : null
                });
            }
        });
        
        // Collecte des données de paiement
        const paiementsData = [];
        modesPaiement.forEach((checkbox) => {
            if (checkbox.checked) {
                const modePaiement = checkbox.value;
                let montant = 0;
                let numeroCheque = '';
                
                if (modePaiement === 'especes') {
                    montant = parseFloat(document.querySelector('input[name="montant_especes"]').value) || 0;
                } else if (modePaiement === 'carte_bleu') {
                    montant = parseFloat(document.querySelector('input[name="montant_cb"]').value) || 0;
                } else if (modePaiement === 'cheque') {
                    montant = parseFloat(document.querySelector('input[name="montant_cheque"]').value) || 0;
                    numeroCheque = document.querySelector('input[name="numero_cheque"]').value.trim();
                }
                
                paiementsData.push({
                    mode: modePaiement,
                    montant: montant,
                    numero_cheque: numeroCheque
                });
            }
        });
        
        // Construction des données finales
        const formData = {
            client_id: clientSelect.value,
            produits: produitsData,
            paiements: paiementsData,
            montant_total: parseFloat(montantTotalInput.value),
            montant_a_regler: parseFloat(montantAReglerInput.value),
            commentaire: document.getElementById('commentaire').value.trim(),
            creer_facture: document.getElementById('creer_facture').checked ? 1 : 0,
        };
        
        // Envoi des données au serveur
        fetch(venteForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Vente enregistrée avec succès!');
                window.location.href = '/ventes';
            } else {
                alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'enregistrement de la vente:', error);
            alert('Une erreur est survenue lors de l\'enregistrement de la vente.');
        });
    });
    
    // Pour la version avec upload de fichiers (ordonnances), on utiliserait FormData à la place de JSON
    // Pour simplifier cette implémentation, on gère uniquement les données sans fichiers
    // En production, vous devrez ajuster cette partie pour gérer les uploads correctement
});