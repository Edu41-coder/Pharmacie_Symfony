<?php

namespace App\Entity;

use App\Repository\CommandeProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeProduitRepository::class)]
#[ORM\Table(name: 'commande_produit')]
class CommandeProduit
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'lignesProduits')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'commande_id', nullable: false)]
    private ?Commande $commande = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(name: 'quantite', type: 'integer')]
    private ?int $quantite = null;

    // Getter et setter pour commande
    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }

    // Getter et setter pour produit
    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    // Getter et setter pour quantite
    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
    
    // Méthode pour calculer le montant total HT
    public function getMontantHT(): float
    {
        if (!$this->produit) {
            return 0;
        }
        
        $prixHT = (float)$this->produit->getPrixVenteHt();
        return $prixHT * $this->quantite;
    }
    
    // Méthode pour calculer le montant total TTC
    public function getMontantTTC(float $tauxTVA = 20.0): float
    {
        $montantHT = $this->getMontantHT();
        return $montantHT * (1 + ($tauxTVA / 100));
    }
    
    // Méthode pour obtenir le prix unitaire HT
    public function getPrixUnitaireHT(): ?float
    {
        if (!$this->produit) {
            return null;
        }
        
        return (float)$this->produit->getPrixVenteHt();
    }
    
    // Méthode pour obtenir le prix unitaire TTC
    public function getPrixUnitaireTTC(float $tauxTVA = 20.0): ?float
    {
        if (!$this->produit) {
            return null;
        }
        
        $prixHT = (float)$this->produit->getPrixVenteHt();
        return $prixHT * (1 + ($tauxTVA / 100));
    }
}
