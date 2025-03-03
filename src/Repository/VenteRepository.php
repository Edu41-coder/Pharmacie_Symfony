<?php

namespace App\Repository;

use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vente::class);
    }

    public function findActiveVentes(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('v.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getVentesByPeriod(\DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.date BETWEEN :debut AND :fin')
            ->andWhere('v.isDeleted = :isDeleted')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('isDeleted', false)
            ->orderBy('v.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createSortedQueryBuilder(string $field = 'date', string $order = 'DESC'): QueryBuilder
    {
        $allowedFields = ['date', 'montant', 'montantRegle'];
        $sortField = in_array($field, $allowedFields) ? 'v.' . $field : 'v.date';
        $sortOrder = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('v')
            ->where('v.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy($sortField, $sortOrder);
    }

    public function softDelete(Vente $vente): void
    {
        $vente->setIsDeleted(true);
        $this->getEntityManager()->flush();
    }
}
