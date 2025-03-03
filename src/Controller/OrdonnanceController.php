<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Form\OrdonnanceType;
use App\Service\OrdonnanceService;
use App\Service\ProduitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/ordonnances')]
class OrdonnanceController extends AbstractController
{
    public function __construct(
        private OrdonnanceService $ordonnanceService,
        private ProduitService $produitService,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'ordonnances_index')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        
        if ($search) {
            $query = $this->ordonnanceService->searchOrdonnances($search);
        } else {
            $query = $this->ordonnanceService->findAllQuery();
        }
        
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('ordonnances/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    #[Route('/new', name: 'ordonnances_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ordonnance = new Ordonnance();
        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imagePath = $this->ordonnanceService->uploadImage($imageFile);
                $ordonnance->setImagePath($imagePath);
            }
            
            $this->ordonnanceService->save($ordonnance);
            
            $this->addFlash('success', 'Ordonnance créée avec succès');
            return $this->redirectToRoute('ordonnances_index');
        }
        
        return $this->render('ordonnances/new.html.twig', [
            'ordonnance' => $ordonnance,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'ordonnances_show', methods: ['GET'])]
    public function show(Ordonnance $ordonnance): Response
    {
        return $this->render('ordonnances/show.html.twig', [
            'ordonnance' => $ordonnance
        ]);
    }

    #[Route('/{id}/edit', name: 'ordonnances_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ordonnance $ordonnance): Response
    {
        $form = $this->createForm(OrdonnanceType::class, $ordonnance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imagePath = $this->ordonnanceService->uploadImage($imageFile);
                $ordonnance->setImagePath($imagePath);
            }
            
            $this->ordonnanceService->save($ordonnance);
            
            $this->addFlash('success', 'Ordonnance mise à jour avec succès');
            return $this->redirectToRoute('ordonnances_index');
        }
        
        return $this->render('ordonnances/edit.html.twig', [
            'ordonnance' => $ordonnance,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'ordonnances_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Ordonnance $ordonnance): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ordonnance->getId(), $request->request->get('_token'))) {
            $this->ordonnanceService->delete($ordonnance);
            $this->addFlash('success', 'Ordonnance supprimée avec succès');
        }
        
        return $this->redirectToRoute('ordonnances_index');
    }
}
