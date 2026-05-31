<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            try {
                $data = [
                    'name' => $request->request->get('name'),
                    'email' => $request->request->get('email'),
                ];

                $newPassword = $request->request->get('password');
                if (!empty($newPassword)) {
                    $data['password'] = $newPassword;
                }

                $this->userService->updateUser($user, $data);

                $this->addFlash('success', 'Профиль успешно обновлён');
                return $this->redirectToRoute('app_profile');

            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user
        ]);
    }
}
