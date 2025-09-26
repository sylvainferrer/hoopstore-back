<?php

namespace App\Entity;

use App\Enum\Size;
use App\Entity\Product;
use App\Repository\ProductVariantRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[UniqueEntity(fields: ['product', 'size'], message: 'Cette variante de produit existe déjà.')]
#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\Table(name: 'product_variant')]
#[ORM\UniqueConstraint(name: 'uniq_product_sizes', columns: ['product_id', 'size'])]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Le stock est requis ou erroné.'),
        new Assert\Type(type: 'integer', message: 'Le stock doit être un nombre entier.'),
        new Assert\PositiveOrZero(message: 'Le stock doit être supérieur ou égal à 0.')
    ])]
    private ?int $stock = null;

    #[ORM\Column(type: Types::STRING, enumType: Size::class, length: 10)]
    private ?Size $size = Size::NO_SIZE;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $active = false;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La référence produit est manquante ou erronée.')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getSize(): ?Size
    {
        return $this->size;
    }

    public function setSize(?Size $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}
