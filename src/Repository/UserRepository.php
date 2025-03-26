<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий для работы с пользователями системы
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Находит всех неудаленных пользователей
     */
    public function findAll(): array
    {
        return $this->findBy(['deletedAt' => null]);
    }

    /**
     * Находит неудаленного пользователя по ID
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?User
    {
        /** @var User|null $user */
        $user = parent::find($id, $lockMode, $lockVersion);
        
        if ($user && $user->isDeleted()) {
            return null;
        }
        
        return $user;
    }

    /**
     * Находит неудаленных пользователей по заданным критериям
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        // Если нет явного критерия для deletedAt, добавляем фильтр по неудаленным пользователям
        if (!isset($criteria['deletedAt'])) {
            $criteria['deletedAt'] = null;
        }
        
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Находит неудаленных пользователей по депо
     */
    public function findByDepot($depotId)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.depot = :depot')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('depot', $depotId)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит неудаленных пользователей с ролью оператора
     */
    public function findOperators()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('role', 'operator')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит неудаленных пользователей с ролью администратора
     */
    public function findAdmins()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('role', 'admin')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит всех пользователей, включая удаленных
     */
    public function findAllWithDeleted(): array
    {
        return parent::findAll();
    }
} 