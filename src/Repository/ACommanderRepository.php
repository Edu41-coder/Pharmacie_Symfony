<?php

namespace App\Repository;

use App\Entity\ACommander;
use App\Entity\CreationCommander;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ACommanderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ACommander::class);
    }

    public function createSortedQueryBuilder(string $sortField, string $sortOrder): QueryBuilder
    {
        return $this->createQueryBuilder('ac')
            ->select('ac', 'p', 'cc')
            ->join('ac.produit', 'p')
            ->leftJoin('ac.creationCommander', 'cc')
            ->orderBy($sortField, $sortOrder);
    }

    public function findLowStock(): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.produit', 'p')
            ->where('p.declencherAlerte = :declencherAlerte')
            ->andWhere('i.stock < p.alerte')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('declencherAlerte', 'oui')
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getResult();
    }
} 