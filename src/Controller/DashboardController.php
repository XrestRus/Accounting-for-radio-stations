<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Repository\DeviceRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для главного экрана (Dashboard) системы учета устройств
 */
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(DeviceRepository $deviceRepository, TransactionRepository $transactionRepository): Response
    {
        // Получаем статистику по устройствам
        $totalDevices = $deviceRepository->count([]);
        $availableDevices = $deviceRepository->count(['status' => StatusEnum::AVAILABLE]);
        
        // Получаем активные (выданные) транзакции
        $activeTransactions = $transactionRepository->findActiveTransactions();
        $issuedDevicesCount = count($activeTransactions);
        
        // Получаем просроченные транзакции
        $overdueTransactions = $transactionRepository->findOverdueTransactions();
        $overdueDevicesCount = count($overdueTransactions);
        
        // Получаем последние 5 транзакций (включая и выдачу, и возврат)
        $recentTransactions = $transactionRepository->findByFilters(
            null, 
            null, 
            null, 
            null, 
            null, 
            null
        );
        $recentTransactions = array_slice($recentTransactions, 0, 5);
        
        // Текущая дата и время для отображения "Последнее обновление"
        $lastUpdate = new \DateTime();
        
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'total_devices' => $totalDevices,
            'available_devices' => $availableDevices,
            'issued_devices' => $issuedDevicesCount,
            'overdue_devices' => $overdueDevicesCount,
            'active_transactions' => $activeTransactions,
            'overdue_transactions' => $overdueTransactions,
            'recent_transactions' => $recentTransactions,
            'last_update' => $lastUpdate->format('d.m.Y, H:i')
        ]);
    }
}
