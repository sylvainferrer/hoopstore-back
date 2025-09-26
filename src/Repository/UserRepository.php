<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\Roles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

       /**
        * @return User[] Returns an array of User objects
        */

        public function findAllUsersOnly(): array
        {
            return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 'ROLE_USER')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
        }

        public function findAllExceptSuperAdmin(): array
        {
            return $this->createQueryBuilder('u')
            ->andWhere('u.role != :role')
            ->setParameter('role', 'ROLE_SUPER_ADMIN')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
        }
}
