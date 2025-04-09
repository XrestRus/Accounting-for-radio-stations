<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Form\DeviceType;
use App\Repository\DeviceRepository;
use App\Repository\TransactionRepository;
use App\Repository\LogRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Контроллер для управления устройствами
 */
class DeviceController extends AbstractController
{
    private DeviceRepository $deviceRepository;
    private EntityManagerInterface $entityManager;
    private TransactionRepository $transactionRepository;
    private LogService $logService;
    private SluggerInterface $slugger;
    private string $deviceImagesDirectory;
    private LogRepository $logRepository;

    public function __construct(
        DeviceRepository $deviceRepository,
        EntityManagerInterface $entityManager,
        TransactionRepository $transactionRepository,
        LogService $logService,
        SluggerInterface $slugger,
        LogRepository $logRepository,
        string $deviceImagesDirectory = 'uploads/device_images'
    ) {
        $this->deviceRepository = $deviceRepository;
        $this->entityManager = $entityManager;
        $this->transactionRepository = $transactionRepository;
        $this->logService = $logService;
        $this->slugger = $slugger;
        $this->deviceImagesDirectory = $deviceImagesDirectory;
        $this->logRepository = $logRepository;
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
            $activeTransaction = null;
            $isOverdue = false;
            
            // Получаем информацию о текущей активной транзакции для устройства
            if ($device->getStatus() === StatusEnum::ISSUED) {
                $activeTransaction = $this->transactionRepository->findActiveByDevice($device->getId());
                if ($activeTransaction) {
                    $isOverdue = $activeTransaction->isOverdue();
                }
            }
            
            // Получаем логи для устройства
            $deviceLogs = $this->logRepository->findByDevice($device->getId());
            $formattedLogs = [];
            
            foreach ($deviceLogs as $log) {
                $formattedLogs[] = [
                    'action' => $log->getAction(),
                    'details' => $log->getDetails(),
                    'username' => $log->getUser()->getUsername(),
                    'timestamp' => $log->getTimestamp()->format('d.m.Y H:i:s')
                ];
            }
            
            return [
                'id' => $device->getId(),
                'name' => $device->getName(),
                'type' => $device->getType(),
                'typeFormatted' => $this->formatDeviceType($device->getType()),
                'serialNumber' => $device->getSerialNumber(),
                'qrCode' => $device->getQrCode(),
                'status' => $device->getStatus(),
                'statusFormatted' => $device->formatStatus(),
                'statusBadgeClass' => $device->getStatusBadgeClass(),
                'depot' => $device->getDepot(),
                'depotName' => $device->getDepot() ? $device->getDepot()->getName() : null,
                'createdAt' => $device->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $device->getUpdatedAt() ? $device->getUpdatedAt()->format('d.m.Y') : null,
                'writeOffDate' => $device->getWriteOffDate() ? $device->getWriteOffDate()->format('d.m.Y') : null,
                'writeOffComment' => $device->getWriteOffComment(),
                'repairComment' => $device->getRepairComment(),
                'issuedTo' => $activeTransaction ? $activeTransaction->getEmployee()->getFullName() : null,
                'dueDate' => $activeTransaction ? $activeTransaction->getDueDate()->format('d.m.Y') : null,
                'isOverdue' => $isOverdue,
                'imageName' => $device->getImageName(),
                'logs' => $formattedLogs
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
            $isNewDevice = false;
        } else {
            $device = new Device();
            $device->setStatus(StatusEnum::AVAILABLE); // Установка статуса по умолчанию для нового устройства
            $originalStatus = StatusEnum::AVAILABLE;
            $pageTitle = 'Добавление устройства';
            $isNewDevice = true;
        }
        
        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Обработка загруженного изображения
            $deviceImageFile = $form->get('deviceImage')->getData();
            
            if ($deviceImageFile) {
                $originalFilename = pathinfo($deviceImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $deviceImageFile->guessExtension();
                
                // Перемещаем файл в директорию для изображений устройств
                try {
                    $deviceImageFile->move(
                        $this->getParameter('device_images_directory'),
                        $newFilename
                    );
                    
                    // Удаляем старое изображение, если оно существует
                    $oldImageName = $device->getImageName();
                    if ($oldImageName) {
                        $oldImagePath = $this->getParameter('device_images_directory') . '/' . $oldImageName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    // Сохраняем новое имя файла
                    $device->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Произошла ошибка при загрузке изображения: ' . $e->getMessage());
                }
            }
            
            // Возвращаем оригинальный статус - не даем его изменить через форму
            $device->setStatus($originalStatus);
            
            $this->entityManager->persist($device);
            $this->entityManager->flush();
            
            // Логируем операцию
            if ($isNewDevice) {
                $this->logService->logDeviceCreate($device);
            } else {
                $this->logService->logDeviceEdit($device);
            }
            
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
            $oldStatus = $device->getStatus()->value;
            $device->setStatus(StatusEnum::WRITTEN_OFF);
            $device->setWriteOffDate(new \DateTime());
            
            $writeOffComment = $request->request->get('writeOffComment');
            if ($writeOffComment) {
                $device->setWriteOffComment($writeOffComment);
            }
            
            $this->entityManager->flush();
            
            // Логируем операцию списания
            $this->logService->logDeviceWriteOff($device, $writeOffComment);
            
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
     * Переводит устройство в ремонт
     */
    #[Route('/devices/send-to-repair/{id}', name: 'app_device_send_to_repair', methods: ['POST'])]
    public function sendToRepair(Request $request, int $id): Response
    {
        $device = $this->deviceRepository->find($id);
        if (!$device) {
            $this->addFlash('error', 'Устройство не найдено.');
            return $this->redirectToRoute('app_device');
        }
        
        // Проверяем, что устройство может быть отправлено в ремонт
        if (!in_array($device->getStatus(), [StatusEnum::AVAILABLE, StatusEnum::FAULTY])) {
            $this->addFlash('error', 'Устройство не может быть отправлено в ремонт, так как имеет статус: ' . 
                           $device->formatStatus());
            return $this->redirectToRoute('app_device');
        }
        
        if ($this->isCsrfTokenValid('repair' . $device->getId(), $request->request->get('_token'))) {
            // Получаем комментарий о причине ремонта
            $repairComment = $request->request->get('repairComment');
            if ($repairComment) {
                $device->setRepairComment($repairComment);
            }
            
            // Изменяем статус на "В ремонте"
            $device->setStatus(StatusEnum::IN_REPAIR);
            
            // Сохраняем изменения
            $this->entityManager->flush();
            
            // Логируем операцию
            $this->logService->logDeviceSendToRepair($device, $repairComment);
            
            $this->addFlash('success', 'Устройство успешно отправлено в ремонт.');
        }
        
        return $this->redirectToRoute('app_device');
    }
    
    /**
     * Возвращает устройство из ремонта
     */
    #[Route('/devices/return-from-repair/{id}', name: 'app_device_return_from_repair', methods: ['POST'])]
    public function returnFromRepair(Request $request, int $id): Response
    {
        $device = $this->deviceRepository->find($id);
        if (!$device) {
            $this->addFlash('error', 'Устройство не найдено.');
            return $this->redirectToRoute('app_device');
        }
        
        // Проверяем, что устройство находится в ремонте
        if ($device->getStatus() != StatusEnum::IN_REPAIR) {
            $this->addFlash('error', 'Устройство не может быть возвращено из ремонта, так как имеет статус: ' . 
                           $device->formatStatus());
            return $this->redirectToRoute('app_device');
        }
        
        if ($this->isCsrfTokenValid('return-repair' . $device->getId(), $request->request->get('_token'))) {
            // Получаем результат ремонта и новый статус
            $repairResult = $request->request->get('repairResult');
            $newStatus = $request->request->get('repairStatus');
            
            // Обновляем комментарий о ремонте
            $oldComment = $device->getRepairComment() ? $device->getRepairComment() . "\n" : "";
            $device->setRepairComment($oldComment . "Результат ремонта: " . $repairResult);
            
            // Устанавливаем новый статус (исправно или неисправно)
            if ($newStatus === 'available') {
                $device->setStatus(StatusEnum::AVAILABLE);
                $statusText = 'доступно';
            } else {
                $device->setStatus(StatusEnum::FAULTY);
                $statusText = 'неисправно';
            }
            
            // Сохраняем изменения
            $this->entityManager->flush();
            
            // Логируем операцию
            $this->logService->logDeviceReturnFromRepair($device, $repairResult, $statusText);
            
            $this->addFlash('success', 'Устройство успешно возвращено из ремонта.');
        }
        
        return $this->redirectToRoute('app_device');
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
}
