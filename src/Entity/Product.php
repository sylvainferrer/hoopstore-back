<?php

namespace App\Entity;

use App\Enum\Genre;
use App\Entity\SubCategory;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Vich\Uploadable]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est manquant ou erroné.', groups: ['create', 'update'])]
    #[Assert\Type(type: 'string',message: 'Le nom du produit doit être une chaîne de caractères.', groups: ['create', 'update'])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: SubCategory::class)]
    #[Assert\NotNull(message: 'La sous-catégorie est manquante ou erronée.', groups: ['create', 'update'])]
    private ?SubCategory $subCategory = null;

    #[ORM\Column(type: Types::STRING, enumType: Genre::class, length: 1)]
    #[Assert\NotNull(message: 'Le genre est manquant ou erroné.', groups: ['create', 'update'])]
    private ?Genre $genre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Type(type: 'string', message: 'La description doit être une chaîne de caractères.', groups: ['create', 'update'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Sequentially([
            new Assert\NotBlank(message: 'Le prix est requis.', groups: ['create', 'update']),
            new Assert\Type(type: 'numeric', message: 'Le prix doit être un nombre.', groups: ['create', 'update']),
            new Assert\PositiveOrZero(message: 'Le prix doit être supérieur ou égal à 0.', groups: ['create', 'update'])
        ])]
    private ?string $price = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[Assert\NotNull(message: "L’image est obligatoire.", groups: ['create'])]
    #[Assert\File(
        maxSize: "1M",
        mimeTypes: ["image/jpeg", "image/png", "image/jpg"],
        mimeTypesMessage: "Formats autorisés : JPEG/JPG et PNG uniquement.",
        maxSizeMessage: "L’image ne doit pas dépasser 1 Mo.",
        groups: ['create', 'update']
    )]
    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'imageUrl')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $variants;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): static
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->variants = new ArrayCollection();
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if ($imageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }
   
}