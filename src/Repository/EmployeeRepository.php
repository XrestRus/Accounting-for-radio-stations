<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для работы с сущностью сотрудников
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * Находит сотрудников по идентификатору депо
     */
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

    /**
     * Находит всех активных сотрудников
     */
    public function findActive()
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.deletedAt IS NULL')
            ->orderBy('e.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит сотрудников по имени или его части
     */
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
    
    /**
     * Получает список всех уникальных подразделений
     */
    public function findAllDepartments()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DISTINCT department 
            FROM employees 
            WHERE department IS NOT NULL AND department != "" AND deleted_at IS NULL
            ORDER BY department ASC
        ';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        
        // Получаем список подразделений в виде одномерного массива
        $departments = array_map(function($row) {
            return $row['department'];
        }, $result->fetchAllAssociative());
        
        return $departments;
    }
} 