<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function findByDevice($deviceId)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.device = :device')
            ->setParameter('device', $deviceId)
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($userId)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAction($action)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.action LIKE :action')
            ->setParameter('action', '%' . $action . '%')
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.timestamp >= :startDate')
            ->andWhere('l.timestamp <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.device', 'd')
            ->andWhere('d.depot = :depot')
            ->setParameter('depot', $depotId)
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 