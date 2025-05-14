<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/clients')]
class ClientController extends AbstractController
{
    private $entityManager;
    private $clientRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository,
        private ClientService $clientService,
        private FormFactoryInterface $formFactory,
        private PaginatorInterface $paginator
    ) {
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
    }

    #[Route('/', name: 'client_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $sortField = $request->query->get('sort', 'c.nom');
        $sortOrder = $request->query->get('direction', 'asc');

        $queryBuilder = $this->clientService->getSortedClients($sortField, $sortOrder);

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'c.nom',
                'defaultSortDirection' => 'asc',
                'sortFieldWhitelist' => [
                    'c.nom', 
                    'c.prenom', 
                    'c.email', 
                    'c.telephone', 
                    'c.adresse', 
                    'c.numeroCarteVitale',  // Corrigé en camelCase
                    'c.chequesImpayes'      // Corrigé en camelCase
                ]
            ]
        );

        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    // IMPORTANT: La route /search doit être AVANT les routes avec {id}
    #[Route('/search', name: 'clients_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('query');
            $criteria = $request->query->get('criteria', 'nom');
            
            if (!$query || strlen($query) < 3) {
                return $this->json([]);
            }
            
            $clients = [];
            
            // Recherche selon le critère sélectionné
            switch ($criteria) {
                case 'telephone':
                    $clients = $this->clientRepository->findByTelephone($query);
                    break;
                case 'carte_vitale':
                    $clients = $this->clientRepository->findByCarteVitale($query);
                    break;
                case 'nom':
                    $clients = $this->clientRepository->findByNomOnly($query);
                    break;
                default:
                    // Remplacer par un tableau vide pour éviter toute confusion
                    $clients = [];
                    $this->addFlash('error', 'Critère de recherche invalide');
            }
            
            // Transformer les résultats pour JSON
            $result = [];
            foreach ($clients as $client) {
                $result[] = [
                    'id' => $client->getId(),
                    'nom' => $client->getNom(),
                    'prenom' => $client->getPrenom(),
                    'telephone' => $client->getTelephone(),
                    'numeroCarteVitale' => $client->getNumeroCarteVitale(),
                    'chequesImpayes' => $client->getChequesImpayes()
                ];
            }
            
            // Assurer que $result est toujours un tableau
            if (!is_array($result)) {
                $result = [];
            }
            return $this->json($result);
        } catch (\Exception $e) {
            // En dev, vous pouvez retourner l'erreur 
            return $this->json(['error' => $e->getMessage()], 500);
            
            // En prod, utilisez plutôt:
            // return $this->json(['error' => 'Une erreur est survenue lors de la recherche'], 500);
        }
    }

    #[Route('/new', name: 'client_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Utiliser la méthode persistClient au lieu de createClient
                $this->clientService->persistClient($client);
                // OU, si vous n'avez pas cette méthode, faire directement :
                $this->entityManager->persist($client);
                $this->entityManager->flush();

                $this->addFlash('success', 'Le client a été créé avec succès.');
                return $this->redirectToRoute('client_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du client: ' . $e->getMessage());
            }
        }

        return $this->render('client/new.html.twig', [
            'form' => $form,
        ]);
    }

    // Les routes avec paramètres {id} APRÈS la route /search
    #[Route('/{id}', name: 'client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client
        ]);
    }

    #[Route('/{id}/edit', name: 'client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->clientService->updateClient($client, $form->getData()->toArray());
            
            $this->addFlash('success', 'Client mis à jour avec succès');
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form->createView(),
            'client' => $client
        ]);
    }

    #[Route('/{id}/delete', name: 'client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $this->clientService->deleteClient($client);

        $this->addFlash('success', 'Client supprimé avec succès');
        return $this->redirectToRoute('client_index');
    }

}