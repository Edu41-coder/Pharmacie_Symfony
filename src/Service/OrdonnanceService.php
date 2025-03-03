<?php

namespace App\Service;

use App\Entity\Ordonnance;
use App\Entity\Produit;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\QueryBuilder;

class OrdonnanceService
{
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrdonnanceRepository $ordonnanceRepository,
        string $projectDir
    ) {
        $this->uploadDir = $projectDir . '/public/uploads/ordonnances/';
    }

    public function save(Ordonnance $ordonnance): void
    {
        $this->entityManager->persist($ordonnance);
        $this->entityManager->flush();
    }

    public function delete(Ordonnance $ordonnance): void
    {
        $this->entityManager->remove($ordonnance);
        $this->entityManager->flush();
        
        // Supprimer l'image si elle existe
        if ($ordonnance->getImagePath()) {
            @unlink($ordonnance->getImagePath());
        }
    }

    public function uploadImage(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($this->uploadDir, $fileName);
        
        return '/uploads/ordonnances/' . $fileName;
    }

    public function findAllQuery(): QueryBuilder
    {
        return $this->ordonnanceRepository->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC');
    }

    public function searchOrdonnances(string $term): QueryBuilder
    {
        return $this->ordonnanceRepository->createQueryBuilder('o')
            ->where('o.numeroOrdonnance LIKE :term')
            ->orWhere('o.numeroDOrdre LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('o.id', 'DESC');
    }

    public function findByNumeroOrdonnance(string $numeroOrdonnance): ?Ordonnance
    {
        return $this->ordonnanceRepository->findOneBy(['numeroOrdonnance' => $numeroOrdonnance]);
    }

    public function findByNumeroDOrdre(string $numeroDOrdre): ?Ordonnance
    {
        return $this->ordonnanceRepository->findOneBy(['numeroDOrdre' => $numeroDOrdre]);
    }

    public function findByProduit(Produit $produit): array
    {
        return $this->ordonnanceRepository->findByProduit($produit);
    }

    public function findOrdonnancesNonAssociees(): array
    {
        return $this->ordonnanceRepository->findOrdonnancesNonAssociees();
    }
}
