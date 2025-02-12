<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Service\ProduitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\FormFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/produits')]
class ProduitController extends AbstractController
{
    public function __construct(
        private ProduitService $produitService,
        private FormFactoryInterface $formFactory,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'produits_index')]
    public function index(Request $request): Response
    {
        $sortField = $request->query->get('sort', 'p.nom');
        $sortOrder = $request->query->get('direction', 'asc');
        
        $query = $this->produitService->createSortedQuery($sortField, $sortOrder);
        
        $pagination = $this->paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            15 // Nombre d'éléments par page
        );

        return $this->render('produits/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/new', name: 'produits_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->formFactory->create(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->produitService->createProduit([
                'nom' => $form->get('nom')->getData(),
                'description' => $form->get('description')->getData(),
                'prixVenteHt' => $form->get('prixVenteHt')->getData(),
                'prescription' => $form->get('prescription')->getData(),
                'tauxRemboursement' => $form->get('tauxRemboursement')->getData(),
                'alerte' => $form->get('alerte')->getData(),
                'declencherAlerte' => $form->get('declencherAlerte')->getData(),
            ]);

            $this->addFlash('success', 'Produit créé avec succès');
            return $this->redirectToRoute('produits_index');
        }

        return $this->render('produits/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'produits_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produits/show.html.twig', [
            'produit' => $produit
        ]);
    }

    #[Route('/{id}/edit', name: 'produits_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Produit $produit): Response
    {
        $form = $this->formFactory->create(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->produitService->updateProduit($produit, [
                'nom' => $form->get('nom')->getData(),
                'description' => $form->get('description')->getData(),
                'prixVenteHt' => $form->get('prixVenteHt')->getData(),
                'prescription' => $form->get('prescription')->getData(),
                'tauxRemboursement' => $form->get('tauxRemboursement')->getData(),
                'alerte' => $form->get('alerte')->getData(),
                'declencherAlerte' => $form->get('declencherAlerte')->getData(),
            ]);
            
            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute('produits_index');
        }

        return $this->render('produits/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit
        ]);
    }

    #[Route('/{id}/delete', name: 'produits_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Produit $produit): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $this->produitService->deleteProduit($produit);

        $this->addFlash('success', 'Produit supprimé avec succès');
        return $this->redirectToRoute('produits_index');
    }
} 