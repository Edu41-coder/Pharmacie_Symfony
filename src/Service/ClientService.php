<?php

namespace App\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClientService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository
    ) {}

    public function createClient(array $data): Client
    {
        $client = new Client();
        $this->hydrateClient($client, $data);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $client;
    }

    public function updateClient(Client $client, array $data): void
    {
        $this->hydrateClient($client, $data);
        $this->entityManager->flush();
    }

    public function deleteClient(Client $client): void
    {
        $this->entityManager->remove($client);
        $this->entityManager->flush();
    }

    private function hydrateClient(Client $client, array $data): void
    {
        if (isset($data['nom'])) {
            $client->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $client->setPrenom($data['prenom']);
        }
        if (isset($data['email'])) {
            $client->setEmail($data['email']);
        }
        if (isset($data['telephone'])) {
            $client->setTelephone($data['telephone']);
        }
        if (isset($data['adresse'])) {
            $client->setAdresse($data['adresse']);
        }
        if (isset($data['commentaire'])) {
            $client->setCommentaire($data['commentaire']);
        }
        if (isset($data['numero_carte_vitale'])) {
            $client->setNumeroCarteVitale($data['numero_carte_vitale']);
        }
        if (isset($data['cheques_impayes'])) {
            $client->setChequesImpayes($data['cheques_impayes']);
        }
    }

    public function getSortedClients(?string $sortField = null, ?string $sortOrder = null)
    {
        return $this->clientRepository->createSortedQueryBuilder($sortField, $sortOrder);
    }

    /**
     * Trouve tous les clients (sans filtrage par isActive puisque ce champ n'existe pas)
     * @return Client[]
     */
    public function findAllActive(): array
    {
        // Modification pour retourner tous les clients au lieu de filtrer par isActive
        return $this->clientRepository->findBy([], ['nom' => 'ASC']);
    }

    /**
     * Recherche un client par term (nom ou prénom)
     * @param string $term
     * @return Client[]
     */
    public function searchByTerm(string $term): array
    {
        return $this->clientRepository->createQueryBuilder('c')
            ->where('c.nom LIKE :term OR c.prenom LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            // Suppression du filtre sur isActive
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Client $client, bool $flush = true): void
    {
        $this->entityManager->persist($client);
        
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    // Modification de la méthode delete pour ne pas utiliser isActive
    public function delete(Client $client): void
    {
        // Suppression complète du client au lieu de le désactiver
        $this->entityManager->remove($client);
        $this->entityManager->flush();
    }

    public function getClientById(int $id): ?Client
    {
        return $this->clientRepository->find($id);
    }
}