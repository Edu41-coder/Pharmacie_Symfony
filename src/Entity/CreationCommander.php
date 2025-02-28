<?php

namespace App\Entity;

use App\Repository\CreationCommanderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreationCommanderRepository::class)]
#[ORM\Table(name: 'creation_a_commander')]
class CreationCommander
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'a_commander_id')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'creationCommander')]
    #[ORM\JoinColumn(name: 'a_commander_id', referencedColumnName: 'a_commander_id', nullable: false)]
    private ?ACommander $aCommander = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getACommander(): ?ACommander
    {
        return $this->aCommander;
    }

    public function setACommander(ACommander $aCommander): self
    {
        $this->aCommander = $aCommander;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
} 