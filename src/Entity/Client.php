<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'client_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(max: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le prénom est requis')]
    #[Assert\Length(max: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L'email est requis")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    #[Assert\Length(max: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 21, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[1-2][\s\d]*\d$/',  // Commence par 1 ou 2, contient des chiffres et espaces
        message: 'Le numéro de carte vitale doit commencer par 1 ou 2'
    )]
    #[Assert\Callback(callback: [Client::class, 'validateNumeroCarteVitale'])]
    private ?string $numero_carte_vitale = null;

    #[ORM\Column]
    private bool $cheques_impayes = false;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Cheque::class)]
    private Collection $cheques;

    public function __construct()
    {
        $this->cheques = new ArrayCollection();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
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

    public function getNumeroCarteVitale(): ?string
    {
        return $this->numero_carte_vitale;
    }

    public function setNumeroCarteVitale(?string $numero_carte_vitale): self
    {
        $this->numero_carte_vitale = $numero_carte_vitale;
        return $this;
    }

    public function getChequesImpayes(): bool
    {
        return $this->cheques_impayes;
    }

    public function setChequesImpayes(bool $cheques_impayes): self
    {
        $this->cheques_impayes = $cheques_impayes;
        return $this;
    }

    public static function validateNumeroCarteVitale($numero_carte_vitale, ExecutionContextInterface $context): void
    {
        if ($numero_carte_vitale === null) {
            return;
        }

        // Compter uniquement les chiffres
        $digits = preg_replace('/\D/', '', $numero_carte_vitale);
        
        if (strlen($digits) !== 15) {  // Changé de 14 à 15 chiffres
            $context->buildViolation('Le numéro de carte vitale doit contenir exactement 15 chiffres')
                   ->addViolation();
        }
    }

    public function toArray(): array
    {
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'commentaire' => $this->commentaire,
            'numero_carte_vitale' => $this->numero_carte_vitale,
            'cheques_impayes' => $this->cheques_impayes,
        ];
    }
}