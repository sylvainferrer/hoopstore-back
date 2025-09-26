<?php

namespace App\Repository;

use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends ServiceEntityRepository<SubCategory>
 */
class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    public function findGroupedByCategory(): array
    {
        return $this->createQueryBuilder('s')
        ->innerJoin('s.category', 'c')
        ->select(
            'c.id   AS categoryId',
            'c.name AS categoryName',
            'c.slug AS categorySlug',
            's.id   AS id',
            's.name AS name',
            's.slug AS slug'
        )
        ->orderBy('c.name', 'ASC')
        ->addOrderBy('s.name', 'ASC')
        ->getQuery()
        ->getArrayResult();
    }

}
