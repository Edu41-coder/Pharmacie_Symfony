<?php

namespace App\Controller;

use App\Entity\ACommander;
use App\Form\ACommanderType;
use App\Service\ACommanderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;

#[Route('/a-commander')]
class ACommanderController extends AbstractController
{
    public function __construct(
        private ACommanderService $aCommanderService,
        private PaginatorInterface $paginator,
        private LoggerInterface $logger
    ) {}

    #[Route('/', name: 'a_commander_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $sortField = $request->query->get('sort', 'cc.created_at');
        $sortOrder = $request->query->get('direction', 'desc');

        $query = $this->aCommanderService->getListesQuery($sortField, $sortOrder);

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10,
            [
                'defaultSortFieldName' => 'cc.created_at',
                'defaultSortDirection' => 'desc',
                'pageParameterName' => 'page',
                'sortFieldParameterName' => 'sort',
                'sortDirectionParameterName' => 'direction',
                'template' => 'pagination/custom_pagination.html.twig'
            ]
        );

        return $this->render('a_commander/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/new', name: 'a_commander_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $aCommander = new ACommander();
        $form = $this->createForm(ACommanderType::class, $aCommander);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->aCommanderService->createACommander([
                'produit' => $aCommander->getProduit(),
                'quantite' => $aCommander->getQuantite()
            ]);
            
            $this->addFlash('success', 'Liste à commander créée avec succès');
            return $this->redirectToRoute('a_commander_index');
        }

        return $this->render('a_commander/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/new-from-inventory', name: 'a_commander_new_from_inventory', methods: ['GET'])]
    public function newFromInventory(): Response
    {
        $this->aCommanderService->createFromInventory();
        $this->addFlash('success', 'Liste créée à partir de l\'inventaire');
        return $this->redirectToRoute('a_commander_index');
    }

    #[Route('/new-from-low-stock', name: 'a_commander_new_from_low_stock', methods: ['GET'])]
    public function newFromLowStock(): Response
    {
        $this->aCommanderService->createFromLowStock();
        $this->addFlash('success', 'Liste créée à partir des stocks insuffisants');
        return $this->redirectToRoute('a_commander_index');
    }

    #[Route('/{liste_id}/produit/{produit_id}/edit', name: 'a_commander_edit_produit', methods: ['GET', 'POST'])]
    public function editProduit(Request $request, int $liste_id, int $produit_id): Response
    {
        $this->logger->debug('Tentative de modification du produit', [
            'liste_id' => $liste_id,
            'produit_id' => $produit_id,
            'request_uri' => $request->getRequestUri()
        ]);

        // Récupérer d'abord la liste pour vérifier qu'elle existe
        $liste = $this->aCommanderService->getListe($liste_id);
        if (!$liste) {
            $this->logger->warning('Liste non trouvée', ['liste_id' => $liste_id]);
            $this->addFlash('error', 'Liste non trouvée');
            return $this->redirectToRoute('a_commander_index');
        }

        $aCommander = $this->aCommanderService->getProduitFromListe($liste_id, $produit_id);
        
        if (!$aCommander) {
            $this->logger->warning('Produit non trouvé', [
                'liste_id' => $liste_id,
                'produit_id' => $produit_id,
                'liste_exists' => ($liste !== null)
            ]);
            $this->addFlash('error', 'Produit non trouvé dans la liste');
            return $this->redirectToRoute('a_commander_show', ['id' => $liste_id]);
        }

        $form = $this->createForm(ACommanderType::class, $aCommander, [
            'edit_mode' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->aCommanderService->updateACommander($aCommander, $form->getData()->toArray());
            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute('a_commander_show', ['id' => $liste_id]);
        }

        return $this->render('a_commander/edit_produit.html.twig', [
            'form' => $form->createView(),
            'liste_id' => $liste_id,
            'produit' => $aCommander
        ]);
    }

    #[Route('/{id}/edit', name: 'a_commander_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $aCommander = $this->aCommanderService->getListe($id);
        if (!$aCommander) {
            throw $this->createNotFoundException('Liste non trouvée');
        }

        $form = $this->createForm(ACommanderType::class, $aCommander);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->aCommanderService->updateACommander($aCommander, $form->getData()->toArray());
            $this->addFlash('success', 'Liste mise à jour avec succès');
            return $this->redirectToRoute('a_commander_show', ['id' => $id]);
        }

        return $this->render('a_commander/edit.html.twig', [
            'form' => $form->createView(),
            'a_commander' => $aCommander
        ]);
    }

    #[Route('/{id}/delete', name: 'a_commander_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $aCommander = $this->aCommanderService->getListe($id);
        if (!$aCommander) {
            throw $this->createNotFoundException('Liste non trouvée');
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $this->aCommanderService->deleteACommander($aCommander);
            $this->addFlash('success', 'Liste supprimée avec succès');
        }

        return $this->redirectToRoute('a_commander_index');
    }

    #[Route('/{id}', name: 'a_commander_show', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $sortField = $request->query->get('sort', 'p.nom');
        $sortOrder = $request->query->get('direction', 'asc');

        $liste = $this->aCommanderService->getListe($id);
        if (!$liste) {
            throw $this->createNotFoundException('Liste non trouvée');
        }

        $produits = $this->aCommanderService->getProduitsListe($id, $sortField, $sortOrder);

        return $this->render('a_commander/show.html.twig', [
            'liste' => $liste,
            'produits' => $produits,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder
        ]);
    }

    #[Route('/{liste_id}/produit/{produit_id}/delete', name: 'a_commander_delete_produit', methods: ['POST'])]
    public function deleteProduit(Request $request, int $liste_id, int $produit_id): Response
    {
        $aCommander = $this->aCommanderService->getProduitFromListe($liste_id, $produit_id);
        if (!$aCommander) {
            throw $this->createNotFoundException('Produit non trouvé dans la liste');
        }

        if ($this->isCsrfTokenValid('delete_produit'.$produit_id, $request->request->get('_token'))) {
            $this->aCommanderService->deleteACommander($aCommander);
            $this->addFlash('success', 'Produit supprimé avec succès');
        }

        return $this->redirectToRoute('a_commander_show', ['id' => $liste_id]);
    }
} 