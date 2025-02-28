<?php

namespace App\Controller;

use App\Entity\Inventaire;
use App\Entity\Produit;
use App\Form\InventaireType;
use App\Service\InventaireService;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/inventaire')]
class InventaireController extends AbstractController
{
    public function __construct(
        private InventaireService $inventaireService,
        private FormFactoryInterface $formFactory,
        private PaginatorInterface $paginator,
        private ProduitRepository $produitRepository
    ) {}

    #[Route('/', name: 'inventaire_index')]
    public function index(Request $request): Response
    {
        $sortField = $request->query->get('sort', 'p.nom');
        $sortOrder = $request->query->get('direction', 'asc');
        
        $query = $this->inventaireService->createSortedQuery($sortField, $sortOrder);
        
        $pagination = $this->paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('inventaire/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/new-all', name: 'inventaire_new_all', methods: ['POST'])]
    public function newAll(): Response
    {
        $this->inventaireService->createInventaireComplet();
        $this->addFlash('success', 'Inventaire complet créé avec succès');
        return $this->redirectToRoute('inventaire_index');
    }

    #[Route('/new', name: 'inventaire_new')]
    public function new(Request $request): Response
    {
        $produitsDisponibles = $this->inventaireService->getProduitsNonInventories();
        
        if (empty($produitsDisponibles)) {
            $this->addFlash('info', 'Tous les produits sont déjà présents dans l\'inventaire.');
            return $this->redirectToRoute('inventaire_index');
        }

        $inventaire = new Inventaire();
        $form = $this->formFactory->create(InventaireType::class, $inventaire, [
            'produits_existants' => $produitsDisponibles
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->inventaireService->createInventaire([
                'produit' => $form->get('produit')->getData(),
                'stock' => $form->get('stock')->getData(),
            ]);

            $this->addFlash('success', 'Produit ajouté à l\'inventaire avec succès');
            return $this->redirectToRoute('inventaire_index');
        }

        return $this->render('inventaire/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'inventaire_show', methods: ['GET'])]
    public function show(Inventaire $inventaire): Response
    {
        return $this->render('inventaire/show.html.twig', [
            'inventaire' => $inventaire
        ]);
    }

    #[Route('/{id}/edit', name: 'inventaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inventaire $inventaire): Response
    {
        $form = $this->formFactory->create(InventaireType::class, $inventaire, [
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->inventaireService->updateInventaire($inventaire, [
                'stock' => $form->get('stock')->getData(),
            ]);
            
            $this->addFlash('success', 'Inventaire mis à jour avec succès');
            return $this->redirectToRoute('inventaire_index');
        }

        return $this->render('inventaire/edit.html.twig', [
            'form' => $form->createView(),
            'inventaire' => $inventaire
        ]);
    }

    #[Route('/{id}/delete', name: 'inventaire_delete', methods: ['POST'])]
    public function delete(Request $request, Inventaire $inventaire): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$inventaire->getProduitId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $this->inventaireService->deleteInventaire($inventaire);

        $this->addFlash('success', 'Inventaire supprimé avec succès');
        return $this->redirectToRoute('inventaire_index');
    }
} 