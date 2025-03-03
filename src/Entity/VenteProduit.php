<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VenteProduitRepository;

#[ORM\Entity(repositoryClass: VenteProduitRepository::class)]
#[ORM\Table(name: 'vente_produit')]
class VenteProduit
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'venteProduits')]
    #[ORM\JoinColumn(name: 'vente_id', referencedColumnName: 'vente_id', nullable: false)]
    private ?Vente $vente = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(name: 'quantite', type: 'integer', nullable: false)]
    private int $quantite = 0;

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): self
    {
        $this->vente = $vente;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
}
