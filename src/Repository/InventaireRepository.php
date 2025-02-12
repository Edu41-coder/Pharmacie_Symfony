<?php

namespace App\Repository;

use App\Entity\Inventaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class InventaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventaire::class);
    }

    public function findAllWithProduits(): array
    {
        return $this->createQueryBuilder('i')
            ->select('i', 'p')
            ->leftJoin('i.produit', 'p')
            ->where('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createSortedQueryBuilder(string $sortField, string $sortOrder): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->select('i', 'p')
            ->leftJoin('i.produit', 'p')
            ->where('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false);
    }
} 