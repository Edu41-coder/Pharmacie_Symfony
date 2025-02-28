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
} 