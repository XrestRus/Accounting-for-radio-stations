<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.depot = :depot')
            ->setParameter('depot', $depotId)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOperators()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 'operator')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAdmins()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', 'admin')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 