<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Entity\Transaction;
use App\Form\IssueDeviceType;
use App\Repository\DeviceRepository;
use App\Repository\EmployeeRepository;
use App\Repository\TransactionRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для выдачи устройств сотрудникам
 */
#[Route('/devices')]
class IssueController extends AbstractController
{
    private DeviceRepository $deviceRepository;
    private EmployeeRepository $employeeRepository;
    private TransactionRepository $transactionRepository;
    private EntityManagerInterface $entityManager;
    private LogService $logService;

    public function __construct(
        DeviceRepository $deviceRepository,
        EmployeeRepository $employeeRepository,
        TransactionRepository $transactionRepository,
        EntityManagerInterface $entityManager,
        LogService $logService
    ) {
        $this->deviceRepository = $deviceRepository;
        $this->employeeRepository = $employeeRepository;
        $this->transactionRepository = $transactionRepository;
        $this->entityManager = $entityManager;
        $this->logService = $logService;
    }

    #[Route('/issue', name: 'app_device_issue')]
    public function issue(Request $request): Response
    {
        // Создаем новую транзакцию
        $transaction = new Transaction();
        
        // Устанавливаем текущую дату и пользователя, выдающего устройство
        $transaction->setIssuedAt(new \DateTime());
        
        // Получаем текущего пользователя
        $user = $this->getUser();
        
        // Если пользователь не авторизован, возвращаем на страницу логина
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $transaction->setIssuedBy($user);
        
        // Устанавливаем срок возврата по умолчанию (конец текущего дня)
        $dueDate = new \DateTime();
        // Устанавливаем время на конец дня без секунд
        $newDueDate = new \DateTime($dueDate->format('Y-m-d 23:59:00'));
        $transaction->setDueDate($newDueDate);
        
        // Создаем форму выдачи устройства
        $form = $this->createForm(IssueDeviceType::class, $transaction);
        $form->handleRequest($request);

        $device = null;
        $deviceInfo = null;
        $error = null;
        
        // Обработка формы при GET запросе с параметром deviceIdentifier
        $deviceIdentifier = $request->query->get('deviceIdentifier');
        if ($deviceIdentifier) {
            $device = $this->findDeviceByIdentifier($deviceIdentifier);
            if ($device) {
                if ($device->getStatus() == StatusEnum::AVAILABLE) {
                    $deviceInfo = $this->formatDeviceInfo($device);
                } else {
                    $error = 'Устройство не может быть выдано, так как имеет статус: ' . 
                             $this->formatDeviceStatus($device->getStatus());
                }
            } else {
                $error = 'Устройство с указанным серийным номером или QR-кодом не найдено';
            }
        }
        
        // Обработка отправки формы
        if ($form->isSubmitted() && $form->isValid()) {
            $deviceIdentifier = $form->get('deviceIdentifier')->getData();
            $device = $this->findDeviceByIdentifier($deviceIdentifier);
            
            if (!$device) {
                $this->addFlash('error', 'Устройство с указанным серийным номером или QR-кодом не найдено');
                return $this->redirectToRoute('app_device_issue');
            }
            
            if ($device->getStatus() != StatusEnum::AVAILABLE) {
                $this->addFlash('error', 'Устройство не может быть выдано, так как имеет статус: ' . 
                                      $this->formatDeviceStatus($device->getStatus()));
                return $this->redirectToRoute('app_device_issue');
            }
            
            // Устанавливаем устройство в транзакции
            $transaction->setDevice($device);
            
            // Обнуляем секунды в дате возврата
            $dueDate = $transaction->getDueDate();
            if ($dueDate instanceof \DateTimeInterface) {
                // Создаем новый DateTime объект с тем же значением, но без секунд
                $newDueDate = new \DateTime($dueDate->format('Y-m-d H:i:00'));
                $transaction->setDueDate($newDueDate);
            }
            
            // Изменяем статус устройства на "Выдано"
            $device->setStatus(StatusEnum::ISSUED);
            
            // Сохраняем изменения в базе данных
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();
            
            // Логируем операцию выдачи устройства
            $this->logService->logDeviceIssue(
                $device, 
                $transaction->getEmployee()->getFullName(), 
                $transaction->getDueDate()
            );
            
            $this->addFlash('success', sprintf(
                'Устройство "%s" успешно выдано сотруднику "%s" до %s',
                $device->getName(),
                $transaction->getEmployee()->getFullName(),
                $transaction->getDueDate()->format('d.m.Y H:i')
            ));
            
            return $this->redirectToRoute('app_device');
        }
        
        // Получаем список активных сотрудников
        $employees = $this->employeeRepository->findActive();
        
        // Получаем список доступных устройств для выбора из списка
        $availableDevices = $this->deviceRepository->findAvailable();
        
        // Форматируем устройства для отображения
        $formattedDevices = array_map(function($device) {
            return $this->formatDeviceInfo($device);
        }, $availableDevices);
        
        return $this->render('device/issue.html.twig', [
            'form' => $form->createView(),
            'employees' => $employees,
            'device' => $deviceInfo,
            'error' => $error,
            'devices' => $formattedDevices,
        ]);
    }

    #[Route('/get-device-info', name: 'app_device_get_info', methods: ['GET'])]
    public function getDeviceInfo(Request $request): JsonResponse
    {
        $identifier = $request->query->get('identifier');
        
        if (!$identifier) {
            return new JsonResponse(['error' => 'Не указан идентификатор устройства'], Response::HTTP_BAD_REQUEST);
        }
        
        $device = $this->findDeviceByIdentifier($identifier);
        
        if (!$device) {
            return new JsonResponse(['error' => 'Устройство не найдено'], Response::HTTP_NOT_FOUND);
        }
        
        if ($device->getStatus() != StatusEnum::AVAILABLE) {
            return new JsonResponse([
                'error' => 'Устройство не может быть выдано, так как имеет статус: ' . 
                          $this->formatDeviceStatus($device->getStatus())
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return new JsonResponse(['device' => $this->formatDeviceInfo($device)]);
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
    private function formatDeviceInfo(Device $device): array
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
     * Получение CSS-класса для отображения статуса
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