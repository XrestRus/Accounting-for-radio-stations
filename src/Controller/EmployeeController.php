<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для управления сотрудниками
 */
class EmployeeController extends AbstractController
{
    private EmployeeRepository $employeeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EmployeeRepository $employeeRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->employeeRepository = $employeeRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Отображает список сотрудников с возможностью поиска и фильтрации
     */
    #[Route('/employees', name: 'app_employee')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $department = $request->query->get('department', '');
        
        // Получаем активных сотрудников из репозитория
        $employees = $this->employeeRepository->findActive();
        
        // Фильтрация сотрудников при необходимости
        if (!empty($search)) {
            $employees = $this->employeeRepository->findByName($search);
        }
        
        // Фильтрация по подразделению
        if (!empty($department)) {
            // Можно добавить фильтрацию по подразделению в EmployeeRepository, но пока оставим фильтрацию на стороне PHP
            $employees = array_filter($employees, function($employee) use ($department) {
                return $employee->getDepartment() === $department;
            });
        }
        
        // Получаем список всех подразделений для выпадающего списка
        $departments = $this->employeeRepository->findAllDepartments();
        
        return $this->render('employee/index.html.twig', [
            'employees' => $employees,
            'search' => $search,
            'department' => $department,
            'departments' => $departments
        ]);
    }
    
    /**
     * Создание или редактирование сотрудника
     */
    #[Route('/employees/edit/{id}', name: 'app_employee_edit')]
    public function edit(Request $request, int $id = null): Response
    {
        if ($id) {
            $employee = $this->employeeRepository->find($id);
            if (!$employee) {
                $this->addFlash('error', 'Сотрудник не найден.');
                return $this->redirectToRoute('app_employee');
            }
            $pageTitle = 'Редактирование сотрудника';
            $edit_mode = true;
        } else {
            $employee = new Employee();
            $pageTitle = 'Добавление сотрудника';
            $edit_mode = false;
        }
        
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($employee);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Сотрудник успешно сохранен.');
            return $this->redirectToRoute('app_employee');
        }
        
        return $this->render('employee/edit.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
            'edit_mode' => $edit_mode,
        ]);
    }
    
    /**
     * Удаление сотрудника (мягкое удаление - устанавливаем deletedAt)
     */
    #[Route('/employees/delete/{id}', name: 'app_employee_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $employee = $this->employeeRepository->find($id);
        if (!$employee) {
            $this->addFlash('error', 'Сотрудник не найден.');
            return $this->redirectToRoute('app_employee');
        }
        
        if ($this->isCsrfTokenValid('delete' . $employee->getId(), $request->request->get('_token'))) {
            // Мягкое удаление - устанавливаем текущую дату в поле deletedAt
            $employee->setDeletedAt(new \DateTime());
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Сотрудник успешно удален.');
        } else {
            $this->addFlash('error', 'Недействительный токен безопасности.');
        }
        
        return $this->redirectToRoute('app_employee');
    }
}
