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
 * Контроллер для работы с отчетами по устройствам
 */
class ReportController extends AbstractController
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Отображает страницу с формой параметров отчета
     */
    #[Route('/reports', name: 'app_reports')]
    public function index(Request $request): Response
    {
        // Проверяем, авторизован ли пользователь
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Получаем параметры фильтрации из запроса
        $reportType = $request->query->get('reportType', '');
        $dateFrom = $request->query->get('dateFrom', '');
        $dateTo = $request->query->get('dateTo', '');
        
        // Если указан тип отчета, генерируем отчет
        if ($reportType) {
            // Получаем данные отчета в зависимости от типа
            $reportData = $this->getReportData($reportType, $dateFrom, $dateTo);
            
            // Если запрошен экспорт, формируем Excel-файл
            if ($request->query->get('export')) {
                return $this->exportReport($reportType, $reportData, $dateFrom, $dateTo);
            }
            
            // Отображаем результаты отчета
            return $this->render('report/results.html.twig', [
                'reportType' => $reportType,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'reportData' => $reportData,
            ]);
        }
        
        // Отображаем форму параметров отчета
        return $this->render('report/index.html.twig', [
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
    
    /**
     * Получает данные отчета в зависимости от типа
     */
    private function getReportData(string $reportType, string $dateFrom, string $dateTo): array
    {
        // Преобразуем строковые даты в объекты DateTime
        $dateFromObj = !empty($dateFrom) ? new \DateTime($dateFrom) : null;
        $dateToObj = !empty($dateTo) ? new \DateTime($dateTo) : null;
        
        // Добавляем время до конца дня для даты окончания
        if ($dateToObj) {
            $dateToObj->setTime(23, 59, 59);
        }
        
        $transactions = [];
        
        switch ($reportType) {
            case 'issued':
                // Отчет по выданным устройствам
                $transactions = $this->transactionRepository->findByFilters(
                    $dateFromObj,
                    $dateToObj,
                    'issue',
                    null,
                    null,
                    'issued'
                );
                break;
                
            case 'returned':
                // Отчет по возвращенным устройствам
                $transactions = $this->transactionRepository->findByFilters(
                    $dateFromObj,
                    $dateToObj,
                    'return',
                    null,
                    null,
                    'returned'
                );
                break;
                
            case 'overdue':
                // Отчет по просроченным возвратам
                $transactions = $this->transactionRepository->findByFilters(
                    $dateFromObj,
                    $dateToObj,
                    'issue',
                    null,
                    null,
                    'overdue'
                );
                break;
        }
        
        // Форматируем данные для отображения
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
        
        return $formattedTransactions;
    }
    
    /**
     * Экспортирует отчет в Excel
     */
    private function exportReport(string $reportType, array $reportData, string $dateFrom, string $dateTo): Response
    {
        // Создаем новый Excel-документ
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Устанавливаем заголовок отчета
        $reportTitle = $this->getReportTitle($reportType);
        $sheet->setCellValue('A1', $reportTitle);
        $sheet->mergeCells('A1:I1');
        
        // Добавляем информацию о периоде
        $periodText = '';
        if ($dateFrom && $dateTo) {
            $periodText = 'Период: с ' . $dateFrom . ' по ' . $dateTo;
        } elseif ($dateFrom) {
            $periodText = 'Период: с ' . $dateFrom;
        } elseif ($dateTo) {
            $periodText = 'Период: по ' . $dateTo;
        }
        
        if ($periodText) {
            $sheet->setCellValue('A2', $periodText);
            $sheet->mergeCells('A2:I2');
        }
        
        // Устанавливаем заголовки столбцов
        $row = $periodText ? 3 : 2;
        $sheet->setCellValue('A' . $row, 'ID');
        $sheet->setCellValue('B' . $row, 'Дата и время');
        $sheet->setCellValue('C' . $row, 'Тип операции');
        $sheet->setCellValue('D' . $row, 'Устройство');
        $sheet->setCellValue('E' . $row, 'Серийный номер');
        $sheet->setCellValue('F' . $row, 'Сотрудник');
        $sheet->setCellValue('G' . $row, 'Срок возврата');
        $sheet->setCellValue('H' . $row, 'Статус');
        $sheet->setCellValue('I' . $row, 'Ответственный');
        
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
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($headerStyleArray);
        
        // Заполняем данные
        $dataRow = $row + 1;
        foreach ($reportData as $item) {
            $sheet->setCellValue('A' . $dataRow, $item['id']);
            $sheet->setCellValue('B' . $dataRow, $item['date']);
            $sheet->setCellValue('C' . $dataRow, $item['type']);
            $sheet->setCellValue('D' . $dataRow, $item['device']);
            $sheet->setCellValue('E' . $dataRow, $item['serialNumber']);
            $sheet->setCellValue('F' . $dataRow, $item['employee']);
            $sheet->setCellValue('G' . $dataRow, $item['dueDate']);
            $sheet->setCellValue('H' . $dataRow, $item['status']);
            $sheet->setCellValue('I' . $dataRow, $item['issuedBy']);
            $dataRow++;
        }
        
        // Автоматическая ширина столбцов
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Настраиваем выравнивание текста в заголовке
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($periodText) {
            $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Создаем объект Writer для форматирования в XLSX
        $writer = new Xlsx($spreadsheet);
        
        // Формируем имя файла для сохранения
        $fileName = sprintf(
            'отчет_%s_%s.xlsx',
            $this->getReportFileNamePart($reportType),
            date('Y-m-d_H-i-s')
        );
        
        // Создаем временный файл
        $tempFile = tempnam(sys_get_temp_dir(), 'report_');
        $writer->save($tempFile);
        
        // Возвращаем файл для скачивания
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
    
    /**
     * Возвращает заголовок отчета в зависимости от типа
     */
    private function getReportTitle(string $reportType): string
    {
        switch ($reportType) {
            case 'issued':
                return 'Отчет по выданным устройствам';
            case 'returned':
                return 'Отчет по возвращенным устройствам';
            case 'overdue':
                return 'Отчет по просроченным возвратам';
            default:
                return 'Отчет';
        }
    }
    
    /**
     * Возвращает часть имени файла в зависимости от типа отчета
     */
    private function getReportFileNamePart(string $reportType): string
    {
        switch ($reportType) {
            case 'issued':
                return 'выданные';
            case 'returned':
                return 'возвращенные';
            case 'overdue':
                return 'просроченные';
            default:
                return 'общий';
        }
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
} 