<?php
// src/Service/FactureService.php
namespace App\Service;

use App\Document\Facture;
use App\Entity\Vente;
use Doctrine\ODM\MongoDB\DocumentManager;

class FactureService
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function saveFacture(Vente $vente, array $produitsData, array $paiementsData): bool
    {
        try {
            // Création d'un document Facture
            $facture = new Facture();
            $facture->setVenteId($vente->getId());
            
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
            $facture->setVente($venteData);
            
            // Traitement des produits
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
            $facture->setProduits($produits);
            
            // Traitement des paiements
            $facture->setPaiements($paiementsData);
            
            // Date de création
            $facture->setCreatedAt(date('d-m-Y_H-i-s'));
            
            // Persistance
            $this->documentManager->persist($facture);
            $this->documentManager->flush();
            
            return true;
        } catch (\Exception $e) {
            // Log de l'erreur
            return false;
        }
    }

    public function getFactureByVenteId(int $venteId)
    {
        return $this->documentManager
            ->createQueryBuilder(Facture::class)
            ->field('venteId')->equals($venteId)
            ->getQuery()
            ->getSingleResult();
    }
}