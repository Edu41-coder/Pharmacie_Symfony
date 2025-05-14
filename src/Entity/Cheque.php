<?php

namespace App\Entity;

use App\Repository\ChequeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChequeRepository::class)]
#[ORM\Table(name: 'cheque')]
class Cheque
{
    public const ETAT_EN_ATTENTE = 'en_attente';
    public const ETAT_VALIDE = 'valide';
    public const ETAT_REFUSE = 'refuse';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'cheque_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_cheque', type: 'string', length: 50, nullable: false)]
    private ?string $numeroCheque = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'cheques')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'client_id', nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(name: 'montant', type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private ?string $montant = '0.00';

    #[ORM\Column(name: 'etat', type: 'string', length: 20, nullable: false)]
    private ?string $etat = self::ETAT_EN_ATTENTE;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCheque(): ?string
    {
        return $this->numeroCheque;
    }

    public function setNumeroCheque(string $numeroCheque): self
    {
        $this->numeroCheque = $numeroCheque;
        return $this;
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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        if (!in_array($etat, [self::ETAT_EN_ATTENTE, self::ETAT_VALIDE, self::ETAT_REFUSE])) {
            throw new \InvalidArgumentException("État de chèque invalide");
        }
        $this->etat = $etat;
        return $this;
    }
}
