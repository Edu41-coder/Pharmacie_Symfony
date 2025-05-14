<?php

namespace App\Controller;

use App\Entity\Vente;
use App\Entity\Client;
use App\Entity\Produit;
use App\Form\VenteType;
use App\Service\VenteService;
use App\Service\ClientService;
use App\Service\ProduitService;
use App\Service\InventaireService;
use App\Service\OrdonnanceService;
use App\Service\FactureService; // Ajout de cette ligne importante
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/ventes')]
class VenteController extends AbstractController
{
    public function __construct(
        private VenteService $venteService,
        private ClientService $clientService,
        private ProduitService $produitService,
        private InventaireService $inventaireService,
        private OrdonnanceService $ordonnanceService,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private FactureService $factureService // Ajout de cette dépendance
    ) {}

    #[Route('/', name: 'ventes_index')]
    public function index(Request $request): Response
    {
        $sortField = $request->query->get('sort', 'date');
        $sortOrder = $request->query->get('direction', 'DESC');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        
        $query = $this->venteService->createSortedQueryBuilder($sortField, $sortOrder, $dateDebut, $dateFin);
        
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('ventes/index.html.twig', [
            'pagination' => $pagination,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
    }

    #[Route('/new', name: 'ventes_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $clientRepository = $this->entityManager->getRepository(Client::class);
        $produitRepository = $this->entityManager->getRepository(Produit::class);
        
        $clients = $clientRepository->findAll();
        $produits = $produitRepository->findAll();

        // Add the TVA value
        $tva = 20; // Default TVA rate (update this with your actual TVA rate)
        
        return $this->render('ventes/new.html.twig', [
            'clients' => $clients,
            'produits' => $produits,
            'tva' => $tva
        ]);
    }

    #[Route('/create', name: 'ventes_create', methods: ['POST'])]
    public function create(Request $request): Response // Retirer le paramètre FactureService
    {
        try {
            // Récupération des données du formulaire
            $data = json_decode($request->getContent(), true);
            
            // Vérification des données essentielles
            if (!isset($data['produits']) || empty($data['produits'])) {
                return new JsonResponse(['error' => 'Aucun produit sélectionné'], 400);
            }

            // Traiter la vente avec le service
            $vente = $this->venteService->processVente($data);
            
            // Création facture si demandée
            if (isset($data['creer_facture']) && $data['creer_facture']) {
                // Utiliser le service injecté via le constructeur
                return $this->forward('App\Controller\FactureController::create', [
                    'id' => $vente->getId()
                ]);
            }
            
            return new JsonResponse([
                'success' => true, 
                'message' => 'Vente enregistrée avec succès',
                'vente_id' => $vente->getId()
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'ventes_show', methods: ['GET'])]
    public function show(Vente $vente): Response
    {
        $produits = $this->venteService->getProduitsVente($vente);
        $paiements = $this->venteService->getPaiementsVente($vente);
        $ordonnances = $vente->getOrdonnances();
        
        return $this->render('ventes/show.html.twig', [
            'vente' => $vente,
            'produits' => $produits,
            'paiements' => $paiements,
            'ordonnances' => $ordonnances
        ]);
    }

    #[Route('/{id}/edit', name: 'ventes_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Vente $vente): Response
    {
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Vente mise à jour');
            return $this->redirectToRoute('ventes_show', ['id' => $vente->getId()]);
        }
        
        return $this->render('ventes/edit.html.twig', [
            'vente' => $vente,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'ventes_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Vente $vente): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vente->getId(), $request->request->get('_token'))) {
            $this->venteService->deleteVente($vente);
            $this->addFlash('success', 'Vente supprimée');
        }
        
        return $this->redirectToRoute('ventes_index');
    }

    #[Route('/api/search-client', name: 'api_search_client', methods: ['GET'])]
    public function searchClient(Request $request): JsonResponse
    {
        $term = $request->query->get('term');
        $clients = $this->clientService->searchByTerm($term);
        
        $result = [];
        foreach ($clients as $client) {
            $result[] = [
                'id' => $client->getId(),
                'text' => $client->getNom() . ' ' . $client->getPrenom(),
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom()
            ];
        }
        
        return new JsonResponse(['results' => $result]);
    }

    #[Route('/api/search-produit', name: 'api_search_produit', methods: ['GET'])]
    public function searchProduit(Request $request): JsonResponse
    {
        $term = $request->query->get('term');
        $produits = $this->produitService->searchByTerm($term);
        
        $result = [];
        foreach ($produits as $produit) {
            $inventaire = $this->inventaireService->findByProduit($produit);
            $stock = $inventaire ? $inventaire->getStock() : 0;
            
            $result[] = [
                'id' => $produit->getId(),
                'text' => $produit->getNom(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrixVenteHt(),
                'prescription' => $produit->getPrescription(),
                'taux_remboursement' => $produit->getTauxRemboursement(),
                'stock' => $stock
            ];
        }
        
        return new JsonResponse(['results' => $result]);
    }
    
    #[Route('/statistiques', name: 'ventes_statistiques')]
    #[IsGranted('ROLE_ADMIN')]
    public function statistiques(Request $request): Response
    {
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        
        $statistiques = $this->venteService->getStatistiquesVentes($dateDebut, $dateFin);
        $totalVentes = $this->venteService->getMontantTotalVentes($dateDebut, $dateFin);
        $totalRemboursements = $this->venteService->getMontantTotalRemboursements($dateDebut, $dateFin);
        
        return $this->render('ventes/statistiques.html.twig', [
            'statistiques' => $statistiques,
            'totalVentes' => $totalVentes,
            'totalRemboursements' => $totalRemboursements,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
    }
}
