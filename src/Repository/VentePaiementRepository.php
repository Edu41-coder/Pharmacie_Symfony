<?php

namespace App\Repository;

use App\Entity\VentePaiement;
use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VentePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VentePaiement::class);
    }

    public function findByVente(Vente $vente): array
    {
        return $this->createQueryBuilder('vp')
            ->where('vp.vente = :vente')
            ->setParameter('vente', $vente)
            ->orderBy('vp.datePaiement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByModePaiement(string $modePaiement, \DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('vp')
            ->join('vp.vente', 'v')
            ->where('vp.modePaiement = :modePaiement')
            ->andWhere('vp.datePaiement BETWEEN :debut AND :fin')
            ->andWhere('v.isDeleted = :isDeleted')
            ->setParameter('modePaiement', $modePaiement)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('isDeleted', false)
            ->orderBy('vp.datePaiement', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    public function getTotalByPaymentMethod(\DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('vp')
            ->select('vp.modePaiement', 'SUM(vp.montant) as total')
            ->join('vp.vente', 'v')
            ->where('vp.datePaiement BETWEEN :debut AND :fin')
            ->andWhere('v.isDeleted = :isDeleted')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('isDeleted', false)
            ->groupBy('vp.modePaiement')
            ->getQuery()
            ->getResult();
    }
}
