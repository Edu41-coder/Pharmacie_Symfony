<?php

namespace App\Service;

use App\Entity\Inventaire;
use App\Entity\Produit;
use App\Repository\InventaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Repository\ProduitRepository;

class InventaireService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InventaireRepository $inventaireRepository,
        private ProduitRepository $produitRepository
    ) {}

    public function createInventaire(array $data): void
    {
        $inventaire = new Inventaire();
        $inventaire->setProduit($data['produit']);
        $inventaire->setStock($data['stock']);
        $inventaire->setLastModified(new \DateTime());

        $this->entityManager->persist($inventaire);
        $this->entityManager->flush();
    }

    public function updateInventaire(Inventaire $inventaire, array $data): void
    {
        if (isset($data['stock'])) {
            $inventaire->setStock($data['stock']);
        }
        $inventaire->setLastModified(new \DateTime());

        $this->entityManager->flush();
    }

    public function createSortedQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        return $this->inventaireRepository->getSortedQueryBuilder($sortField, $sortOrder);
    }

    private function hydrateInventaire(Inventaire $inventaire, array $data): void
    {
        if (isset($data['produit'])) {
            $inventaire->setProduit($data['produit']);
            $inventaire->setProduitId($data['produit']->getId());
        }
        if (isset($data['stock'])) {
            $inventaire->setStock($data['stock']);
        }
    }

    public function getProduitsNonInventories(): array
    {
        $qb = $this->produitRepository->createQueryBuilder('p')
            ->where('p.isDeleted = :isDeleted')
            ->andWhere('NOT EXISTS (
                SELECT 1 FROM App\Entity\Inventaire i 
                WHERE i.produit = p
            )')
            ->setParameter('isDeleted', false)
            ->orderBy('p.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function createInventaireComplet(): void
    {
        $produits = $this->produitRepository->findAllActive();
        foreach ($produits as $produit) {
            if (!$this->inventaireRepository->findOneBy(['produit' => $produit])) {
                $this->createInventaire([
                    'produit' => $produit,
                    'stock' => 10
                ]);
            }
        }
    }

    public function deleteInventaire(Inventaire $inventaire): void
    {
        $this->entityManager->remove($inventaire);
        $this->entityManager->flush();
    }

    /**
     * Trouve l'inventaire par produit
     * @param Produit $produit
     * @return Inventaire|null
     */
    public function findByProduit(Produit $produit): ?Inventaire
    {
        return $this->inventaireRepository->findOneBy(['produit' => $produit]);
    }

    /**
     * Mettre à jour le stock d'un produit
     * @param Produit $produit
     * @param int $quantite (peut être négatif pour un retrait)
     */
    public function updateStock(Produit $produit, int $quantite): void
    {
        $inventaire = $this->findByProduit($produit);
        
        if (!$inventaire) {
            $inventaire = new Inventaire();
            $inventaire->setProduit($produit);
            $inventaire->setStock(max(0, $quantite)); // Au moins 0
            $this->entityManager->persist($inventaire);
        } else {
            $nouveauStock = $inventaire->getStock() + $quantite;
            $inventaire->setStock(max(0, $nouveauStock)); // Éviter stock négatif
        }
        
        $this->entityManager->flush();
    }

    /**
     * Vérifie si un produit est disponible en stock
     * @param Produit $produit
     * @param int $quantite
     * @return bool
     */
    public function estDisponible(Produit $produit, int $quantite): bool
    {
        $inventaire = $this->findByProduit($produit);
        
        if (!$inventaire) {
            return false;
        }
        
        return $inventaire->getStock() >= $quantite;
    }
}