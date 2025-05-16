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
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $sort = $request->query->get('sort', 'date');
        $direction = $request->query->get('direction', 'DESC');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $sortByPaiement = ($sort === 'paiement');
        if ($sortByPaiement) {
            $sortField = 'v.date';
            $manualSortByPaiement = true;
        } else {
            $sortField = \str_contains($sort, '.') ? $sort : 'v.' . $sort;
            $manualSortByPaiement = false;
        }

        $queryBuilder = $this->venteService->createSortedQueryBuilder(
            $sortField,
            $direction,
            $dateDebut,
            $dateFin            
        );

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5,
            [
                'wrap-queries' => true,                
                'sortFieldWhitelist' => ['v.id', 'v.date', 'v.montant', 'v.montantRegle', 'v.aRembourser', 'client.nom']
            ]
        );

        return $this->render('ventes/index.html.twig', [
            'pagination' => $pagination,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            // Important: on transmet toujours le paramètre original pour l'affichage des icônes
            'currentSort' => $sort,
            'currentDirection' => $direction,
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
    public function create(Request $request): Response
    {
        try {
            // Détecter le format des données envoyées
            $contentType = $request->headers->get('Content-Type');
            
            // Si c'est du JSON, récupérer depuis le contenu
            if (str_contains($contentType, 'application/json')) {
                $data = json_decode($request->getContent(), true);
                if (!$data) {
                    throw new \Exception("JSON invalide");
                }
            } else {
                // Sinon, récupérer depuis request->request (form data)
                $data = $request->request->all();
                
                // Si les produits sont envoyés en JSON, les décoder
                if (isset($data['produits']) && is_string($data['produits'])) {
                    $data['produits'] = json_decode($data['produits'], true);
                }
                if (isset($data['paiements']) && is_string($data['paiements'])) {
                    $data['paiements'] = json_decode($data['paiements'], true);
                }
            }
            
            // Vérifier que les produits existent
            if (!isset($data['produits']) || !is_array($data['produits'])) {
                throw new \Exception("Aucun produit n'a été fourni");
            }

            // Créer le répertoire si nécessaire
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Traiter les fichiers uniquement si le format est multipart/form-data
            if (!str_contains($contentType, 'application/json')) {
                $uploadedFiles = $request->files->get('image_ordonnance', []);
                error_log('UPLOAD DEBUG: Nombre de fichiers reçus : ' . count($uploadedFiles));
                
                foreach ($uploadedFiles as $fileKey => $file) {
                    if ($file) {
                        error_log("UPLOAD DEBUG: Traitement du fichier à l'index {$fileKey}");
                        
                        try {
                            // Créer un nom unique pour le fichier
                            $uniqueName = uniqid('ordo_') . '.' . $file->guessExtension();
                            
                            // Déplacer le fichier
                            $file->move($uploadDir, $uniqueName);
                            $imagePath = 'uploads/' . $uniqueName;
                            
                            error_log("UPLOAD DEBUG: Fichier déplacé vers {$imagePath}");
                            
                            // Trouver et mettre à jour le produit correspondant
                            if (isset($data['produits'][$fileKey]) && 
                                isset($data['produits'][$fileKey]['ordonnance'])) {
                                
                                // Injecter directement le chemin de l'image
                                $data['produits'][$fileKey]['ordonnance']['image_path'] = $imagePath;
                                error_log("UPLOAD DEBUG: Image_path injecté dans produit[$fileKey]");
                            }
                        } catch (\Exception $e) {
                            error_log("UPLOAD ERROR: " . $e->getMessage());
                        }
                    }
                }
            }
            
            $vente = $this->venteService->processVente($data);
            
            return new JsonResponse([
                'success' => true, 
                'message' => 'Vente enregistrée avec succès',
                'vente_id' => $vente->getId()
            ], 200);
        } catch (\Exception $e) {
            error_log("Erreur : " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'ventes_show', methods: ['GET'])]
    public function show(Vente $vente): Response
    {
        $produits = $this->venteService->getProduitsVente($vente);
        $paiements = $this->venteService->getPaiementsVente($vente);
        $ordonnances = $this->ordonnanceService->getOrdonnancesForVente($vente);

        return $this->render('ventes/show.html.twig', [
            'vente' => $vente,
            'produits' => $produits,
            'paiements' => $paiements,
            'ordonnances' => $ordonnances
        ]);
    }

    #[Route('/{id}/edit', name: 'ventes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vente $vente): Response
    {
        // Évitez complètement le problème - créez un formulaire sans accéder aux ordonnances
        $form = $this->createForm(VenteType::class, $vente);

        // Récupérer manuellement les données pour la vue
        $produits = $this->venteService->getProduitsVente($vente);
        $paiements = $this->venteService->getPaiementsVente($vente);
        $ordonnances = $this->ordonnanceService->getOrdonnancesForVente($vente);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();
                $this->addFlash('success', 'Vente modifiée avec succès');
                return $this->redirectToRoute('ventes_show', ['id' => $vente->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification de la vente: ' . $e->getMessage());
            }
        }

        return $this->render('ventes/edit.html.twig', [
            'vente' => $vente,
            'produits' => $produits,
            'paiements' => $paiements,
            'ordonnances' => $ordonnances,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'ventes_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Vente $vente): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vente->getId(), $request->request->get('_token'))) {
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
