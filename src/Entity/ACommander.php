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

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: LigneACommander::class, mappedBy: 'aCommander', cascade: ['persist'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneACommander $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes[] = $ligne;
            $ligne->setACommander($this);
        }
        return $this;
    }

    public function removeLigne(LigneACommander $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getACommander() === $this) {
                $ligne->setACommander(null);
            }
        }
        return $this;
    }
} 