<?php

namespace App\Entity;

use App\Entity\Category;
use App\Repository\SubCategoryRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubCategoryRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de sous-catégorie existe déjà.')]

class SubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'La sous-catégorie est manquante ou erronée.')]
    #[Assert\Type(type: 'string', message: 'Le nom de la sous-catégorie doit être une chaîne de caractères.')]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[Assert\NotNull(message: 'La catégorie est manquante ou erronée.')]
    private ?Category $category = null;

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

        $slugger = new AsciiSlugger();
        $this->slug = $slugger->slug($name)->lower()->toString();

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        
        return $this;
    }
}
