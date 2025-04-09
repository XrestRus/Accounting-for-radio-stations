<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\Log;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Сервис для логирования операций
 */
class LogService
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Создает запись лога для операции
     * 
     * @param string $action Название действия
     * @param Device|null $device Устройство (опционально)
     * @param string|null $details Детали операции (опционально)
     * @param array|null $detailsMeta Мета-данные операции (опционально)
     * @return Log Созданный лог
     */
    public function log(string $action, ?Device $device = null, ?string $details = null, ?array $detailsMeta = null): Log
    {
        /** @var User $user */
        $user = $this->security->getUser();
        
        $log = new Log();
        $log->setUser($user);
        $log->setAction($action);
        
        if ($device) {
            $log->setDevice($device);
        }
        
        if ($details) {
            $log->setDetails($details);
        }
        
        if ($detailsMeta) {
            $log->setDetailsMeta($detailsMeta);
        }
        
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        
        return $log;
    }
    
    /**
     * Логирует операцию добавления устройства
     * 
     * @param Device $device Добавленное устройство
     * @return Log Созданный лог
     */
    public function logDeviceCreate(Device $device): Log
    {
        return $this->log(
            'Добавление устройства',
            $device,
            sprintf('Добавлено устройство "%s" (S/N: %s)', $device->getName(), $device->getSerialNumber())
        );
    }
    
    /**
     * Логирует операцию редактирования устройства
     * 
     * @param Device $device Отредактированное устройство
     * @return Log Созданный лог
     */
    public function logDeviceEdit(Device $device): Log
    {
        return $this->log(
            'Редактирование устройства',
            $device,
            sprintf('Отредактировано устройство "%s" (S/N: %s)', $device->getName(), $device->getSerialNumber())
        );
    }
    
    /**
     * Логирует операцию изменения статуса устройства
     * 
     * @param Device $device Устройство
     * @param string $oldStatus Предыдущий статус
     * @param string $newStatus Новый статус
     * @return Log Созданный лог
     */
    public function logDeviceStatusChange(Device $device, string $oldStatus, string $newStatus): Log
    {
        return $this->log(
            'Изменение статуса устройства',
            $device,
            sprintf('Изменен статус устройства "%s" (S/N: %s) с "%s" на "%s"', 
                $device->getName(), 
                $device->getSerialNumber(),
                $oldStatus,
                $newStatus
            )
        );
    }
    
    /**
     * Логирует операцию выдачи устройства
     * 
     * @param Device $device Выданное устройство
     * @param string $employeeName Имя сотрудника
     * @param \DateTime $dueDate Дата возврата
     * @return Log Созданный лог
     */
    public function logDeviceIssue(Device $device, string $employeeName, \DateTime $dueDate): Log
    {
        return $this->log(
            'Выдача устройства',
            $device,
            sprintf('Устройство "%s" (S/N: %s) выдано сотруднику %s до %s', 
                $device->getName(), 
                $device->getSerialNumber(),
                $employeeName,
                $dueDate->format('d.m.Y')
            ),
            [
                'employee' => $employeeName,
                'dueDate' => $dueDate->format('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Логирует операцию возврата устройства
     * 
     * @param Device $device Возвращенное устройство
     * @param string $employeeName Имя сотрудника
     * @param string $returnStatus Статус возврата
     * @return Log Созданный лог
     */
    public function logDeviceReturn(Device $device, string $employeeName, string $returnStatus): Log
    {
        return $this->log(
            'Возврат устройства',
            $device,
            sprintf('Устройство "%s" (S/N: %s) возвращено сотрудником %s в состоянии "%s"', 
                $device->getName(), 
                $device->getSerialNumber(),
                $employeeName,
                $returnStatus
            ),
            [
                'employee' => $employeeName,
                'returnStatus' => $returnStatus,
                'returnDate' => (new \DateTime())->format('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Логирует операцию списания устройства
     * 
     * @param Device $device Списанное устройство
     * @param string|null $comment Комментарий о списании
     * @return Log Созданный лог
     */
    public function logDeviceWriteOff(Device $device, ?string $comment = null): Log
    {
        $details = sprintf('Устройство "%s" (S/N: %s) списано', 
            $device->getName(), 
            $device->getSerialNumber()
        );
        
        if ($comment) {
            $details .= sprintf('. Причина: %s', $comment);
        }
        
        return $this->log('Списание устройства', $device, $details);
    }
    
    /**
     * Логирует операцию отправки устройства в ремонт
     * 
     * @param Device $device Устройство, отправленное в ремонт
     * @param string|null $comment Комментарий о причине ремонта
     * @return Log Созданный лог
     */
    public function logDeviceSendToRepair(Device $device, ?string $comment = null): Log
    {
        $details = sprintf('Устройство "%s" (S/N: %s) отправлено в ремонт', 
            $device->getName(), 
            $device->getSerialNumber()
        );
        
        if ($comment) {
            $details .= sprintf('. Причина: %s', $comment);
        }
        
        return $this->log('Отправка устройства в ремонт', $device, $details);
    }
    
    /**
     * Логирует операцию возврата устройства из ремонта
     * 
     * @param Device $device Устройство, возвращенное из ремонта
     * @param string $result Результат ремонта
     * @param string $newStatus Новый статус устройства
     * @return Log Созданный лог
     */
    public function logDeviceReturnFromRepair(Device $device, string $result, string $newStatus): Log
    {
        return $this->log(
            'Возврат устройства из ремонта',
            $device,
            sprintf('Устройство "%s" (S/N: %s) возвращено из ремонта. Результат: %s. Новый статус: %s', 
                $device->getName(), 
                $device->getSerialNumber(),
                $result,
                $newStatus
            )
        );
    }
} 