<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Контроллер для обработки авторизации, входа и выхода из системы
 */
class SecurityController extends AbstractController
{
    /**
     * Отображает страницу авторизации
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Получаем ошибку входа, если есть
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Получаем последнее имя пользователя, введенное пользователем
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    
    /**
     * Обрабатывает выход из системы
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method will be intercepted by the logout key on your firewall
        throw new \LogicException('This method should never be reached!');
    }
    
    /**
     * Отображает страницу с ошибкой доступа
     */
    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('security/access_denied.html.twig');
    }
}
