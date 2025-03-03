<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'produit_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix_vente_ht', type: 'string')]
    #[Assert\NotBlank(message: 'Le prix de vente HT ne peut pas être vide')]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Le prix doit être un nombre décimal valide')]
    private ?string $prixVenteHt = null;

    #[ORM\Column(name: 'prescription', type: 'string', length: 3, options: ['default' => 'non'])]
    private string $prescription = 'non';

    #[ORM\Column(name: 'taux_remboursement', type: 'integer', nullable: true)]
    private ?int $tauxRemboursement = null;

    #[ORM\Column(name: 'alerte', type: 'integer', nullable: true)]
    private ?int $alerte = null;

    #[ORM\Column(name: 'declencher_alerte', type: 'string', length: 3, options: ['default' => 'non'])]
    private string $declencherAlerte = 'non';

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private bool $isDeleted = false;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixVenteHt(): ?string
    {
        return $this->prixVenteHt;
    }

    public function setPrixVenteHt(?string $prixVenteHt): self
    {
        $this->prixVenteHt = $prixVenteHt;
        return $this;
    }

    public function getPrescription(): string
    {
        return $this->prescription;
    }

    public function setPrescription(string $prescription): self
    {
        $this->prescription = $prescription;
        return $this;
    }

    public function getTauxRemboursement(): ?int
    {
        return $this->tauxRemboursement;
    }

    public function setTauxRemboursement(?int $tauxRemboursement): self
    {
        $this->tauxRemboursement = $tauxRemboursement;
        return $this;
    }

    public function getAlerte(): ?int
    {
        return $this->alerte;
    }

    public function setAlerte(?int $alerte): self
    {
        $this->alerte = $alerte;
        return $this;
    }

    public function getDeclencherAlerte(): string
    {
        return $this->declencherAlerte;
    }

    public function setDeclencherAlerte(string $declencherAlerte): self
    {
        $this->declencherAlerte = $declencherAlerte;
        return $this;
    }

    public function isIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }
}