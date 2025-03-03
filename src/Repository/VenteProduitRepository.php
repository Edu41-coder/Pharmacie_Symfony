<?php

namespace App\Repository;

use App\Entity\VenteProduit;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VenteProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenteProduit::class);
    }

    public function findTopSellingProducts(\DateTime $debut, \DateTime $fin, int $limit = 10): array
    {
        return $this->createQueryBuilder('vp')
            ->select('p.nom', 'SUM(vp.quantite) as total')
            ->join('vp.produit', 'p')
            ->join('vp.vente', 'v')
            ->where('v.date BETWEEN :debut AND :fin')
            ->andWhere('v.isDeleted = :isDeleted')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('isDeleted', false)
            ->groupBy('p.id')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    public function getSalesByProduct(Produit $produit, \DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('vp')
            ->join('vp.vente', 'v')
            ->where('vp.produit = :produit')
            ->andWhere('v.date BETWEEN :debut AND :fin')
            ->andWhere('v.isDeleted = :isDeleted')
            ->setParameter('produit', $produit)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getResult();
    }

    public function updateStock(int $venteId, int $produitId, int $quantite): void
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        
        // Mettre à jour le stock dans la table inventaire
        $connection->executeStatement(
            'UPDATE inventaire SET stock = stock - :quantite 
             WHERE produit_id = :produitId',
            [
                'quantite' => $quantite,
                'produitId' => $produitId
            ]
        );
    }
}