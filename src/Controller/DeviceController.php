<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Form\DeviceType;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для управления устройствами
 */
class DeviceController extends AbstractController
{
    private DeviceRepository $deviceRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DeviceRepository $deviceRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->deviceRepository = $deviceRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/devices', name: 'app_device')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', '');
        $status = $request->query->get('status', '');
        
        // Get filtered devices from repository
        $queryBuilder = $this->deviceRepository->createQueryBuilder('d')
            ->orderBy('d.name', 'ASC');
        
        if (!empty($search)) {
            $queryBuilder->andWhere('d.name LIKE :search OR d.serialNumber LIKE :search OR d.qrCode LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        if (!empty($type)) {
            $queryBuilder->andWhere('d.type = :type')
                ->setParameter('type', $type);
        }
        
        if (!empty($status)) {
            // Convert string status to StatusEnum
            $statusEnum = StatusEnum::tryFrom($status);
            if ($statusEnum) {
                $queryBuilder->andWhere('d.status = :status')
                    ->setParameter('status', $statusEnum);
            }
        }
        
        $devices = $queryBuilder->getQuery()->getResult();
        
        // Format devices for display
        $formattedDevices = array_map(function($device) {
            return [
                'id' => $device->getId(),
                'name' => $device->getName(),
                'type' => $device->getType(),
                'typeFormatted' => $this->formatDeviceType($device->getType()),
                'serialNumber' => $device->getSerialNumber(),
                'qrCode' => $device->getQrCode(),
                'status' => $device->getStatus(),
                'statusFormatted' => $this->formatDeviceStatus($device->getStatus()),
                'statusBadgeClass' => $this->getStatusBadgeClass($device->getStatus()),
                'depot' => $device->getDepot(),
                'depotName' => $device->getDepot() ? $device->getDepot()->getName() : null,
                'createdAt' => $device->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $device->getUpdatedAt() ? $device->getUpdatedAt()->format('d.m.Y') : null,
                'writeOffDate' => $device->getWriteOffDate() ? $device->getWriteOffDate()->format('d.m.Y') : null,
                'writeOffComment' => $device->getWriteOffComment(),
                'repairComment' => $device->getRepairComment(),
            ];
        }, $devices);
        
        return $this->render('device/index.html.twig', [
            'devices' => $formattedDevices,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'statusEnum' => StatusEnum::class
        ]);
    }

    #[Route('/devices/edit/{id}', name: 'app_device_edit')]
    public function edit(Request $request, int $id = null): Response
    {
        if ($id) {
            $device = $this->deviceRepository->find($id);
            if (!$device) {
                $this->addFlash('error', 'Устройство не найдено.');
                return $this->redirectToRoute('app_device');
            }
            $originalStatus = $device->getStatus(); // Запоминаем оригинальный статус
            $pageTitle = 'Редактирование устройства';
        } else {
            $device = new Device();
            $device->setStatus(StatusEnum::AVAILABLE); // Установка статуса по умолчанию для нового устройства
            $originalStatus = StatusEnum::AVAILABLE;
            $pageTitle = 'Добавление устройства';
        }
        
        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Возвращаем оригинальный статус - не даем его изменить через форму
            $device->setStatus($originalStatus);
            
            $this->entityManager->persist($device);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Устройство успешно сохранено.');
            return $this->redirectToRoute('app_device');
        }
        
        return $this->render('device/edit.html.twig', [
            'form' => $form->createView(),
            'device' => $device,
            'pageTitle' => $pageTitle,
        ]);
    }

    #[Route('/devices/delete/{id}', name: 'app_device_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $device = $this->deviceRepository->find($id);
        if (!$device) {
            $this->addFlash('error', 'Устройство не найдено.');
            return $this->redirectToRoute('app_device');
        }
        
        if ($this->isCsrfTokenValid('delete' . $device->getId(), $request->request->get('_token'))) {
            // Изменяем status на "Списано" вместо удаления
            $device->setStatus(StatusEnum::WRITTEN_OFF);
            $device->setWriteOffDate(new \DateTime());
            $this->entityManager->flush();
            $this->addFlash('success', 'Устройство успешно списано.');
        }
        
        return $this->redirectToRoute('app_device');
    }

    #[Route('/devices/change-status/{id}/{status}', name: 'app_device_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, int $id, string $status): Response
    {
        // Отключаем этот метод, возвращая ошибку
        $this->addFlash('error', 'Изменение статуса не поддерживается. Используйте соответствующие операции выдачи и возврата устройств.');
        return $this->redirectToRoute('app_device');
    }

    #[Route('/devices/return', name: 'app_device_return')]
    public function return(): Response
    {
        return $this->render('device/return.html.twig', [
            'controller_name' => 'DeviceController',
        ]);
    }
    
    /**
     * Format device type for display
     */
    private function formatDeviceType(string $type): string
    {
        return match($type) {
            'radio' => 'Радиостанция',
            'carrier' => 'Носитель информации',
            'security' => 'Устройство безопасности',
            default => $type,
        };
    }
    
    /**
     * Format device status for display
     */
    private function formatDeviceStatus(StatusEnum $status): string
    {
        return match($status) {
            StatusEnum::AVAILABLE => 'Доступно',
            StatusEnum::ISSUED => 'Выдано',
            StatusEnum::FAULTY => 'Неисправно',
            StatusEnum::IN_REPAIR => 'В ремонте',
            StatusEnum::WRITTEN_OFF => 'Списано',
        };
    }
    
    /**
     * Get Bootstrap badge class for status
     */
    private function getStatusBadgeClass(StatusEnum $status): string
    {
        return match($status) {
            StatusEnum::AVAILABLE => 'bg-success',
            StatusEnum::ISSUED => 'bg-warning text-dark',
            StatusEnum::FAULTY => 'bg-danger',
            StatusEnum::IN_REPAIR => 'bg-info',
            StatusEnum::WRITTEN_OFF => 'bg-secondary',
        };
    }
}
