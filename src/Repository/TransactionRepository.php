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

    /**
     * Находит транзакции по заданным фильтрам
     */
    public function findByFilters(
        ?\DateTime $dateFrom = null, 
        ?\DateTime $dateTo = null, 
        ?string $operationType = null, 
        ?string $employee = null, 
        ?string $device = null, 
        ?string $status = null
    ) {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.device', 'd')
            ->innerJoin('t.employee', 'e')
            ->innerJoin('t.issuedBy', 'u');
        
        // Фильтр по дате начала
        if ($dateFrom) {
            $qb->andWhere('
                (t.issuedAt >= :dateFrom) OR 
                (t.returnedAt IS NOT NULL AND t.returnedAt >= :dateFrom)
            ')
            ->setParameter('dateFrom', $dateFrom);
        }
        
        // Фильтр по дате окончания
        if ($dateTo) {
            $qb->andWhere('
                (t.issuedAt <= :dateTo) OR 
                (t.returnedAt IS NOT NULL AND t.returnedAt <= :dateTo)
            ')
            ->setParameter('dateTo', $dateTo);
        }
        
        // Фильтр по типу операции
        if ($operationType) {
            if ($operationType === 'issue') {
                $qb->andWhere('t.returnedAt IS NULL');
            } elseif ($operationType === 'return') {
                $qb->andWhere('t.returnedAt IS NOT NULL');
            }
        }
        
        // Фильтр по сотруднику
        if ($employee) {
            $qb->andWhere('e.fullName LIKE :employee')
                ->setParameter('employee', '%' . $employee . '%');
        }
        
        // Фильтр по устройству
        if ($device) {
            $qb->andWhere('d.name LIKE :device OR d.serialNumber LIKE :device')
                ->setParameter('device', '%' . $device . '%');
        }
        
        // Фильтр по статусу
        if ($status) {
            $now = new \DateTime();
            
            if ($status === 'issued') {
                $qb->andWhere('t.returnedAt IS NULL')
                   ->andWhere('t.dueDate >= :now')
                   ->setParameter('now', $now);
            } elseif ($status === 'returned') {
                $qb->andWhere('t.returnedAt IS NOT NULL');
            } elseif ($status === 'overdue') {
                $qb->andWhere('t.returnedAt IS NULL')
                   ->andWhere('t.dueDate < :now')
                   ->setParameter('now', $now);
            }
        }
        
        // Сортировка по дате (сначала новые)
        $qb->addOrderBy('CASE WHEN t.returnedAt IS NOT NULL THEN t.returnedAt ELSE t.issuedAt END', 'DESC');
        
        return $qb->getQuery()->getResult();
    }
} 