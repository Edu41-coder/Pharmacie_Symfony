<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class ProduitRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
        $this->entityManager = $registry->getManager();
    }

    /**
     * @return Produit[] Returns an array of active Produit objects
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Produit[] Returns an array of Produit objects with low stock
     */
    public function findLowStock(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.declencherAlerte = :declencherAlerte')
            ->andWhere('p.alerte >= p.stock')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('declencherAlerte', 'oui')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.nom) LIKE LOWER(:nom)')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('nom', '%' . $nom . '%')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function softDelete(Produit $produit): void
    {
        $produit->setIsDeleted(true);
        $this->entityManager->persist($produit);
        $this->entityManager->flush();
    }

    public function searchByTerm(string $term)
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.nom) LIKE LOWER(:term)')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les produits qui ne sont pas encore dans l'inventaire
     */
    public function findProduitsNonInventories(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('App\Entity\Inventaire', 'i', 'WITH', 'i.produit_id = p.id')
            ->where('i.produit_id IS NULL')
            ->andWhere('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }
} 