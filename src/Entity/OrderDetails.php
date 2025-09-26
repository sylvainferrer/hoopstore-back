<?php

namespace App\Entity;

use App\Entity\ProductVariant;
use App\Entity\Order;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\OrderDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailsRepository::class)]
class OrderDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductVariant $productVariant = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Le prix est requis ou erroné.'),
        new Assert\Type(type: 'integer', message: 'Le prix doit être un nombre entier.'),
        new Assert\PositiveOrZero(message: 'Le prix doit être supérieur ou égal à 0.')
    ])]
    private int $unitPrice;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'La quantité est requis ou erroné.'),
        new Assert\Type(type: 'integer', message: 'La quantité doit être un nombre entier.'),
        new Assert\PositiveOrZero(message: 'La quantité doit être supérieur ou égal à 0.')
    ])]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariant $productVariant): static
    {
        $this->productVariant = $productVariant;
        $this->setUnitPrice($productVariant->getProduct()->getPrice()); 
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

}
