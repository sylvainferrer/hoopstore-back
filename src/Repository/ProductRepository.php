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

    public function findProducts(?string $categorySlug, ?string $subcategorySlug, ?string $genre,  ?string $sort): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.subCategory', 'sc')->addSelect('sc')
            ->leftJoin('sc.category', 'c')->addSelect('c')
            ->join('p.variants', 'v')
            ->andWhere('v.active = :active')
            ->setParameter('active', true);

        if ($categorySlug) {
            $qb->andWhere('c.slug = :cat')->setParameter('cat', $categorySlug);
        }

        if ($subcategorySlug) {
            $qb->andWhere('sc.slug = :sub')->setParameter('sub', $subcategorySlug);
        }

        if ($genre) {
            $allowed = ['h', 'f', 'e', 'u'];
            if (in_array($genre, $allowed, true)) {
                $qb->andWhere('p.genre = :genre')
                ->setParameter('genre', $genre);
            }
        }

        if ($sort === 'price_asc') {
            $qb->orderBy('p.price', 'ASC');
        } elseif ($sort === 'price_desc') {
            $qb->orderBy('p.price', 'DESC');
        } else {
            $qb->orderBy('p.date', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

}
