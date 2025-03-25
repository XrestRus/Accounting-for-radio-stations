<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Контроллер для работы с журналом учета операций
 */
class JournalController extends AbstractController
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Отображает журнал учета с фильтрацией
     */
    #[Route('/journal', name: 'app_journal')]
    public function index(Request $request): Response
    {
        // Получаем параметры фильтрации из запроса
        $dateFrom = $request->query->get('dateFrom', '');
        $dateTo = $request->query->get('dateTo', '');
        $operationType = $request->query->get('operationType', '');
        $employee = $request->query->get('employee', '');
        $device = $request->query->get('device', '');
        $status = $request->query->get('status', '');
        
        // Преобразуем строковые даты в объекты DateTime
        $dateFromObj = !empty($dateFrom) ? new \DateTime($dateFrom) : null;
        $dateToObj = !empty($dateTo) ? new \DateTime($dateTo) : null;
        
        // Добавляем время до конца дня для даты окончания
        if ($dateToObj) {
            $dateToObj->setTime(23, 59, 59);
        }
        
        // Получаем список транзакций с применением фильтров
        $transactions = $this->transactionRepository->findByFilters(
            $dateFromObj, 
            $dateToObj, 
            $operationType, 
            $employee, 
            $device, 
            $status
        );
        
        // Форматируем данные для отображения в шаблоне
        $formattedTransactions = [];
        foreach ($transactions as $transaction) {
            $formattedTransactions[] = [
                'id' => $transaction->getId(),
                'date' => $this->getOperationDate($transaction),
                'type' => $this->getOperationType($transaction),
                'typeClass' => $this->getOperationTypeClass($transaction),
                'device' => $transaction->getDevice()->getName(),
                'deviceId' => $transaction->getDevice()->getId(),
                'serialNumber' => $transaction->getDevice()->getSerialNumber(),
                'employee' => $transaction->getEmployee()->getFullName(),
                'employeeId' => $transaction->getEmployee()->getId(),
                'dueDate' => $transaction->getDueDate()->format('d.m.Y'),
                'status' => $this->getTransactionStatus($transaction),
                'statusClass' => $this->getTransactionStatusClass($transaction),
                'issuedBy' => $transaction->getIssuedBy()->getUsername(),
                'issuedById' => $transaction->getIssuedBy()->getId(),
            ];
        }
        
        return $this->render('journal/index.html.twig', [
            'transactions' => $formattedTransactions,
            'filters' => [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'operationType' => $operationType,
                'employee' => $employee,
                'device' => $device,
                'status' => $status,
            ],
        ]);
    }
    
    /**
     * Возвращает дату операции в зависимости от типа
     */
    private function getOperationDate(Transaction $transaction): string
    {
        if ($transaction->getReturnedAt()) {
            return $transaction->getReturnedAt()->format('d.m.Y H:i');
        }
        
        return $transaction->getIssuedAt()->format('d.m.Y H:i');
    }
    
    /**
     * Определяет тип операции
     */
    private function getOperationType(Transaction $transaction): string
    {
        if ($transaction->getReturnedAt()) {
            return 'Возврат';
        }
        
        return 'Выдача';
    }
    
    /**
     * Возвращает класс Bootstrap для бейджа типа операции
     */
    private function getOperationTypeClass(Transaction $transaction): string
    {
        if ($transaction->getReturnedAt()) {
            return 'bg-success';
        }
        
        return 'bg-primary';
    }
    
    /**
     * Определяет статус транзакции
     */
    private function getTransactionStatus(Transaction $transaction): string
    {
        if ($transaction->getReturnedAt()) {
            return $transaction->getReturnStatus() === Transaction::RETURN_STATUS_RETURNED_OK ? 
                'Возвращено' : 'Возвращено с неисправностью';
        }
        
        if ($transaction->isOverdue()) {
            return 'Просрочено';
        }
        
        return 'Выдано';
    }
    
    /**
     * Возвращает класс Bootstrap для бейджа статуса транзакции
     */
    private function getTransactionStatusClass(Transaction $transaction): string
    {
        if ($transaction->getReturnedAt()) {
            return $transaction->getReturnStatus() === Transaction::RETURN_STATUS_RETURNED_OK ? 
                'bg-success' : 'bg-danger';
        }
        
        if ($transaction->isOverdue()) {
            return 'bg-danger';
        }
        
        return 'bg-warning text-dark';
    }

    /**
     * Экспортирует журнал учета в Excel
     */
    #[Route('/journal/export', name: 'app_journal_export')]
    public function export(Request $request): Response
    {
        // Получаем параметры фильтрации из запроса (те же, что и для отображения)
        $dateFrom = $request->query->get('dateFrom', '');
        $dateTo = $request->query->get('dateTo', '');
        $operationType = $request->query->get('operationType', '');
        $employee = $request->query->get('employee', '');
        $device = $request->query->get('device', '');
        $status = $request->query->get('status', '');
        
        // Преобразуем строковые даты в объекты DateTime
        $dateFromObj = !empty($dateFrom) ? new \DateTime($dateFrom) : null;
        $dateToObj = !empty($dateTo) ? new \DateTime($dateTo) : null;
        
        // Добавляем время до конца дня для даты окончания
        if ($dateToObj) {
            $dateToObj->setTime(23, 59, 59);
        }
        
        // Получаем список транзакций с применением фильтров
        $transactions = $this->transactionRepository->findByFilters(
            $dateFromObj, 
            $dateToObj, 
            $operationType, 
            $employee, 
            $device, 
            $status
        );
        
        // Создаем новый Excel-документ
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Устанавливаем заголовки столбцов
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Дата и время');
        $sheet->setCellValue('C1', 'Тип операции');
        $sheet->setCellValue('D1', 'Устройство');
        $sheet->setCellValue('E1', 'Серийный номер');
        $sheet->setCellValue('F1', 'Сотрудник');
        $sheet->setCellValue('G1', 'Срок возврата');
        $sheet->setCellValue('H1', 'Статус');
        $sheet->setCellValue('I1', 'Ответственный');
        
        // Применяем стили к заголовкам
        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E0E0E0',
                ],
            ],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyleArray);
        
        // Заполняем данные
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction->getId());
            $sheet->setCellValue('B' . $row, $this->getOperationDate($transaction));
            $sheet->setCellValue('C' . $row, $this->getOperationType($transaction));
            $sheet->setCellValue('D' . $row, $transaction->getDevice()->getName());
            $sheet->setCellValue('E' . $row, $transaction->getDevice()->getSerialNumber());
            $sheet->setCellValue('F' . $row, $transaction->getEmployee()->getFullName());
            $sheet->setCellValue('G' . $row, $transaction->getDueDate()->format('d.m.Y'));
            $sheet->setCellValue('H' . $row, $this->getTransactionStatus($transaction));
            $sheet->setCellValue('I' . $row, $transaction->getIssuedBy()->getUsername());
            $row++;
        }
        
        // Автоматическая ширина столбцов
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Создаем объект Writer для записи в формате Excel
        $writer = new Xlsx($spreadsheet);
        
        // Создаем временный файл
        $fileName = 'journal_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);
        
        // Возвращаем файл как ответ
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
