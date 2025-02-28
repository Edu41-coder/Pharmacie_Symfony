<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ligne_a_commander')]
class LigneACommander
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ACommander::class)]
    #[ORM\JoinColumn(name: 'a_commander_id', referencedColumnName: 'a_commander_id', nullable: false)]
    private ?ACommander $aCommander = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getACommander(): ?ACommander
    {
        return $this->aCommander;
    }

    public function setACommander(?ACommander $aCommander): self
    {
        $this->aCommander = $aCommander;
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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
} 