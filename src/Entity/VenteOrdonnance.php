<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VenteOrdonnanceRepository;

#[ORM\Entity(repositoryClass: VenteOrdonnanceRepository::class)]
#[ORM\Table(name: 'vente_ordonnance')]
class VenteOrdonnance
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'venteOrdonnances')]
    #[ORM\JoinColumn(name: 'vente_id', referencedColumnName: 'vente_id', nullable: false)]
    private ?Vente $vente = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Ordonnance::class)]
    #[ORM\JoinColumn(name: 'ordonnance_id', referencedColumnName: 'ordonnance_id', nullable: false)]
    private ?Ordonnance $ordonnance = null;

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): self
    {
        $this->vente = $vente;
        return $this;
    }

    public function getOrdonnance(): ?Ordonnance
    {
        return $this->ordonnance;
    }

    public function setOrdonnance(?Ordonnance $ordonnance): self
    {
        $this->ordonnance = $ordonnance;
        return $this;
    }
}