<?php

namespace App\Controller\Api;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/produits', name: 'api_produits_')]
class ProduitApiController extends AbstractController
{
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $query = $request->query->get('q');
        
        if (empty($query)) {
            return $this->json([]);
        }

        $produits = $produitRepository->searchByTerm($query);
        
        // Formater les données pour l'API
        $results = array_map(function($produit) {
            return [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prixVenteHt' => number_format($produit->getPrixVenteHt(), 2, ',', ' '),
                'prescription' => $produit->getPrescription(),
                'tauxRemboursement' => $produit->getTauxRemboursement()
            ];
        }, $produits);

        return $this->json($results);
    }
} 