<?php

namespace App\Controller;

use App\Entity\Cheque;
use App\Form\ChequeType;
use App\Service\ChequeService;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/cheques')]
#[IsGranted('ROLE_ADMIN')]
class ChequeController extends AbstractController
{
    public function __construct(
        private ChequeService $chequeService,
        private ClientService $clientService,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'cheques_index')]
    public function index(Request $request): Response
    {
        $etat = $request->query->get('etat');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $sortField = $request->query->get('sort', 'date_paiement');
        $sortOrder = $request->query->get('direction', 'DESC');
        
        $query = $this->chequeService->createFilteredQuery($etat, $dateDebut, $dateFin, $sortField, $sortOrder);
        
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );
        
        return $this->render('cheques/index.html.twig', [
            'pagination' => $pagination,
            'etat' => $etat,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
    }

    #[Route('/new', name: 'cheques_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $cheque = new Cheque();
        $form = $this->createForm(ChequeType::class, $cheque);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->chequeService->saveCheque($cheque);
            $this->addFlash('success', 'Chèque créé avec succès');
            return $this->redirectToRoute('cheques_index');
        }
        
        return $this->render('cheques/new.html.twig', [
            'cheque' => $cheque,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'cheques_show', methods: ['GET'])]
    public function show(Cheque $cheque): Response
    {
        $ventePaiement = $this->chequeService->findVentePaiementByCheque($cheque);
        
        return $this->render('cheques/show.html.twig', [
            'cheque' => $cheque,
            'ventePaiement' => $ventePaiement
        ]);
    }

    #[Route('/{id}/edit', name: 'cheques_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cheque $cheque): Response
    {
        $form = $this->createForm(ChequeType::class, $cheque);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->chequeService->saveCheque($cheque);
            $this->addFlash('success', 'Chèque mis à jour avec succès');
            return $this->redirectToRoute('cheques_show', ['id' => $cheque->getId()]);
        }
        
        return $this->render('cheques/edit.html.twig', [
            'cheque' => $cheque,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/update-status', name: 'cheques_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Cheque $cheque): Response
    {
        $etat = $request->request->get('etat');
        
        if (in_array($etat, [Cheque::ETAT_EN_ATTENTE, Cheque::ETAT_VALIDE, Cheque::ETAT_REFUSE])) {
            $this->chequeService->updateEtat($cheque, $etat);
            $this->addFlash('success', 'Statut du chèque mis à jour');
        } else {
            $this->addFlash('error', 'Statut invalide');
        }
        
        return $this->redirectToRoute('cheques_show', ['id' => $cheque->getId()]);
    }

    #[Route('/{id}/delete', name: 'cheques_delete', methods: ['POST'])]
    public function delete(Request $request, Cheque $cheque): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cheque->getId(), $request->request->get('_token'))) {
            $this->chequeService->deleteCheque($cheque);
            $this->addFlash('success', 'Chèque supprimé avec succès');
        }
        
        return $this->redirectToRoute('cheques_index');
    }
}
