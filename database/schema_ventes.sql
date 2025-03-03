
-- Table vente
CREATE TABLE "vente" (
    "vente_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "client_id" INTEGER,
    "user_id" INTEGER NOT NULL,
    "date" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "montant" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "montant_regle" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "a_rembourser" DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    "commentaire" TEXT,
    "is_deleted" BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY ("client_id") REFERENCES "client"("client_id") ON UPDATE CASCADE,
    FOREIGN KEY ("user_id") REFERENCES "user"("user_id") ON UPDATE CASCADE
);

-- Table vente_ordonnance (relation entre ventes et ordonnances)
CREATE TABLE "vente_ordonnance" (
    "vente_id" INTEGER NOT NULL,
    "ordonnance_id" INTEGER NOT NULL,
    PRIMARY KEY ("vente_id", "ordonnance_id"),
    FOREIGN KEY ("vente_id") REFERENCES "vente"("vente_id") ON DELETE CASCADE,
    FOREIGN KEY ("ordonnance_id") REFERENCES "ordonnance"("ordonnance_id") ON DELETE CASCADE
);

-- Table vente_paiement
CREATE TABLE "vente_paiement" (
    "paiement_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "vente_id" INTEGER NOT NULL,
    "mode_paiement" TEXT CHECK("mode_paiement" IN ('especes', 'carte_bleu', 'cheque')),
    "montant" DECIMAL(10,2) NOT NULL,
    "numero_cheque" VARCHAR(50),
    "date_paiement" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "cheque_id" INTEGER,
    FOREIGN KEY ("vente_id") REFERENCES "vente"("vente_id") ON DELETE CASCADE,
    FOREIGN KEY ("cheque_id") REFERENCES "cheque"("cheque_id") ON DELETE SET NULL
);

-- Table vente_produit
CREATE TABLE "vente_produit" (
    "vente_id" INTEGER NOT NULL,
    "produit_id" INTEGER NOT NULL,
    "quantite" INTEGER NOT NULL,
    PRIMARY KEY ("vente_id", "produit_id"),
    FOREIGN KEY ("vente_id") REFERENCES "vente"("vente_id") ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY ("produit_id") REFERENCES "produit"("produit_id") ON UPDATE CASCADE
);

-- Table cheque
CREATE TABLE "cheque" (
    "cheque_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "numero_cheque" VARCHAR(50) NOT NULL,
    "client_id" INTEGER NOT NULL,
    "montant" DECIMAL(10,2) NOT NULL,
    "etat" TEXT CHECK("etat" IN ('en_attente', 'valide', 'refuse')) NOT NULL DEFAULT 'en_attente',
    FOREIGN KEY ("client_id") REFERENCES "client"("client_id")
);

-- Table ordonnance
CREATE TABLE "ordonnance" (
    "ordonnance_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "numero_ordonnance" VARCHAR(50) NOT NULL,
    "image_path" VARCHAR(255),
    "numero_d'ordre" VARCHAR(255) NOT NULL
);

-- Table ordonnance_produit
CREATE TABLE "ordonnance_produit" (
    "ordonnance_id" INTEGER NOT NULL,
    "produit_id" INTEGER NOT NULL,
    PRIMARY KEY ("ordonnance_id", "produit_id"),
    FOREIGN KEY ("ordonnance_id") REFERENCES "ordonnance"("ordonnance_id"),
    FOREIGN KEY ("produit_id") REFERENCES "produit"("produit_id")
);

-- Création des index pour améliorer les performances
CREATE INDEX "idx_vente_client" ON "vente" ("client_id");
CREATE INDEX "idx_vente_user" ON "vente" ("user_id");
CREATE INDEX "idx_vente_paiement_vente" ON "vente_paiement" ("vente_id");
CREATE INDEX "idx_vente_paiement_cheque" ON "vente_paiement" ("cheque_id");
CREATE INDEX "idx_vente_produit_produit" ON "vente_produit" ("produit_id");
CREATE INDEX "idx_cheque_client" ON "cheque" ("client_id");
