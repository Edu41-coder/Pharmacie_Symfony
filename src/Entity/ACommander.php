<?php

namespace App\Entity;

use App\Repository\ACommanderRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ACommanderRepository::class)]
#[ORM\Table(name: 'a_commander')]
class ACommander
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'a_commander_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantite = null;

    #[ORM\OneToOne(targetEntity: CreationCommander::class, mappedBy: 'aCommander')]
    private ?CreationCommander $creationCommander = null;

    #[ORM\OneToMany(targetEntity: LigneACommander::class, mappedBy: 'aCommander')]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreationCommander(): ?CreationCommander
    {
        return $this->creationCommander;
    }

    public function setCreationCommander(?CreationCommander $creationCommander): self
    {
        $this->creationCommander = $creationCommander;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->creationCommander?->getCreatedAt();
    }

    public function getLignes(): Collection
    {
        return $this->lignes;
    }
} 