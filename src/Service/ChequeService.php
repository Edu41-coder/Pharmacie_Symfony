<?php

namespace App\Service;

use App\Entity\Cheque;
use App\Entity\Client;
use App\Entity\VentePaiement;
use App\Repository\ChequeRepository;
use App\Repository\VentePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ChequeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChequeRepository $chequeRepository,
        private VentePaiementRepository $ventePaiementRepository
    ) {}

    public function saveCheque(Cheque $cheque): void
    {
        $this->entityManager->persist($cheque);
        $this->entityManager->flush();
    }

    public function deleteCheque(Cheque $cheque): void
    {
        $this->entityManager->remove($cheque);
        $this->entityManager->flush();
    }

    public function updateEtat(Cheque $cheque, string $etat): void
    {
        $cheque->setEtat($etat);
        $this->entityManager->flush();
    }

    public function createFilteredQuery(
        ?string $etat = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        string $sortField = 'c.id',
        string $sortOrder = 'DESC'
    ): QueryBuilder {
        $query = $this->chequeRepository->createQueryBuilder('c')
            ->leftJoin(VentePaiement::class, 'vp', 'WITH', 'c.id = vp.cheque')
            ->leftJoin('vp.vente', 'v')
            ->leftJoin('c.client', 'cl');
        
        if ($etat) {
            $query->andWhere('c.etat = :etat')
                ->setParameter('etat', $etat);
        }
        
        if ($dateDebut) {
            $query->andWhere('vp.datePaiement >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($dateDebut));
        }
        
        if ($dateFin) {
            $query->andWhere('vp.datePaiement <= :dateFin')
                ->setParameter('dateFin', new \DateTime($dateFin . ' 23:59:59'));
        }

        // Vérifier si le champ de tri est valide
        $allowedFields = ['c.id', 'c.numeroCheque', 'c.montant', 'c.etat', 'vp.datePaiement', 'cl.nom'];
        $validSortField = in_array($sortField, $allowedFields) ? $sortField : 'c.id';
        $validSortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'DESC';
        
        $query->orderBy($validSortField, $validSortOrder);
        
        return $query;
    }

    public function findVentePaiementByCheque(Cheque $cheque): ?VentePaiement
    {
        return $this->ventePaiementRepository->findOneBy(['cheque' => $cheque]);
    }

    public function findByClient(Client $client): array
    {
        return $this->chequeRepository->findByClient($client);
    }

    public function findByEtat(string $etat): array
    {
        return $this->chequeRepository->findByEtat($etat);
    }

    public function findByNumeroCheque(string $numeroCheque): ?Cheque
    {
        return $this->chequeRepository->findOneBy(['numeroCheque' => $numeroCheque]);
    }
}
