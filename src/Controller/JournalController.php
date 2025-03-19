<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JournalController extends AbstractController
{
    #[Route('/journal', name: 'app_journal')]
    public function index(): Response
    {
        return $this->render('journal/index.html.twig', [
            'controller_name' => 'JournalController',
        ]);
    }
}
