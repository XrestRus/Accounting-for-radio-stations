<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Аутентификатор для обработки формы логина
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    /**
     * Путь к форме логина
     */
    public const LOGIN_ROUTE = 'app_login';

    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор аутентификатора
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator, 
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Аутентификация пользователя на основе данных запроса
     */
    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');
        $rememberMe = $request->request->getBoolean('_remember_me', false);

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username, function (string $username) {
                $user = $this->userRepository->findOneBy(['username' => $username]);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Неверное имя пользователя или пароль.');
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    /**
     * Обработка успешной аутентификации
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Обновляем время последнего входа пользователя
        $user = $token->getUser();
        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Перенаправляем на главную страницу после успешного входа
        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    /**
     * Получение URL формы входа в систему
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * Обработка неудачной аутентификации
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }
} 