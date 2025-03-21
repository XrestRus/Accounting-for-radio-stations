<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.depot = :depot')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('depot', $depotId)
            ->orderBy('e.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActive()
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.deletedAt IS NULL')
            ->orderBy('e.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName($name)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.fullName LIKE :name')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('e.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 