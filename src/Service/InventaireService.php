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

    public function createInventaire(array $data): Inventaire
    {
        $inventaire = new Inventaire();
        $this->hydrateInventaire($inventaire, $data);

        $this->entityManager->persist($inventaire);
        $this->entityManager->flush();

        return $inventaire;
    }

    public function updateInventaire(Inventaire $inventaire, array $data): void
    {
        $this->hydrateInventaire($inventaire, $data);
        $inventaire->setLastModified(new \DateTime());
        $this->entityManager->flush();
    }

    public function createSortedQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'i.stock', 'i.last_modified'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->inventaireRepository->createSortedQueryBuilder($sortField, $sortOrder)
            ->addOrderBy($sortField, $sortOrder);
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
        return $this->produitRepository->findProduitsNonInventories();
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