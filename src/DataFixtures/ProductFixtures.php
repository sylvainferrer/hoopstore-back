<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\SubCategory;
use App\DataFixtures\SubCategoryFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Enum\Genre;
use Faker\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupération des sous-catégories
        $subCategories = [
            SubCategoryFixtures::SUBCATEGORY_MAILLOTS,
            SubCategoryFixtures::SUBCATEGORY_SHORTS,
            SubCategoryFixtures::SUBCATEGORY_BALLONS,
            SubCategoryFixtures::SUBCATEGORY_PANIERS,
            SubCategoryFixtures::SUBCATEGORY_LIFESTYLES,
            SubCategoryFixtures::SUBCATEGORY_BASKETBALL,
        ];
        

        foreach ($subCategories as $subRef) {
            /** @var SubCategory $subCategory */
            $subCategory = $this->getReference($subRef,SubCategory::class);

            $cat = $subCategory->getCategory()->getSlug();

            for ($i = 0; $i < 5; $i++) {
                $product = new Product();
                $product->setName($faker->words(3, true));
                $product->setDescription($faker->paragraph(2));
                $product->setPrice($faker->randomFloat(2, 49, 249));
                $product->setGenre($faker->randomElement(Genre::cases()));
                $product->setSubCategory($subCategory);
                $product->setImageUrl('placeholder.jpg');

                $manager->persist($product);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SubCategoryFixtures::class];
    }
    
}