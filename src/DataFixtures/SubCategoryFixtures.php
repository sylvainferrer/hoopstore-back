<?php

namespace App\DataFixtures;

use App\Entity\SubCategory;
use App\Entity\Category;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public const SUBCATEGORY_MAILLOTS = 'subcategory_maillots';
    public const SUBCATEGORY_SHORTS = 'subcategory_shorts';
    public const SUBCATEGORY_BALLONS = 'subcategory_ballons';
    public const SUBCATEGORY_PANIERS = 'subcategory_paniers';
    public const SUBCATEGORY_LIFESTYLES = 'subcategory_lifestyles';
    public const SUBCATEGORY_BASKETBALL = 'subcategory_basketball';

    public function load(ObjectManager $manager): void
    {
        // VÊTEMENTS
        $maillots = new SubCategory();
        $maillots->setName('Maillots');
        $maillots->setCategory($this->getReference(CategoryFixtures::CATEGORY_VETEMENTS,Category::class));
        $manager->persist($maillots);
        $this->addReference(self::SUBCATEGORY_MAILLOTS, $maillots);

        $shorts = new SubCategory();
        $shorts->setName('Shorts');
        $shorts->setCategory($this->getReference(CategoryFixtures::CATEGORY_VETEMENTS,Category::class));
        $manager->persist($shorts);
        $this->addReference(self::SUBCATEGORY_SHORTS, $shorts);

        // ACCESSOIRES
        $ballons = new SubCategory();
        $ballons->setName('Ballons');
        $ballons->setCategory($this->getReference(CategoryFixtures::CATEGORY_ACCESSOIRES,Category::class));
        $manager->persist($ballons);
        $this->addReference(self::SUBCATEGORY_BALLONS, $ballons);

        $paniers = new SubCategory();
        $paniers->setName('Paniers');
        $paniers->setCategory($this->getReference(CategoryFixtures::CATEGORY_ACCESSOIRES,Category::class));
        $manager->persist($paniers);
        $this->addReference(self::SUBCATEGORY_PANIERS, $paniers);

        // CHAUSSURES
        $lifestyles = new SubCategory();
        $lifestyles->setName('Lifestyles');
        $lifestyles->setCategory($this->getReference(CategoryFixtures::CATEGORY_CHAUSSURES,Category::class));
        $manager->persist($lifestyles);
        $this->addReference(self::SUBCATEGORY_LIFESTYLES, $lifestyles);

        $basketball = new SubCategory();
        $basketball->setName('BasketBall');
        $basketball->setCategory($this->getReference(CategoryFixtures::CATEGORY_CHAUSSURES,Category::class));
        $manager->persist($basketball);
        $this->addReference(self::SUBCATEGORY_BASKETBALL, $basketball);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}

