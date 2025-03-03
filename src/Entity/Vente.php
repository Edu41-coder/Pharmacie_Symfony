<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
#[ORM\Table(name: 'vente')]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'vente_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'client_id', nullable: true)]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: 'montant', type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $montant = '0.00';

    #[ORM\Column(name: 'montant_regle', type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $montantRegle = '0.00';

    #[ORM\Column(name: 'a_rembourser', type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $aRembourser = '0.00';

    #[ORM\Column(name: 'commentaire', type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', nullable: false)]
    private bool $isDeleted = false;

    #[ORM\OneToMany(targetEntity: VenteProduit::class, mappedBy: 'vente', cascade: ['persist', 'remove'])]
    private Collection $venteProduits;

    #[ORM\OneToMany(targetEntity: VentePaiement::class, mappedBy: 'vente', cascade: ['persist', 'remove'])]
    private Collection $ventePaiements;

    #[ORM\ManyToMany(targetEntity: Ordonnance::class, inversedBy: 'ventes')]
    #[ORM\JoinTable(name: 'vente_ordonnance',
        joinColumns: [new ORM\JoinColumn(name: 'vente_id', referencedColumnName: 'vente_id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'ordonnance_id')]
    )]
    private Collection $ordonnances;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->venteProduits = new ArrayCollection();
        $this->ventePaiements = new ArrayCollection();
        $this->ordonnances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMontantRegle(): ?string
    {
        return $this->montantRegle;
    }

    public function setMontantRegle(string $montantRegle): self
    {
        $this->montantRegle = $montantRegle;
        return $this;
    }

    public function getARembourser(): ?string
    {
        return $this->aRembourser;
    }

    public function setARembourser(string $aRembourser): self
    {
        $this->aRembourser = $aRembourser;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    /**
     * @return Collection<int, VenteProduit>
     */
    public function getVenteProduits(): Collection
    {
        return $this->venteProduits;
    }

    public function addVenteProduit(VenteProduit $venteProduit): self
    {
        if (!$this->venteProduits->contains($venteProduit)) {
            $this->venteProduits[] = $venteProduit;
            $venteProduit->setVente($this);
        }
        return $this;
    }

    public function removeVenteProduit(VenteProduit $venteProduit): self
    {
        if ($this->venteProduits->removeElement($venteProduit)) {
            if ($venteProduit->getVente() === $this) {
                $venteProduit->setVente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, VentePaiement>
     */
    public function getVentePaiements(): Collection
    {
        return $this->ventePaiements;
    }

    public function addVentePaiement(VentePaiement $ventePaiement): self
    {
        if (!$this->ventePaiements->contains($ventePaiement)) {
            $this->ventePaiements[] = $ventePaiement;
            $ventePaiement->setVente($this);
        }
        return $this;
    }

    public function removeVentePaiement(VentePaiement $ventePaiement): self
    {
        if ($this->ventePaiements->removeElement($ventePaiement)) {
            if ($ventePaiement->getVente() === $this) {
                $ventePaiement->setVente(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Ordonnance>
     */
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

    public function addOrdonnance(Ordonnance $ordonnance): self
    {
        if (!$this->ordonnances->contains($ordonnance)) {
            $this->ordonnances[] = $ordonnance;
        }
        return $this;
    }

    public function removeOrdonnance(Ordonnance $ordonnance): self
    {
        $this->ordonnances->removeElement($ordonnance);
        return $this;
    }
}
