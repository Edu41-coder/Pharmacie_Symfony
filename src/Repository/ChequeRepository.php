<?php

namespace App\Repository;

use App\Entity\Cheque;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChequeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cheque::class);
    }

    public function findByNumeroCheque(string $numeroCheque): ?Cheque
    {
        return $this->createQueryBuilder('c')
            ->where('c.numeroCheque = :numeroCheque')
            ->setParameter('numeroCheque', $numeroCheque)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByClient(Client $client): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.client = :client')
            ->setParameter('client', $client)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEtat(string $etat): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.etat = :etat')
            ->setParameter('etat', $etat)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function updateEtatCheque(int $chequeId, string $etat): void
    {
        $em = $this->getEntityManager();
        $cheque = $this->find($chequeId);
        
        if ($cheque) {
            $cheque->setEtat($etat);
            $em->flush();
        }
    }
    
    public function findPendingCheques(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.etat = :etat')
            ->setParameter('etat', Cheque::ETAT_EN_ATTENTE)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    public function getTotalByEtat(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.etat, COUNT(c.id) as count, SUM(c.montant) as total')
            ->groupBy('c.etat')
            ->getQuery()
            ->getResult();
    }
    
    public function findChequesForPeriod(\DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('c')
            ->join('App\Entity\VentePaiement', 'vp', 'WITH', 'vp.cheque = c')
            ->where('vp.datePaiement BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getResult();
    }
}
