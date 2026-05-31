<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            try {
                $name = $request->request->get('name');
                $email = $request->request->get('email');
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirm_password');

                if ($password !== $confirmPassword) {
                    $this->addFlash('danger', 'Пароли не совпадают');
                    return $this->redirectToRoute('app_register');
                }

                $this->userService->registerUser($name, $email, $password);

                $this->addFlash('success', 'Регистрация успешна! Теперь вы можете войти.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig');
    }
}
