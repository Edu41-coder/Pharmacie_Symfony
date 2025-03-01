<?php

namespace App\Service;

use App\Entity\Inventaire;
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
                WHERE i.produit_id = p.produit_id
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
} 