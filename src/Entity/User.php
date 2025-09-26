<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Enum\Roles;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà.')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est manquant ou erroné.')]
    #[Assert\Type(type: 'string', message: 'Le nom doit être une chaîne de caractères.')]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est manquant ou erroné.')]
    #[Assert\Type(type: 'string', message: 'Le prénom doit être une chaîne de caractères.')]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: 'La date de naissance est manquante ou le format est invalide.')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'La date de naissance doit être une date valide (DD-MM-YYYY).')]
    #[Assert\LessThan('today', message: 'La date de naissance doit être antérieure à aujourd\'hui.')]
    private ?\DateTimeImmutable $birthday = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse postale est manquante ou erronée.")]
    #[Assert\Type(type: 'string', message: "L'adresse postale doit être une chaîne de caractères.")]
    private ?string $adresse = null;

    #[ORM\Column(name: 'codePostal', length: 5)]
    #[Assert\NotBlank(message: "Le code postal est manquant ou erroné.")]
    #[Assert\Type(type: 'string', message: "Le code postal doit être une chaîne de caractères.")]
    #[Assert\Regex(
    pattern: "/^\d{5}$/",
    message: "Le code postal doit contenir exactement 5 chiffres."
    )]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la ville est manquant ou erroné.")]
    #[Assert\Type(type: 'string', message: "Le nom de la ville doit être une chaîne de caractères.")]
    private ?string $ville = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'adresse e-mail est manquante ou erronée.")]
    #[Assert\Email(message: "Adresse e-mail invalide.",mode: "html5-allow-no-tld")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est manquant ou erroné.")]
    #[Assert\Length(
        min: 12,
        minMessage: "Le mot de passe doit contenir au moins 12 caractères.",
        max: 4096
    )]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Un rôle est requis.")]
    #[Assert\Choice(
        choices: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
        message: "Rôle invalide. Valeurs autorisées : ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN."
    )]
    private ?string $role = 'ROLE_USER';

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    } 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getBirthday(): \DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeImmutable $birthday): static
    {
        $this->birthday = $birthday;
        return $this;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getCodePostal(): string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role ?? 'ROLE_USER';
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    // requis par UserInterface → doit renvoyer un tableau
    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function eraseCredentials(): void
    {
        // Laisse vide sauf si tu stockes des infos sensibles temporairement
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

}
