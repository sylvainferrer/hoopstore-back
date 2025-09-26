<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $order, bool $flush = true): void
    {
        $this->getEntityManager()->persist($order);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByStripeSessionId(string $sessionId): ?Order
    {
        return $this->findOneBy(['stripeSessionId' => $sessionId]);
    }

    public function findOneWithOrderDetails(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderDetails', 'od')->addSelect('od')
            ->leftJoin('od.productVariant', 'pv')->addSelect('pv')
            ->leftJoin('pv.product', 'p')->addSelect('p')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNonCanceledOrdersByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->select(
                'o.id',
                'u.firstname',
                'u.lastname',
                'u.email',
                'o.amount',
                'o.status',
                'o.paidAt'
            )
            ->join('o.user', 'u')
            ->andWhere('o.user = :user')
            ->andWhere('o.status != :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'CANCELED')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findNonCanceledOrderDetailsByUser(int $id, User $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderDetails', 'od')->addSelect('od')
            ->leftJoin('od.productVariant', 'pv')->addSelect('pv')
            ->leftJoin('pv.product', 'p')->addSelect('p')
            ->andWhere('o.id = :id')
            ->andWhere('o.user = :user')
            ->andWhere('o.status != :status')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->setParameter('status', 'CANCELED')
            ->getQuery()
            ->getOneOrNullResult();
    }

}