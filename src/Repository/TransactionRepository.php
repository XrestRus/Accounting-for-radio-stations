<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findActiveByDevice($deviceId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.device = :device')
            ->andWhere('t.returnedAt IS NULL')
            ->setParameter('device', $deviceId)
            ->orderBy('t.issuedAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByDeviceAndEmployee($deviceId, $employeeId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.device = :device')
            ->andWhere('t.employee = :employee')
            ->setParameter('device', $deviceId)
            ->setParameter('employee', $employeeId)
            ->orderBy('t.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveTransactions()
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.returnedAt IS NULL')
            ->orderBy('t.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOverdueTransactions()
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('t')
            ->andWhere('t.returnedAt IS NULL')
            ->andWhere('t.dueDate < :now')
            ->setParameter('now', $now)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.device', 'd')
            ->andWhere('d.depot = :depot')
            ->setParameter('depot', $depotId)
            ->orderBy('t.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveTransactionsByDepot($depotId)
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.device', 'd')
            ->andWhere('d.depot = :depot')
            ->andWhere('t.returnedAt IS NULL')
            ->setParameter('depot', $depotId)
            ->orderBy('t.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOverdueTransactionsByDepot($depotId)
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('t')
            ->innerJoin('t.device', 'd')
            ->andWhere('d.depot = :depot')
            ->andWhere('t.returnedAt IS NULL')
            ->andWhere('t.dueDate < :now')
            ->setParameter('depot', $depotId)
            ->setParameter('now', $now)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 