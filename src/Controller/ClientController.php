<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private ClientService $clientService,
        private FormFactoryInterface $formFactory,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager
    ) {}

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
                'sortFieldWhitelist' => ['c.nom', 'c.prenom', 'c.email', 'c.telephone', 'c.adresse', 'c.numero_carte_vitale', 'c.cheques_impayes']
            ]
        );

        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/new', name: 'client_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(ClientType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->clientService->createClient($form->getData());
            
            $this->addFlash('success', 'Client créé avec succès');
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

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