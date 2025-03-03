<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ProduitService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProduitRepository $produitRepository
    ) {}

    public function createProduit(array $data): Produit
    {
        $produit = new Produit();
        $this->hydrateProduit($produit, $data);

        $this->entityManager->persist($produit);
        $this->entityManager->flush();

        return $produit;
    }

    public function updateProduit(Produit $produit, array $data): void
    {
        $this->hydrateProduit($produit, $data);
        $this->entityManager->flush();
    }

    public function deleteProduit(Produit $produit): void
    {
        $this->produitRepository->softDelete($produit);
    }

    /**
     * Trouve tous les produits
     * @return Produit[]
     */
    public function findAll(): array
    {
        return $this->produitRepository->findBy(['isDeleted' => false], ['nom' => 'ASC']);
    }

    /**
     * Recherche un produit par term (nom)
     * @param string $term
     * @return Produit[]
     */
    public function searchByTerm(string $term): array
    {
        return $this->produitRepository->createQueryBuilder('p')
            ->where('p.nom LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->andWhere('p.isDeleted = :deleted')
            ->setParameter('deleted', false)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Produit $produit, bool $flush = true): void
    {
        $this->entityManager->persist($produit);
        
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete(Produit $produit): void
    {
        $produit->setIsDeleted(true);
        $this->entityManager->flush();
    }

    public function getProduitById(int $id): ?Produit
    {
        return $this->produitRepository->find($id);
    }

    public function findByNom(string $nom): array
    {
        return $this->produitRepository->findByNom($nom);
    }

    public function findLowStock(): array
    {
        return $this->produitRepository->findLowStock();
    }

    public function createSortedQuery(string $sortField, string $sortOrder): QueryBuilder
    {
        $allowedFields = ['p.nom', 'p.prixVenteHt', 'p.prescription', 'p.tauxRemboursement'];
        $sortField = in_array($sortField, $allowedFields) ? $sortField : 'p.nom';
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'DESC' : 'ASC';

        return $this->produitRepository->createQueryBuilder('p')
            ->select('p')
            ->where('p.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->addOrderBy($sortField, $sortOrder);
    }

    private function hydrateProduit(Produit $produit, array $data): void
    {
        if (isset($data['nom'])) {
            $produit->setNom($data['nom']);
        }
        if (isset($data['description'])) {
            $produit->setDescription($data['description']);
        }
        if (isset($data['prixVenteHt'])) {
            $produit->setPrixVenteHt($data['prixVenteHt']);
        }
        if (isset($data['prescription'])) {
            $produit->setPrescription($data['prescription']);
        }
        if (isset($data['tauxRemboursement'])) {
            $produit->setTauxRemboursement($data['tauxRemboursement']);
        }
        if (isset($data['alerte'])) {
            $produit->setAlerte($data['alerte']);
        }
        if (isset($data['declencherAlerte'])) {
            $produit->setDeclencherAlerte($data['declencherAlerte']);
        }
    }
}