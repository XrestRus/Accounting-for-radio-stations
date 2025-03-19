<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    #[Route('/employees', name: 'app_employee')]
    public function index(): Response
    {
        return $this->render('employee/index.html.twig', [
            'controller_name' => 'EmployeeController',
        ]);
    }
    
    #[Route('/employees/edit/{id}', name: 'app_employee_edit')]
    public function edit(int $id = null): Response
    {
        return $this->render('employee/edit.html.twig', [
            'employee_id' => $id,
            'edit_mode' => $id ? true : false,
        ]);
    }
}
