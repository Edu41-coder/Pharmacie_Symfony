
-- Table des commandes
CREATE TABLE IF NOT EXISTS commande (
  commande_id INTEGER PRIMARY KEY AUTOINCREMENT,
  date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut TEXT CHECK(statut IN ('En attente', 'En cours', 'Livrée', 'Annulée')) DEFAULT 'En attente',
  total DECIMAL(10,2) DEFAULT 0.00
);

-- Table de relation entre commandes et produits
CREATE TABLE IF NOT EXISTS commande_produit (
  commande_id INTEGER NOT NULL,
  produit_id INTEGER NOT NULL,
  quantite INTEGER NOT NULL,
  PRIMARY KEY (commande_id, produit_id),
  FOREIGN KEY (commande_id) REFERENCES commande(commande_id),
  FOREIGN KEY (produit_id) REFERENCES produit(produit_id)
);

-- Créer un index sur produit_id pour optimiser les recherches
CREATE INDEX IF NOT EXISTS idx_commande_produit_produit_id ON commande_produit(produit_id);
