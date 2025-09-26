<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_VETEMENTS = 'category_vetements';
    public const CATEGORY_ACCESSOIRES = 'category_accessoires';
    public const CATEGORY_CHAUSSURES = 'category_chaussures';

    public function load(ObjectManager $manager): void
    {
         // 1. Vêtements
        $vetements = new Category();
        $vetements->setName('Vêtements');
        $manager->persist($vetements);
        $this->addReference(self::CATEGORY_VETEMENTS, $vetements);

        // 2. Accessoires
        $accessoires = new Category();
        $accessoires->setName('Accessoires');
        $manager->persist($accessoires);
        $this->addReference(self::CATEGORY_ACCESSOIRES, $accessoires);

        // 3. Chaussures
        $chaussures = new Category();
        $chaussures->setName('Chaussures');
        $manager->persist($chaussures);
        $this->addReference(self::CATEGORY_CHAUSSURES, $chaussures);

        $manager->flush();
    }
}

