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

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->select('i', 'p')
            ->leftJoin('i.produit', 'p')
            ->where('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false);
    }

    public function findAllQuery(): QueryBuilder
    {
        return $this->createBaseQueryBuilder()
            ->orderBy('i.produit_id', 'ASC');
    }

    public function findAllWithProduits(): array
    {
        return $this->createBaseQueryBuilder()
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getSortedQueryBuilder(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'i.stock', 'i.last_modified'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        return $this->createBaseQueryBuilder()
            ->orderBy($sortField, $sortOrder);
    }

    public function findLowStock(): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.produit', 'p')
            ->where('i.stock <= p.alerte')
            ->andWhere('p.declencherAlerte = :alerte')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('alerte', 'oui')
            ->setParameter('isDeleted', false)
            ->getQuery()
            ->getResult();
    }
} 