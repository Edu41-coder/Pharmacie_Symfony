<?php

namespace App\Entity;

use App\Repository\VentePaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VentePaiementRepository::class)]
#[ORM\Table(name: 'vente_paiement')]
class VentePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'paiement_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'ventePaiements')]
    #[ORM\JoinColumn(name: 'vente_id', referencedColumnName: 'vente_id', nullable: false)]
    private ?Vente $vente = null;

    #[ORM\Column(name: 'mode_paiement', type: 'string', length: 20, nullable: true, enumType: false)]
    private ?string $modePaiement = null;

    #[ORM\Column(name: 'montant', type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $montant = '0.00';

    #[ORM\Column(name: 'numero_cheque', type: 'string', length: 50, nullable: true)]
    private ?string $numeroCheque = null;

    #[ORM\Column(name: 'date_paiement', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\ManyToOne(targetEntity: Cheque::class)]
    #[ORM\JoinColumn(name: 'cheque_id', referencedColumnName: 'cheque_id', nullable: true)]
    private ?Cheque $cheque = null;

    public function __construct()
    {
        $this->datePaiement = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): self
    {
        $this->vente = $vente;
        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?string $modePaiement): self
    {
        $this->modePaiement = $modePaiement;
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

    public function getNumeroCheque(): ?string
    {
        return $this->numeroCheque;
    }

    public function setNumeroCheque(?string $numeroCheque): self
    {
        $this->numeroCheque = $numeroCheque;
        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getCheque(): ?Cheque
    {
        return $this->cheque;
    }

    public function setCheque(?Cheque $cheque): self
    {
        $this->cheque = $cheque;
        return $this;
    }
}
