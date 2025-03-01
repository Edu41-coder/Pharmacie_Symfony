<?php

namespace App\Entity;

use App\Repository\InventaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InventaireRepository::class)]
#[ORM\Table(name: 'inventaire')]
class Inventaire
{
    #[ORM\Id]
    #[ORM\Column(name: 'produit_id', type: 'integer')]
    private ?int $produit_id = null;

    #[ORM\OneToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $stock = null;

    #[ORM\Column(name: 'last_modified', type: 'datetime')]
    private ?\DateTimeInterface $lastModified = null;

    public function getProduitId(): ?int
    {
        return $this->produit_id;
    }

    public function setProduitId(?int $produit_id): self
    {
        $this->produit_id = $produit_id;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        $this->produit_id = $produit ? $produit->getId() : null;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTimeInterface $lastModified): self
    {
        $this->lastModified = $lastModified;
        return $this;
    }
}