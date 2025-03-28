<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для работы со страницей справки и инструкциями
 */
class HelpController extends AbstractController
{
    /**
     * Отображает основную страницу справки
     */
    #[Route('/help', name: 'app_help')]
    public function index(): Response
    {
        return $this->render('help/index.html.twig', [
            'controller_name' => 'HelpController',
        ]);
    }
    
    /**
     * Отображает печатную версию справки
     */
    #[Route('/help/print', name: 'app_help_print')]
    public function printHelp(): Response
    {
        return $this->render('help/print.html.twig', [
            'controller_name' => 'HelpController',
        ]);
    }
}
