<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

class ClientRepository extends ServiceEntityRepository
{
    private $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger = null)
    {
        parent::__construct($registry, Client::class);
        $this->logger = $logger;
    }

    public function createSortedQueryBuilder(?string $sortField = null, ?string $sortOrder = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        // Vérification et nettoyage des paramètres de tri
        $sortField = $sortField ?? 'c.nom';
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC']) ? strtoupper($sortOrder) : 'ASC';

        // Liste des champs triables autorisés avec préfixe
        // IMPORTANT: Mettre à jour pour utiliser les noms des propriétés en camelCase
        $allowedFields = [
            'c.nom',
            'c.prenom',
            'c.email',
            'c.telephone',
            'c.adresse',
            'c.numeroCarteVitale', // Modifié: camelCase
            'c.chequesImpayes'     // Modifié: camelCase
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

    public function findByNom(string $query)
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nom) LIKE LOWER(:query)')
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByNomOnly(string $query)
    {
        if ($this->logger) {
            $this->logger->info('findByNomOnly appelé avec: ' . $query);
        }

        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nom) LIKE LOWER(:query)')
            // Assurons-nous d'exclure explicitement les correspondances avec le prénom
            ->andWhere('c.prenom NOT LIKE :query')
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByTelephone(string $query)
    {
        $queryWithoutSpaces = str_replace(' ', '', $query);
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT c.*
            FROM client c
            WHERE REPLACE(c.telephone, \' \', \'\') LIKE :query
            ORDER BY c.nom ASC
            LIMIT 10
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('query', '%' . $queryWithoutSpaces . '%');
        $result = $stmt->executeQuery();
        
        // Convertir les résultats en entités Client
        $clientsData = $result->fetchAllAssociative();
        $clients = [];
        foreach ($clientsData as $clientData) {
            $client = $this->getEntityManager()->getRepository(Client::class)->find($clientData['client_id']);
            if ($client) {
                $clients[] = $client;
            }
        }
        
        return $clients;
    }

    public function findByCarteVitale(string $query)
    {
        $queryWithoutSpaces = str_replace(' ', '', $query);
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT c.*
            FROM client c
            WHERE REPLACE(c.numero_carte_vitale, \' \', \'\') LIKE :query
            ORDER BY c.nom ASC
            LIMIT 10
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('query', '%' . $queryWithoutSpaces . '%');
        $result = $stmt->executeQuery();
        
        // Convertir les résultats en entités Client
        $clientsData = $result->fetchAllAssociative();
        $clients = [];
        foreach ($clientsData as $clientData) {
            $client = $this->getEntityManager()->getRepository(Client::class)->find($clientData['client_id']);
            if ($client) {
                $clients[] = $client;
            }
        }
        
        return $clients;
    }
}