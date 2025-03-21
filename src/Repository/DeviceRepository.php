<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.depot = :depot')
            ->setParameter('depot', $depotId)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailable($depotId = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', Device::STATUS_AVAILABLE);
        
        if ($depotId) {
            $qb->andWhere('d.depot = :depot')
               ->setParameter('depot', $depotId);
        }
        
        return $qb->orderBy('d.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function findIssued($depotId = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', Device::STATUS_ISSUED);
        
        if ($depotId) {
            $qb->andWhere('d.depot = :depot')
               ->setParameter('depot', $depotId);
        }
        
        return $qb->orderBy('d.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function findFaulty($depotId = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', Device::STATUS_FAULTY);
        
        if ($depotId) {
            $qb->andWhere('d.depot = :depot')
               ->setParameter('depot', $depotId);
        }
        
        return $qb->orderBy('d.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function findInRepair($depotId = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', Device::STATUS_IN_REPAIR);
        
        if ($depotId) {
            $qb->andWhere('d.depot = :depot')
               ->setParameter('depot', $depotId);
        }
        
        return $qb->orderBy('d.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    public function findBySerialNumber($serialNumber)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.serialNumber = :serialNumber')
            ->setParameter('serialNumber', $serialNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByQrCode($qrCode)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.qrCode = :qrCode')
            ->setParameter('qrCode', $qrCode)
            ->getQuery()
            ->getOneOrNullResult();
    }
}