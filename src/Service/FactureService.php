<?php
// src/Service/FactureService.php 
namespace App\Service;

use App\Entity\Vente;
use MongoDB\Client;
use MongoDB\Collection;

class FactureService
{
    private Client $client;
    private Collection $collection;

    public function __construct(string $mongoUrl = 'mongodb://localhost:27017', string $databaseName = 'pharma_symfo')
    {
        $this->client = new Client($mongoUrl);
        $this->collection = $this->client->selectDatabase($databaseName)->selectCollection('factures');
    }

    public function saveFacture(Vente $vente, array $produitsData, array $paiementsData): bool
    {
        try {
            // Document complet à sauvegarder
            $document = $this->createFactureDocument($vente, $produitsData, $paiementsData);
            
            // Insertion dans MongoDB
            $result = $this->collection->insertOne($document);
            
            return $result->isAcknowledged();
        } catch (\Exception $e) {
            // Log l'erreur pour débogage
            error_log('MongoDB Error: ' . $e->getMessage());
            return false;
        }
    }

    public function getFactureByVenteId(int $venteId)
    {
        return $this->collection->findOne(['venteId' => $venteId]);
    }

    private function createFactureDocument(Vente $vente, array $produitsData, array $paiementsData): array
    {
        // Données de la vente
        $venteData = [
            'vente_id' => $vente->getId(),
            'client_id' => $vente->getClient() ? $vente->getClient()->getId() : 0,
            'client_nom' => $vente->getClient() ? $vente->getClient()->getNom() : 'Client de passage',
            'client_prenom' => $vente->getClient() ? $vente->getClient()->getPrenom() : '',
            'date_vente' => $vente->getDate()->format('Y-m-d H:i:s'),
            'montant_total' => $vente->getMontant(),
            'montant_regle' => $vente->getMontantRegle(),
            'montant_a_rembourser' => $vente->getARembourser(),
            'commentaire' => $vente->getCommentaire() ?? ''
        ];
        
        // Préparation des produits
        $produits = [];
        foreach ($produitsData as $produitData) {
            $produit = $produitData['produit'];
            $produits[] = [
                'produit_id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'quantite' => $produitData['quantite'],
                'prix_unitaire' => $produit->getPrixVenteTtc(),
                'montant_total' => round($produit->getPrixVenteTtc() * $produitData['quantite'], 2),
                'taux_remboursement' => $produit->getTauxRemboursement(),
                'montant_a_rembourser' => round($produitData['montant_a_rembourser'], 2)
            ];
        }
        
        return [
            'venteId' => $vente->getId(),
            'vente' => $venteData,
            'produits' => $produits,
            'paiements' => $paiementsData,
            'createdAt' => date('d-m-Y_H-i-s')
        ];
    }
}