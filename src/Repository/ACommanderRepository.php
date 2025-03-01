<?php

namespace App\Repository;

use App\Entity\ACommander;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<ACommander>
 */
class ACommanderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ACommander::class);
    }

    public function createSortedQueryBuilder(string $sortField, string $sortOrder): QueryBuilder
    {
        return $this->createQueryBuilder('ac')
            ->select('ac', 'l', 'p')
            ->leftJoin('ac.lignes', 'l')
            ->leftJoin('l.produit', 'p')
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

    public function findAllWithCreationDateQuery(string $sortField = 'ac.createdAt', string $sortOrder = 'DESC'): Query
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac', 'l', 'p')
            ->leftJoin('ac.lignes', 'l')
            ->leftJoin('l.produit', 'p');
            
        // Handle sort field
        $allowedSortFields = ['ac.createdAt', 'p.nom'];
        if (in_array($sortField, $allowedSortFields)) {
            $qb->orderBy($sortField, $sortOrder === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('ac.createdAt', 'DESC');
        }
        
        return $qb->getQuery();
    }
    
    public function findOneByListeAndProduit(int $liste_id, int $produit_id): ?ACommander
    {
        return $this->createQueryBuilder('a')
            ->where('a.id = :liste_id')
            ->andWhere('a.produit = :produit_id')
            ->setParameter('liste_id', $liste_id)
            ->setParameter('produit_id', $produit_id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findProduitsByListe(int $liste_id, string $sortField = 'p.nom', string $sortOrder = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->select('ac', 'l', 'p')
            ->leftJoin('ac.lignes', 'l')
            ->leftJoin('l.produit', 'p')
            ->where('ac.id = :liste_id')
            ->setParameter('liste_id', $liste_id);
            
        // Sort field handling
        $allowedSortFields = ['p.nom', 'l.quantite', 'p.stock'];
        $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'p.nom';
        $sortOrder = $sortOrder === 'ASC' ? 'ASC' : 'DESC';
        
        $qb->orderBy($sortField, $sortOrder);
        
        return $qb->getQuery()->getResult();
    }
}