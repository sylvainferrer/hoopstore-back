<?php

namespace App\DataFixtures;

use App\Entity\ProductVariant;
use App\Entity\Product;
use App\Enum\Size;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class ProductVariantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $products = $manager->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $category = $product->getSubCategory()?->getCategory()?->getSlug() ?? '';

            $pool = match ($category) {
                'vetements'  => Size::clothingCases(),
                'chaussures' => Size::shoesCases(),
                default      => [Size::NO_SIZE],
            };

            if ($pool === [Size::NO_SIZE]) {
                $sizesToCreate = [Size::NO_SIZE];
            } else {
                shuffle($pool);
                $sizesToCreate = array_slice($pool, 0, random_int(1, 2));
            }

            foreach ($sizesToCreate as $sizeEnum) {
                $existing = $manager->getRepository(ProductVariant::class)->findOneBy([
                    'product' => $product,
                    'size'    => $sizeEnum,
                ]);
                if ($existing) {
                    continue;
                }

                $variant = new ProductVariant();
                $variant->setProduct($product);
                $variant->setSize($sizeEnum);
                $variant->setStock($faker->numberBetween(0, 50));
                $variant->setActive($faker->boolean(95));

                $manager->persist($variant);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}
