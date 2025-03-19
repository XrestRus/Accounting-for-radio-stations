<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    
    #[Route('/users/edit/{id}', name: 'app_user_edit')]
    public function edit(int $id = null): Response
    {
        return $this->render('user/edit.html.twig', [
            'user_id' => $id,
            'edit_mode' => $id ? true : false,
        ]);
    }
}
