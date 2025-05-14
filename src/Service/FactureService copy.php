<?php
// src/Service/FactureService.php
namespace App\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use MongoDB\Collection;
use App\Entity\Vente;
use App\Entity\Produit;

class FactureService
{
    private $mongoClient;
    private $database;

    public function __construct(string $mongoUrl, string $databaseName)
    {
        $this->mongoClient = new Client($mongoUrl);
        $this->database = $this->mongoClient->selectDatabase($databaseName);
    }

    /**
     * Enregistre une facture pour une vente dans MongoDB
     */
    public function saveFacture(Vente $vente, array $produitsData, array $paiementsData): bool
    {
        $collectionName = 'facture_' . time();
        $collection = $this->database->selectCollection($collectionName);
        
        // Construire les données de la vente
        $venteData = [
            'vente_id' => $vente->getId(),
            'client_id' => $vente->getClient() ? $vente->getClient()->getId() : 0,
            'client_nom' => $vente->getClient() ? $vente->getClient()->getNom() : 'Client de passage',
            'client_prenom' => $vente->getClient() ? $vente->getClient()->getPrenom() : '',
            'date_vente' => $vente->getDate()->format('Y-m-d H:i:s'),  // Méthode correcte
            'montant_total' => $vente->getMontant(),                    // Méthode correcte
            'montant_regle' => $vente->getMontantRegle(),
            'montant_a_rembourser' => $vente->getARembourser(),          // Méthode correcte
            'commentaire' => $vente->getCommentaire() ?? ''
        ];

        $facture = [
            'vente' => $venteData,
            'produits' => [],
            'paiements' => array_map(function ($paiement) {
                $paiementData = [
                    'mode' => $paiement['mode'],
                    'montant' => round($paiement['montant'], 2)
                ];
                if (isset($paiement['numero_cheque'])) {
                    $paiementData['numero_cheque'] = $paiement['numero_cheque'];
                }
                return $paiementData;
            }, $paiementsData),
            'createdAt' => date('d-m-Y_H-i-s')
        ];

        // Ajouter les produits
        foreach ($produitsData as $produitData) {
            $produit = $produitData['produit']; // Supposons que c'est un objet Produit
            
            $facture['produits'][] = [
                'produit_id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'quantite' => $produitData['quantite'],
                'prix_unitaire' => $produit->getPrixVenteTtc(),
                'montant_total' => round($produit->getPrixVenteTtc() * $produitData['quantite'], 2),
                'taux_remboursement' => $produit->getTauxRemboursement(),
                'montant_a_rembourser' => round($produitData['montant_a_rembourser'], 2)
            ];
        }

        try {
            $result = $collection->insertOne($facture);
            return $result->getInsertedCount() > 0;
        } catch (\Exception $e) {
            // Log de l'erreur
            return false;
        }
    }

    /**
     * Récupère les collections de factures triées par date de création
     */
    public function getSortedFactures(): array
    {
        $collections = [];
        foreach ($this->database->listCollections() as $collection) {
            $collectionName = $collection->getName();
            if (strpos($collectionName, 'facture_') === 0) {
                $facture = $this->database->selectCollection($collectionName)->findOne();
                if ($facture && isset($facture['createdAt'])) {
                    $dateTime = \DateTime::createFromFormat('d-m-Y_H-i-s', $facture['createdAt']);
                    if ($dateTime) {
                        $collections[$collectionName] = $dateTime->getTimestamp();
                    }
                }
            }
        }
        
        arsort($collections);
        return array_keys($collections);
    }

    /**
     * Charge une facture spécifique
     */
    public function loadFacture(string $factureId): ?array
    {
        return $this->database->selectCollection($factureId)->findOne();
    }

    /**
     * Récupère la dernière facture enregistrée
     */
    public function getLastFacture(): ?array
    {
        $sortedFactures = $this->getSortedFactures();
        if (!empty($sortedFactures)) {
            return $this->loadFacture($sortedFactures[0]);
        }
        return null;
    }

    /**
     * Trouve une facture par l'ID de vente
     */
    public function getFactureByVenteId($venteId): ?array
    {
        foreach ($this->getSortedFactures() as $collectionName) {
            $facture = $this->database->selectCollection($collectionName)->findOne(['vente.vente_id' => $venteId]);
            if ($facture) {
                return $facture;
            }
        }
        return null;
    }
}