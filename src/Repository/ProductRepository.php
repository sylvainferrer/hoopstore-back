<?php

namespace App\Repository;

use App\Entity\Product;
// use App\Entity\Category;
// use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->join('p.variants', 'v')
            ->andWhere('v.active = :active')
            ->setParameter('active', true)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAdmin(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByIdWithVariants(int $id): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.subCategory', 'sc')->addSelect('sc')
            ->leftJoin('sc.category', 'c')->addSelect('c')
            ->leftJoin('p.variants', 'v')->addSelect('v')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }


    public function findById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();            
    }

    public function findByCategory(string $slug): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();             
    }

    public function findBySubCategory(string $slug): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->andWhere('sc.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();             
    }

    public function findLatestProducts(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->join('p.subCategory', 'sc')->addSelect('sc')
            ->join('sc.category', 'c')->addSelect('c')
            ->join('p.variants', 'v')
            ->andWhere('v.active = :active')
            ->setParameter('active', true)
            ->orderBy('p.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
