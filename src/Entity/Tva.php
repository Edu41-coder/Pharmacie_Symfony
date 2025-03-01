<?php

namespace App\Entity;

use App\Repository\TvaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TvaRepository::class)]
#[ORM\Table(name: 'parametres')]
class Tva
{
    #[ORM\Id]
    #[ORM\Column(name: 'nom', type: 'string')]
    private $id = 'taux_tva'; // Valeur fixe 'taux_tva' comme identifiant

    #[ORM\Column(name: 'valeur', type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d+)?$/', message: 'Le taux de TVA doit être un nombre.')]
    private $taux;

    private $updatedAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTaux(): ?float
    {
        // Convertit la valeur stockée comme chaîne en nombre à virgule flottante
        return (float) $this->taux;
    }

    public function setTaux(float $taux): self
    {
        // Stocke le nombre à virgule flottante comme chaîne
        $this->taux = (string) $taux;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        // Cette méthode est conservée pour la compatibilité avec le code existant,
        // mais updatedAt n'est pas stocké dans la base de données
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        // Cette méthode est conservée pour la compatibilité avec le code existant,
        // mais updatedAt n'est pas stocké dans la base de données
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
