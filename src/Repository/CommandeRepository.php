<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Récupère toutes les commandes avec leurs produits
     */
    public function findAllWithProducts(): Query
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.lignesProduits', 'l')
            ->leftJoin('l.produit', 'p')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery();
    }

    /**
     * Crée un QueryBuilder pour les commandes avec tri
     */
    public function createSortedQueryBuilder(string $sortField = 'c.dateCommande', string $sortOrder = 'DESC'): QueryBuilder
    {
        $allowedFields = ['c.id', 'c.dateCommande', 'c.statut', 'c.total'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'c.dateCommande';
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? $sortOrder : 'DESC';

        return $this->createQueryBuilder('c')
            ->orderBy($sortField, $sortOrder);
    }

    /**
     * Trouve une commande avec tous ses produits
     */
    public function findOneWithProducts(int $id): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.lignesProduits', 'l') // Correction ici: lignesProduits au lieu de lignes
            ->leftJoin('l.produit', 'p')
            ->addSelect('l', 'p')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
