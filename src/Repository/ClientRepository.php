<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function createSortedQueryBuilder(?string $sortField = null, ?string $sortOrder = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        // Vérification et nettoyage des paramètres de tri
        $sortField = $sortField ?? 'c.nom';
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'ASC';

        // Liste des champs triables autorisés avec préfixe
        $allowedFields = [
            'c.nom',
            'c.prenom',
            'c.email',
            'c.telephone',
            'c.adresse',
            'c.numero_carte_vitale',
            'c.cheques_impayes'
        ];

        // Si le champ de tri est valide, l'utiliser
        if (in_array($sortField, $allowedFields)) {
            $qb->orderBy($sortField, $sortOrder);
        } else {
            // Tri par défaut
            $qb->orderBy('c.nom', 'ASC');
        }

        return $qb;
    }

    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nom) LIKE LOWER(:term)')
            ->orWhere('LOWER(c.prenom) LIKE LOWER(:term)')
            ->orWhere('LOWER(c.email) LIKE LOWER(:term)')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
} 