<?php

namespace App\Repository;

use App\Entity\Ordonnance;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrdonnanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ordonnance::class);
    }

    public function findByNumeroOrdonnance(string $numeroOrdonnance): ?Ordonnance
    {
        return $this->createQueryBuilder('o')
            ->where('o.numeroOrdonnance = :numeroOrdonnance')
            ->setParameter('numeroOrdonnance', $numeroOrdonnance)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findByNumeroOrdre(string $numeroDOrdre): ?Ordonnance
    {
        return $this->createQueryBuilder('o')
            ->where('o.numeroDOrdre = :numeroDOrdre')
            ->setParameter('numeroDOrdre', $numeroDOrdre)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByProduit(Produit $produit): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.produits', 'p')
            ->where('p.id = :produitId')
            ->setParameter('produitId', $produit->getId())
            ->getQuery()
            ->getResult();
    }
    
    public function findOrdonnancesNonAssociees(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.ventes', 'v')
            ->where('v.id IS NULL')
            ->getQuery()
            ->getResult();
    }
}
