<?php
// src/Controller/FactureController.php
namespace App\Controller;

use App\Entity\Vente;
use App\Service\FactureService;
use App\Service\VenteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/factures')]
class FactureController extends AbstractController
{
    public function __construct(
        private FactureService $factureService,
        private VenteService $venteService
    ) {}

    #[Route('/create/{id}', name: 'factures_create', methods: ['POST'])]
    public function create(Request $request, Vente $vente): JsonResponse
    {
        try {
            $produits = $this->venteService->getProduitsVente($vente);
            $paiements = $this->venteService->getPaiementsVente($vente);
            
            $produitsData = [];
            foreach ($produits as $produitVente) {
                $produitsData[] = [
                    'produit' => $produitVente['produit'],
                    'quantite' => $produitVente['quantite'],
                    'montant_a_rembourser' => $produitVente['montant_a_rembourser']
                ];
            }
            
            // Nouveau code pour traiter les modes de paiement multiples
            $paiementsData = [];
            foreach ($paiements as $paiement) {
                $modes = explode(',', $paiement->getMode());
                $montantTotal = $paiement->getMontant();
                $nombreModes = count($modes);
                
                foreach ($modes as $mode) {
                    $mode = trim($mode);
                    $paiementInfo = [
                        'mode' => $mode,
                        'montant' => round($montantTotal / $nombreModes, 2) // Répartition égale du montant
                    ];
                    
                    if ($mode === 'cheque' && $paiement->getNumeroCheque()) {
                        $paiementInfo['numero_cheque'] = $paiement->getNumeroCheque();
                    }
                    
                    $paiementsData[] = $paiementInfo;
                }
            }
            
            $result = $this->factureService->saveFacture($vente, $produitsData, $paiementsData);
            
            if ($result) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Facture créée avec succès'
                ]);
            } else {
                return new JsonResponse([
                    'error' => 'Erreur lors de la création de la facture'
                ], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/{venteId}', name: 'factures_show', methods: ['GET'])]
    public function show(string $venteId): Response
    {
        $facture = $this->factureService->getFactureByVenteId((int)$venteId);
        
        return $this->render('factures/facture_show.html.twig', [
            'facture' => $facture,
            'venteId' => $venteId
        ]);
    }
    
    #[Route('/', name: 'factures_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $factures = [];
        $collections = $this->factureService->getSortedFactures();
        
        foreach ($collections as $collection) {
            $facture = $this->factureService->loadFacture($collection);
            if ($facture) {
                $factures[] = $facture;
            }
        }
        
        return $this->render('factures/index.html.twig', [
            'factures' => $factures
        ]);
    }
}