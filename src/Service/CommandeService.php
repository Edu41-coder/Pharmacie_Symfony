<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\CommandeProduit;
use App\Entity\Produit;
use App\Entity\ACommander;
use App\Service\TvaService;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    private $entityManager;
    private $tvaService;

    public function __construct(EntityManagerInterface $entityManager, TvaService $tvaService = null)
    {
        $this->entityManager = $entityManager;
        $this->tvaService = $tvaService;
    }

    /**
     * Crée une nouvelle commande
     */
    public function createCommande(): Commande
    {
        $commande = new Commande();
        $this->entityManager->persist($commande);
        $this->entityManager->flush();
        
        return $commande;
    }

    /**
     * Ajoute un produit à la commande
     */
    public function addProduitToCommande(Commande $commande, Produit $produit, int $quantite): CommandeProduit
    {
        // Vérifier si le produit existe déjà dans la commande
        $ligne = $this->findCommandeProduit($commande->getId(), $produit->getId());
        
        if (!$ligne) {
            // Créer une nouvelle ligne
            $ligne = new CommandeProduit();
            $ligne->setCommande($commande);
            $ligne->setProduit($produit);
            $ligne->setQuantite($quantite);
            
            // On n'a pas besoin de setTauxTva ni setPrixUnitaire car ces champs n'existent pas
            // Les méthodes associées calculent directement à partir du produit
            
            $commande->addLigneProduit($ligne);
        } else {
            // Mettre à jour la quantité
            $ligne->setQuantite($ligne->getQuantite() + $quantite);
        }
        
        // Mettre à jour le total de la commande
        $commande->mettreAJourTotal();
        
        $this->entityManager->persist($ligne);
        $this->entityManager->flush();
        
        return $ligne;
    }

    /**
     * Trouve une ligne de commande pour un produit donné
     */
    private function findCommandeProduit(int $commandeId, int $produitId): ?CommandeProduit
    {
        return $this->entityManager->getRepository(CommandeProduit::class)
            ->findOneBy([
                'commande' => $commandeId,
                'produit' => $produitId
            ]);
    }

    /**
     * Met à jour la quantité d'un produit dans une commande
     */
    public function updateQuantite(Commande $commande, Produit $produit, int $quantite): ?CommandeProduit
    {
        $ligne = $this->findCommandeProduit($commande->getId(), $produit->getId());
        
        if ($ligne) {
            $ligne->setQuantite($quantite);
            $commande->mettreAJourTotal();
            
            $this->entityManager->flush();
            return $ligne;
        }
        
        return null;
    }

    /**
     * Supprime un produit d'une commande
     */
    public function removeProduit(Commande $commande, Produit $produit): bool
    {
        $ligne = $this->findCommandeProduit($commande->getId(), $produit->getId());
        
        if ($ligne) {
            $commande->removeLigneProduit($ligne);
            $this->entityManager->remove($ligne);
            
            $commande->mettreAJourTotal();
            $this->entityManager->flush();
            
            return true;
        }
        
        return false;
    }

    /**
     * Met à jour le statut d'une commande
     */
    public function updateStatut(Commande $commande, string $statut): Commande
    {
        $commande->setStatut($statut);
        $this->entityManager->flush();
        
        return $commande;
    }

    /**
     * Importe les produits d'un ACommander dans une nouvelle commande
     */
    public function importFromACommander(ACommander $aCommander): Commande
    {
        $commande = $this->createCommande();
        
        foreach ($aCommander->getLignes() as $ligneACommander) {
            $this->addProduitToCommande(
                $commande,
                $ligneACommander->getProduit(),
                $ligneACommander->getQuantite()
            );
        }
        
        return $commande;
    }

    /**
     * Supprime une commande et toutes ses lignes
     */
    public function deleteCommande(Commande $commande): void
    {
        $this->entityManager->remove($commande);
        $this->entityManager->flush();
    }
}
