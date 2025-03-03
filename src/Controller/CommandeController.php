<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\ACommander;
use App\Form\CommandeProduitType;
use App\Form\CommandeStatutType;
use App\Service\CommandeService;
use App\Service\ACommanderService;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    private $commandeService;
    private $entityManager;
    private $commandeRepository;
    private $aCommanderService;

    public function __construct(
        CommandeService $commandeService, 
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        ACommanderService $aCommanderService
    ) {
        $this->commandeService = $commandeService;
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->aCommanderService = $aCommanderService;
    }

    #[Route('/', name: 'commande_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Récupérer les paramètres de tri depuis la requête
        $sortField = $request->query->get('sort', 'c.dateCommande');
        $sortOrder = $request->query->get('direction', 'DESC');
        
        // Créer la requête directement au lieu d'appeler une méthode personnalisée
        $query = $this->commandeRepository->createQueryBuilder('c')
            ->leftJoin('c.lignesProduits', 'l')
            ->leftJoin('l.produit', 'p')
            ->orderBy($sortField, $sortOrder)
            ->getQuery();
        
        // Paginer les résultats
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('commande/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/new', name: 'commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(CommandeProduitType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $commande = $this->commandeService->createCommande();
            $this->commandeService->addProduitToCommande(
                $commande, 
                $data['produit'], 
                $data['quantite']
            );
            
            $this->addFlash('success', 'Commande créée avec succès.');
            return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
        }
        
        return $this->render('commande/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/from-a-commander/{id}', name: 'commande_new_from_a_commander', methods: ['GET'])]
    public function newFromACommander(ACommander $aCommander): Response
    {
        $commande = $this->commandeService->importFromACommander($aCommander);
        
        $this->addFlash('success', 'Commande créée à partir de la liste à commander.');
        return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/select-a-commander', name: 'commande_select_a_commander', methods: ['GET'])]
    public function selectACommander(Request $request, PaginatorInterface $paginator): Response
    {
        $sortField = $request->query->get('sort', 'ac.createdAt');
        $sortOrder = $request->query->get('direction', 'desc');

        // Utiliser le service injecté au lieu d'essayer de le récupérer via le container
        $query = $this->aCommanderService->getListesQuery($sortField, $sortOrder);
        
        // Paginer les résultats
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('commande/select_a_commander.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/{id}', name: 'commande_show', methods: ['GET'])]
    public function show(Request $request, Commande $commande, PaginatorInterface $paginator): Response
    {
        $sortField = $request->query->get('sort', 'p.nom');
        $sortOrder = $request->query->get('direction', 'ASC');
        
        // Récupérer la commande avec ses lignes de produits
        $commandeWithProducts = $this->entityManager->getRepository(Commande::class)
            ->findOneWithProducts($commande->getId());
        
        // Utiliser lignesProduits au lieu de lignes
        $lignesProduits = $commandeWithProducts->getLignesProduits();
        
        $pagination = $paginator->paginate(
            $lignesProduits,
            $request->query->getInt('page', 1),
            10
        );
        
        // Formulaire pour ajouter un produit
        $form = $this->createForm(CommandeProduitType::class);
        
        // Formulaire pour changer le statut
        $formStatut = $this->createForm(CommandeStatutType::class, $commande);
        
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'form' => $form->createView(),
            'formStatut' => $formStatut->createView()
        ]);
    }

    #[Route('/{id}/add-produit', name: 'commande_add_produit', methods: ['GET', 'POST'])]
    public function addProduit(Request $request, Commande $commande): Response
    {
        $form = $this->createForm(CommandeProduitType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $this->commandeService->addProduitToCommande(
                $commande, 
                $data['produit'], 
                $data['quantite']
            );
            
            $this->addFlash('success', 'Produit ajouté à la commande.');
            return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
        }
        
        // Rendre la vue pour la méthode GET
        return $this->render('commande/add_produit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }

    #[Route('/{id}/statut', name: 'commande_update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, Commande $commande): Response
    {
        $formStatut = $this->createForm(CommandeStatutType::class, $commande);
        $formStatut->handleRequest($request);
        
        if ($formStatut->isSubmitted() && $formStatut->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Statut de la commande mis à jour.');
        }
        
        return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/{commande_id}/produit/{produit_id}/edit', name: 'commande_edit_produit', methods: ['GET', 'POST'])]
    public function editProduit(Request $request, int $commande_id, int $produit_id): Response
    {
        $commande = $this->entityManager->getRepository(Commande::class)->find($commande_id);
        $produit = $this->entityManager->getRepository(Produit::class)->find($produit_id);
        
        if (!$commande || !$produit) {
            throw $this->createNotFoundException('Commande ou produit non trouvé');
        }
        
        $ligne = null;
        foreach ($commande->getLignesProduits() as $l) { // Correction ici: lignesProduits au lieu de lignes
            if ($l->getProduit()->getId() === $produit->getId()) {
                $ligne = $l;
                break;
            }
        }
        
        if (!$ligne) {
            throw $this->createNotFoundException('Produit non trouvé dans la commande');
        }
        
        $form = $this->createFormBuilder(['quantite' => $ligne->getQuantite()])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['min' => 1]
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->commandeService->updateQuantite($commande, $produit, $data['quantite']);
            
            $this->addFlash('success', 'Quantité mise à jour avec succès.');
            return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
        }
        
        return $this->render('commande/edit_produit.html.twig', [
            'commande' => $commande,
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{commande_id}/produit/{produit_id}/delete', name: 'commande_delete_produit', methods: ['POST'])]
    public function deleteProduit(Request $request, int $commande_id, int $produit_id): Response
    {
        $commande = $this->entityManager->getRepository(Commande::class)->find($commande_id);
        $produit = $this->entityManager->getRepository(Produit::class)->find($produit_id);
        
        if (!$commande || !$produit) {
            throw $this->createNotFoundException('Commande ou produit non trouvé');
        }
        
        if ($this->isCsrfTokenValid('delete_produit'.$produit_id, $request->request->get('_token'))) {
            $this->commandeService->removeProduit($commande, $produit);
            $this->addFlash('success', 'Produit supprimé de la commande.');
        }
        
        return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/delete', name: 'commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $this->commandeService->deleteCommande($commande);
            $this->addFlash('success', 'Commande supprimée avec succès.');
        }
        
        return $this->redirectToRoute('commande_index');
    }
}
