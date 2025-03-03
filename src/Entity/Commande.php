<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    const STATUT_EN_ATTENTE = 'En attente';
    const STATUT_EN_COURS = 'En cours';
    const STATUT_LIVREE = 'Livrée';
    const STATUT_ANNULEE = 'Annulée';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'commande_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'date_commande', type: 'datetime')]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 20)]
    private ?string $statut = 'En attente';

    #[ORM\Column(name: 'total', type: 'decimal', precision: 10, scale: 2)]
    private ?string $total = '0.00';

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeProduit::class, cascade: ['persist', 'remove'])]
    private Collection $lignesProduits;

    public function __construct()
    {
        $this->lignesProduits = new ArrayCollection();
        $this->dateCommande = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return Collection<int, CommandeProduit>
     */
    public function getLignesProduits(): Collection
    {
        return $this->lignesProduits;
    }

    public function addLigneProduit(CommandeProduit $ligneProduit): self
    {
        if (!$this->lignesProduits->contains($ligneProduit)) {
            $this->lignesProduits->add($ligneProduit);
            $ligneProduit->setCommande($this);
        }

        return $this;
    }

    public function removeLigneProduit(CommandeProduit $ligneProduit): self
    {
        if ($this->lignesProduits->removeElement($ligneProduit)) {
            // set the owning side to null (unless already changed)
            if ($ligneProduit->getCommande() === $this) {
                $ligneProduit->setCommande(null);
            }
        }

        return $this;
    }

    // Calculer le total HT de la commande
    public function calculerTotalHT(): float
    {
        $total = 0;
        foreach ($this->lignesProduits as $ligne) {
            $total += $ligne->getMontantHT();
        }
        return $total;
    }

    // Calculer le total TTC de la commande
    public function calculerTotalTTC(float $tauxTVA = 20.0): float
    {
        $total = 0;
        foreach ($this->lignesProduits as $ligne) {
            $total += $ligne->getMontantTTC($tauxTVA);
        }
        return $total;
    }

    // Mettre à jour le total de la commande
    public function mettreAJourTotal(float $tauxTVA = 20.0): self
    {
        $this->total = (string)$this->calculerTotalTTC($tauxTVA);
        return $this;
    }

    /**
     * Retourne les statuts possibles pour la commande
     */
    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE,
            self::STATUT_EN_COURS,
            self::STATUT_LIVREE,
            self::STATUT_ANNULEE
        ];
    }
}
