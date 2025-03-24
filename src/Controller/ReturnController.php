<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Entity\Transaction;
use App\Form\ReturnDeviceType;
use App\Repository\DeviceRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для обработки возврата устройств от сотрудников
 */
#[Route('/devices')]
class ReturnController extends AbstractController
{
    private DeviceRepository $deviceRepository;
    private TransactionRepository $transactionRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DeviceRepository $deviceRepository,
        TransactionRepository $transactionRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->deviceRepository = $deviceRepository;
        $this->transactionRepository = $transactionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Отображает форму возврата устройства и обрабатывает возврат
     */
    #[Route('/return', name: 'app_device_return')]
    public function return(Request $request): Response
    {
        $transaction = null;
        $error = null;
        $deviceInfo = null;
        
        // Обработка GET запроса с параметром deviceIdentifier
        $deviceIdentifier = $request->query->get('deviceIdentifier');
        if ($deviceIdentifier) {
            $device = $this->findDeviceByIdentifier($deviceIdentifier);
            if ($device) {
                if ($device->getStatus() == StatusEnum::ISSUED) {
                    // Ищем активную транзакцию для этого устройства
                    $transaction = $this->transactionRepository->findActiveByDevice($device->getId());
                    if ($transaction) {
                        $deviceInfo = $this->formatDeviceAndEmployeeInfo($device, $transaction);
                    } else {
                        $error = 'В системе не найдена информация о выдаче этого устройства';
                    }
                } else {
                    $error = 'Устройство не может быть принято, так как имеет статус: ' . 
                             $this->formatDeviceStatus($device->getStatus());
                }
            } else {
                $error = 'Устройство с указанным серийным номером или QR-кодом не найдено';
            }
        }

        // Если транзакция найдена, создаем форму
        $form = $this->createForm(ReturnDeviceType::class, $transaction);
        $form->handleRequest($request);

        // Обработка отправки формы
        if ($form->isSubmitted() && $form->isValid()) {
            $deviceIdentifier = $form->get('deviceIdentifier')->getData();
            $device = $this->findDeviceByIdentifier($deviceIdentifier);
            
            if (!$device) {
                $this->addFlash('error', 'Устройство с указанным серийным номером или QR-кодом не найдено');
                return $this->redirectToRoute('app_device_return');
            }
            
            if ($device->getStatus() != StatusEnum::ISSUED) {
                $this->addFlash('error', 'Устройство не может быть принято, так как имеет статус: ' . 
                                      $this->formatDeviceStatus($device->getStatus()));
                return $this->redirectToRoute('app_device_return');
            }
            
            // Ищем активную транзакцию для этого устройства
            $transaction = $this->transactionRepository->findActiveByDevice($device->getId());
            if (!$transaction) {
                $this->addFlash('error', 'В системе не найдена информация о выдаче этого устройства');
                return $this->redirectToRoute('app_device_return');
            }
            
            // Обновляем поля транзакции
            $transaction->setReturnedAt(new \DateTime());
            $transaction->setReturnStatus($form->get('returnStatus')->getData());
            
            // Получаем текущего пользователя
            $user = $this->getUser();
            
            // Если пользователь не авторизован, получаем первого пользователя из базы данных
            // Это временное решение до полной настройки авторизации
            if (!$user) {
                $userRepository = $this->entityManager->getRepository('App\Entity\User');
                $user = $userRepository->findOneBy([]);
                
                if (!$user) {
                    $this->addFlash('error', 'Система не может выполнить операцию: не найден пользователь для приема устройства');
                    return $this->redirectToRoute('app_device');
                }
            }
            
            // Обновляем статус устройства в зависимости от состояния при возврате
            if ($transaction->getReturnStatus() === Transaction::RETURN_STATUS_RETURNED_OK) {
                $device->setStatus(StatusEnum::AVAILABLE);
            } else {
                $device->setStatus(StatusEnum::FAULTY);
            }
            
            // Сохраняем изменения в базе данных
            $this->entityManager->flush();
            
            $this->addFlash('success', sprintf(
                'Устройство "%s" успешно принято от сотрудника "%s"',
                $device->getName(),
                $transaction->getEmployee()->getFullName()
            ));
            
            return $this->redirectToRoute('app_device');
        }
        
        return $this->render('device/return.html.twig', [
            'form' => $form->createView(),
            'device' => $deviceInfo,
            'error' => $error,
        ]);
    }

    /**
     * Предоставляет информацию о выданном устройстве в формате JSON
     */
    #[Route('/get-issued-device-info', name: 'app_device_get_issued_info', methods: ['GET'])]
    public function getIssuedDeviceInfo(Request $request): JsonResponse
    {
        $identifier = $request->query->get('identifier');
        
        if (!$identifier) {
            return new JsonResponse(['error' => 'Не указан идентификатор устройства'], Response::HTTP_BAD_REQUEST);
        }
        
        $device = $this->findDeviceByIdentifier($identifier);
        
        if (!$device) {
            return new JsonResponse(['error' => 'Устройство не найдено'], Response::HTTP_NOT_FOUND);
        }
        
        if ($device->getStatus() != StatusEnum::ISSUED) {
            return new JsonResponse([
                'error' => 'Устройство не может быть возвращено, так как имеет статус: ' . 
                          $this->formatDeviceStatus($device->getStatus())
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Получаем транзакцию выдачи для этого устройства
        $transaction = $this->transactionRepository->findActiveByDevice($device->getId());
        
        if (!$transaction) {
            return new JsonResponse([
                'error' => 'В системе не найдена информация о выдаче этого устройства'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return new JsonResponse([
            'device' => $this->formatDeviceAndEmployeeInfo($device, $transaction)
        ]);
    }

    /**
     * Поиск устройства по серийному номеру или QR-коду
     */
    private function findDeviceByIdentifier(string $identifier): ?Device
    {
        // Сначала ищем по QR-коду
        $device = $this->deviceRepository->findByQrCode($identifier);
        
        // Если не найдено, ищем по серийному номеру
        if (!$device) {
            $device = $this->deviceRepository->findBySerialNumber($identifier);
        }
        
        return $device;
    }

    /**
     * Форматирование информации об устройстве для отображения
     */
    private function formatDeviceAndEmployeeInfo(Device $device, Transaction $transaction): array
    {
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
            'issuedAt' => $transaction->getIssuedAt()->format('d.m.Y H:i'),
            'dueDate' => $transaction->getDueDate()->format('d.m.Y H:i'),
            'isOverdue' => $transaction->isOverdue(),
            'employee' => [
                'id' => $transaction->getEmployee()->getId(),
                'fullName' => $transaction->getEmployee()->getFullName(),
                'position' => $transaction->getEmployee()->getPosition(),
                'department' => $transaction->getEmployee()->getDepartment(),
                'phone' => $transaction->getEmployee()->getPhone(),
            ]
        ];
    }

    /**
     * Форматирование типа устройства для отображения
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
     * Форматирование статуса устройства для отображения
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
     * Получение класса для отображения статуса устройства
     */
    private function getStatusBadgeClass(StatusEnum $status): string
    {
        return match($status) {
            StatusEnum::AVAILABLE => 'bg-success',
            StatusEnum::ISSUED => 'bg-primary',
            StatusEnum::FAULTY => 'bg-danger',
            StatusEnum::IN_REPAIR => 'bg-warning',
            StatusEnum::WRITTEN_OFF => 'bg-secondary',
        };
    }
} 