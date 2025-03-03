<?php

namespace App\Repository;

use App\Entity\CommandeProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeProduit>
 */
class CommandeProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeProduit::class);
    }

    /**
     * Trouve tous les produits d'une commande avec tri
     */
    public function findByCommandeWithSort(int $commandeId, string $sortField = 'p.nom', string $sortOrder = 'ASC'): array
    {
        $allowedFields = ['p.nom', 'cp.quantite', 'cp.prixUnitaire'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? $sortOrder : 'ASC';

        return $this->createQueryBuilder('cp')
            ->join('cp.produit', 'p')
            ->join('cp.commande', 'c')
            ->where('c.id = :commandeId')
            ->setParameter('commandeId', $commandeId)
            ->orderBy($sortField, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une ligne de commande pour une commande et un produit donnés
     */
    public function findOneByCommandeAndProduit(int $commandeId, int $produitId): ?CommandeProduit
    {
        return $this->findOneBy([
            'commande' => $commandeId,
            'produit' => $produitId
        ]);
    }

    /**
     * Trouve toutes les lignes d'une commande
     */
    public function findByCommande(int $commandeId): array
    {
        return $this->findBy(['commande' => $commandeId]);
    }

    /**
     * Supprime toutes les lignes d'une commande
     */
    public function deleteByCommande(int $commandeId): void
    {
        $qb = $this->createQueryBuilder('cp')
            ->delete()
            ->where('cp.commande = :commandeId')
            ->setParameter('commandeId', $commandeId);
        
        $qb->getQuery()->execute();
    }
}
