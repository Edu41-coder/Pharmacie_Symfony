<?php

namespace App\Entity;

use App\Repository\OrdonnanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdonnanceRepository::class)]
#[ORM\Table(name: 'ordonnance')]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ordonnance_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_ordonnance', type: 'string', length: 50, nullable: false)]
    private ?string $numeroOrdonnance = null;

    #[ORM\Column(name: 'image_path', type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(name: 'numero_d\'ordre', type: 'string', length: 255, nullable: false)]
    private ?string $numeroDOrdre = null;

    #[ORM\ManyToMany(targetEntity: Produit::class)]
    #[ORM\JoinTable(name: 'ordonnance_produit',
        joinColumns: [new ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'ordonnance_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'produit_id')]
    )]
    private Collection $produits;

    #[ORM\ManyToMany(targetEntity: Vente::class, mappedBy: 'ordonnances')]
    private Collection $ventes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->ventes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroOrdonnance(): ?string
    {
        return $this->numeroOrdonnance;
    }

    public function setNumeroOrdonnance(string $numeroOrdonnance): self
    {
        $this->numeroOrdonnance = $numeroOrdonnance;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getNumeroDOrdre(): ?string
    {
        return $this->numeroDOrdre;
    }

    public function setNumeroDOrdre(string $numeroDOrdre): self
    {
        $this->numeroDOrdre = $numeroDOrdre;
        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
        }
        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        $this->produits->removeElement($produit);
        return $this;
    }

    /**
     * @return Collection<int, Vente>
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): self
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes[] = $vente;
            $vente->addOrdonnance($this);
        }
        return $this;
    }

    public function removeVente(Vente $vente): self
    {
        if ($this->ventes->removeElement($vente)) {
            $vente->removeOrdonnance($this);
        }
        return $this;
    }
}
