<?php

namespace App\Service;

use App\Entity\Vente;
use App\Entity\VenteProduit;
use App\Entity\VentePaiement;
use App\Entity\Cheque;
use App\Entity\Ordonnance;
use App\Entity\Produit;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\VenteRepository;
use App\Repository\ProduitRepository;
use App\Repository\InventaireRepository;
use App\Repository\ChequeRepository;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
// Remplacer l'importation de Security par la bonne selon votre version de Symfony
use Symfony\Bundle\SecurityBundle\Security; // Pour Symfony 6+
// use Symfony\Component\Security\Core\Security; // Pour Symfony < 6
use Doctrine\ORM\QueryBuilder;

class VenteService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VenteRepository $venteRepository,
        private ProduitRepository $produitRepository,
        private InventaireRepository $inventaireRepository,
        private ChequeRepository $chequeRepository,
        private OrdonnanceRepository $ordonnanceRepository,
        private Security $security
    ) {}

    public function processVente(array $data): Vente
    {
        $this->entityManager->beginTransaction();
        
        try {
            // 1. Créer la vente
            $vente = new Vente();
            $this->hydrateVente($vente, $data);
            
            // 2. Ajouter les produits
            $montantTotal = 0;
            $montantARembourser = 0;
            
            foreach ($data['produits'] as $produitData) {
                $produit = $this->produitRepository->find($produitData['id']);
                if (!$produit || $produit->isDeleted()) {
                    throw new \Exception("Produit invalide");
                }
                
                $quantite = (int)$produitData['quantite'];
                if ($quantite <= 0) {
                    throw new \Exception("Quantité invalide");
                }
                
                // Vérifier le stock
                $inventaire = $this->inventaireRepository->findOneBy(['produit' => $produit]);
                if (!$inventaire || $inventaire->getStock() < $quantite) {
                    throw new \Exception("Stock insuffisant pour le produit: " . $produit->getNom());
                }
                
                // Calculer le prix (TTC)
                $prixHT = (float)$produit->getPrixVenteHt();
                $tva = isset($data['tva']) ? (float)$data['tva'] : 20.0; // Valeur par défaut
                $prixTTC = $prixHT * (1 + ($tva / 100));
                $montantProduit = $prixTTC * $quantite;
                
                // Ajouter montant au total de la vente
                $montantTotal += $montantProduit;
                
                // Calculer le remboursement si applicable
                if ($produit->getTauxRemboursement() > 0) {
                    $tauxRemboursement = (float)$produit->getTauxRemboursement();
                    $montantRemboursement = $montantProduit * ($tauxRemboursement / 100);
                    $montantARembourser += $montantRemboursement;
                }
                
                // Créer l'objet VenteProduit
                $venteProduit = new VenteProduit();
                $venteProduit->setProduit($produit);
                $venteProduit->setVente($vente);
                $venteProduit->setQuantite($quantite);
                
                $vente->addVenteProduit($venteProduit);
                
                // Mise à jour du stock
                $inventaire->setStock($inventaire->getStock() - $quantite);
                $this->entityManager->persist($inventaire);
                
                // Traitement des ordonnances pour les produits sous prescription
                if ($produit->getPrescription() === 'oui' && isset($produitData['ordonnance'])) {
                    $this->processOrdonnance($vente, $produit, $produitData['ordonnance']);
                }
            }
            
            // Mise à jour des montants de la vente
            $vente->setMontant((string)$montantTotal);
            $vente->setARembourser((string)$montantARembourser);
            
            // 3. Traiter les paiements
            $montantRegle = 0;
            
            if (isset($data['paiements']) && is_array($data['paiements'])) {
                foreach ($data['paiements'] as $paiementData) {
                    $montantPaiement = (float)$paiementData['montant'];
                    $modePaiement = $paiementData['mode'];
                    
                    $ventePaiement = new VentePaiement();
                    $ventePaiement->setVente($vente);
                    $ventePaiement->setModePaiement($modePaiement);
                    $ventePaiement->setMontant((string)$montantPaiement);
                    $ventePaiement->setDatePaiement(new \DateTime());
                    
                    // Traitement spécifique pour les chèques
                    if ($modePaiement === 'cheque') {
                        if (!isset($paiementData['numero_cheque']) || empty($paiementData['numero_cheque'])) {
                            throw new \Exception("Numéro de chèque requis");
                        }
                        
                        $ventePaiement->setNumeroCheque($paiementData['numero_cheque']);
                        
                        // Créer le chèque si un client est associé
                        if ($vente->getClient()) {
                            $cheque = new Cheque();
                            $cheque->setNumeroCheque($paiementData['numero_cheque']);
                            $cheque->setClient($vente->getClient());
                            $cheque->setMontant((string)$montantPaiement);
                            $cheque->setEtat(Cheque::ETAT_EN_ATTENTE);
                            
                            $this->entityManager->persist($cheque);
                            $ventePaiement->setCheque($cheque);
                        }
                    }
                    
                    $vente->addVentePaiement($ventePaiement);
                    $montantRegle += $montantPaiement;
                }
            }
            
            $vente->setMontantRegle((string)$montantRegle);
            
            // Persistance de la vente et validation
            $this->entityManager->persist($vente);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $vente;
            
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function processOrdonnance(Vente $vente, Produit $produit, array $ordonnanceData): void
    {
        // Chercher si l'ordonnance existe déjà
        $ordonnance = null;
        
        if (isset($ordonnanceData['id']) && $ordonnanceData['id']) {
            $ordonnance = $this->ordonnanceRepository->find($ordonnanceData['id']);
        }
        
        // Si l'ordonnance n'existe pas, la créer
        if (!$ordonnance && isset($ordonnanceData['numero']) && isset($ordonnanceData['numero_ordre'])) {
            $ordonnance = new Ordonnance();
            $ordonnance->setNumeroOrdonnance($ordonnanceData['numero']);
            $ordonnance->setNumeroDOrdre($ordonnanceData['numero_ordre']);
            
            // Traiter l'image si présente
            if (isset($ordonnanceData['image_path'])) {
                $ordonnance->setImagePath($ordonnanceData['image_path']);
            }
            
            $ordonnance->addProduit($produit);
            $this->entityManager->persist($ordonnance);
        }
        
        // Associer l'ordonnance à la vente
        if ($ordonnance) {
            $vente->addOrdonnance($ordonnance);
        }
    }

    private function hydrateVente(Vente $vente, array $data): void
    {
        // Client
        if (isset($data['client_id']) && $data['client_id']) {
            $client = $this->entityManager->getRepository(Client::class)->find($data['client_id']);
            if ($client) {
                $vente->setClient($client);
            }
        }
        
        // User (utilisateur connecté)
        $vente->setUser($this->security->getUser());
        
        // Date
        $vente->setDate(new \DateTime());
        
        // Commentaire
        if (isset($data['commentaire'])) {
            $vente->setCommentaire($data['commentaire']);
        }
    }
    
    public function deleteVente(Vente $vente): void
    {
        $this->venteRepository->softDelete($vente);
    }
    
    public function createSortedQueryBuilder(
        string $sortField = 'date', 
        string $sortOrder = 'DESC',
        ?string $dateDebut = null,
        ?string $dateFin = null
    ): QueryBuilder {
        $qb = $this->venteRepository->createSortedQueryBuilder($sortField, $sortOrder);
        
        if ($dateDebut) {
            $qb->andWhere('v.date >= :dateDebut')
               ->setParameter('dateDebut', new \DateTime($dateDebut . ' 00:00:00'));
        }
        
        if ($dateFin) {
            $qb->andWhere('v.date <= :dateFin')
               ->setParameter('dateFin', new \DateTime($dateFin . ' 23:59:59'));
        }
        
        return $qb;
    }
    
    public function getProduitsVente(Vente $vente): array
    {
        $result = [];
        foreach ($vente->getVenteProduits() as $venteProduit) {
            $produit = $venteProduit->getProduit();
            $result[] = [
                'produit' => $produit,
                'quantite' => $venteProduit->getQuantite(),
                'prix_unitaire' => $produit->getPrixVenteHt(),
                'total' => $produit->getPrixVenteHt() * $venteProduit->getQuantite()
            ];
        }
        return $result;
    }
    
    public function getPaiementsVente(Vente $vente): array
    {
        return $vente->getVentePaiements()->toArray();
    }
    
    public function getStatistiquesVentes(?string $dateDebut = null, ?string $dateFin = null): array
    {
        // Convertir les dates en objets DateTime
        $debut = $dateDebut ? new \DateTime($dateDebut . ' 00:00:00') : null;
        $fin = $dateFin ? new \DateTime($dateFin . ' 23:59:59') : null;
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u.id, u.nom, u.prenom, SUM(v.montant) as total_ventes')
           ->from(Vente::class, 'v')
           ->join('v.user', 'u')
           ->where('v.isDeleted = :isDeleted')
           ->setParameter('isDeleted', false)
           ->groupBy('u.id, u.nom, u.prenom')
           ->orderBy('total_ventes', 'DESC');
        
        if ($debut && $fin) {
            $qb->andWhere('v.date BETWEEN :debut AND :fin')
               ->setParameter('debut', $debut)
               ->setParameter('fin', $fin);
        } elseif ($debut) {
            $qb->andWhere('v.date >= :debut')
               ->setParameter('debut', $debut);
        } elseif ($fin) {
            $qb->andWhere('v.date <= :fin')
               ->setParameter('fin', $fin);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function getMontantTotalVentes(?string $dateDebut = null, ?string $dateFin = null): float
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('SUM(v.montant)')
           ->from(Vente::class, 'v')
           ->where('v.isDeleted = :isDeleted')
           ->setParameter('isDeleted', false);
        
        if ($dateDebut && $dateFin) {
            $qb->andWhere('v.date BETWEEN :debut AND :fin')
               ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'))
               ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }
        
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result ? (float)$result : 0.0;
    }
    
    public function getMontantTotalRemboursements(?string $dateDebut = null, ?string $dateFin = null): float
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('SUM(v.aRembourser)')
           ->from(Vente::class, 'v')
           ->where('v.isDeleted = :isDeleted')
           ->setParameter('isDeleted', false);
        
        if ($dateDebut && $dateFin) {
            $qb->andWhere('v.date BETWEEN :debut AND :fin')
               ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'))
               ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }
        
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result ? (float)$result : 0.0;
    }
}