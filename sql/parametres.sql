
-- Table des paramètres
CREATE TABLE IF NOT EXISTS parametres (
  nom TEXT NOT NULL PRIMARY KEY,
  valeur TEXT NOT NULL
);

-- Insertion de la valeur de TVA par défaut
INSERT OR IGNORE INTO parametres (nom, valeur) VALUES ('TVA', '5');
