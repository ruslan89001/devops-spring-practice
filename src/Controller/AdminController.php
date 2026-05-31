<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\AdminService;
use App\Service\UserService;
use App\Service\SpaceService;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private AdminService $adminService;
    private UserService $userService;
    private SpaceService $spaceService;
    private ReviewService $reviewService;

    public function __construct(
        AdminService $adminService,
        UserService $userService,
        SpaceService $spaceService,
        ReviewService $reviewService,
        EntityManagerInterface $entityManager
    ) {
        $this->adminService = $adminService;
        $this->userService = $userService;
        $this->spaceService = $spaceService;
        $this->reviewService = $reviewService;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $this->adminService->getDashboardStats();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->userService->getAllUsers();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/create', name: 'app_admin_user_create', methods: ['POST'])]
    public function createUser(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $role = $request->request->get('role', 'ROLE_USER');

            $roles = $role === 'ROLE_ADMIN' ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER'];

            $this->userService->createUser($name, $email, $password, $roles);
            $this->addFlash('success', 'Пользователь создан');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit', methods: ['POST'])]
    public function editUser(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $user = $this->userService->getUserById($id);
            if (!$user) {
                throw new \RuntimeException('Пользователь не найден');
            }

            $data = [
                'name' => $request->request->get('name'),
                'email' => $request->request->get('email'),
            ];

            $password = $request->request->get('password');
            if (!empty($password)) {
                $data['password'] = $password;
            }

            $role = $request->request->get('role');
            $roles = $role === 'ROLE_ADMIN' ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER'];
            $user->setRoles($roles);

            $this->userService->updateUser($user, $data);
            $this->addFlash('success', 'Пользователь обновлён');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->userService->deleteUser($id, $currentUser);
            $this->addFlash('success', 'Пользователь удалён');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/spaces', name: 'app_admin_spaces')]
    public function spaces(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $spaces = $this->spaceService->getAllSpaces();

        return $this->render('admin/spaces.html.twig', [
            'spaces' => $spaces
        ]);
    }

    #[Route('/spaces/create', name: 'app_admin_space_create', methods: ['POST'])]
    public function createSpace(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $data = [
                'name' => $request->request->get('name'),
                'address' => $request->request->get('address'),
                'description' => $request->request->get('description'),
                'price' => $request->request->get('price'),
                'status' => $request->request->get('status', 'active'),
            ];

            $photoFile = $request->files->get('photo');

            $this->spaceService->createSpace($data, $photoFile);
            $this->addFlash('success', 'Пространство создано');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_spaces');
    }

    #[Route('/spaces/{id}/edit', name: 'app_admin_space_edit', methods: ['POST'])]
    public function editSpace(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $space = $this->spaceService->getSpaceById($id);
            if (!$space) {
                throw new \RuntimeException('Пространство не найдено');
            }

            $data = [
                'name' => $request->request->get('name'),
                'address' => $request->request->get('address'),
                'description' => $request->request->get('description'),
                'price' => $request->request->get('price'),
                'status' => $request->request->get('status'),
            ];

            $photoFile = $request->files->get('photo');

            $this->spaceService->updateSpace($space, $data, $photoFile);
            $this->addFlash('success', 'Пространство обновлено');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_spaces');
    }

    #[Route('/spaces/{id}/delete', name: 'app_admin_space_delete', methods: ['POST'])]
    public function deleteSpace(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $this->spaceService->deleteSpace($id);
            $this->addFlash('success', 'Пространство удалено');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_spaces');
    }

    #[Route('/reviews', name: 'app_admin_reviews')]
    public function reviews(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $reviews = $this->reviewService->getAllWithDetails();

        return $this->render('admin/reviews.html.twig', [
            'reviews' => $reviews
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'app_admin_review_delete', methods: ['POST'])]
    public function deleteReview(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->reviewService->deleteReview($currentUser, $id, true);
            $this->addFlash('success', 'Отзыв удалён');

        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_reviews');
    }

    #[Route('/bookings', name: 'app_admin_bookings')]
    public function bookings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $bookings = $this->entityManager->getRepository(Booking::class)->findAll();

        return $this->render('admin/bookings.html.twig', [
            'bookings' => $bookings
        ]);
    }

    #[Route('/bookings/{id}/cancel', name: 'app_admin_booking_cancel', methods: ['POST'])]
    public function cancelBooking(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            $this->addFlash('danger', 'Бронирование не найдено');
            return $this->redirectToRoute('app_admin_bookings');
        }

        $booking->setStatus('cancelled');
        $this->entityManager->flush();

        $this->addFlash('success', 'Бронирование отменено');
        return $this->redirectToRoute('app_admin_bookings');
    }

    #[Route('/bookings/{id}/delete', name: 'app_admin_booking_delete', methods: ['POST'])]
    public function deleteBooking(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            $this->addFlash('danger', 'Бронирование не найдено');
            return $this->redirectToRoute('app_admin_bookings');
        }

        $this->entityManager->remove($booking);
        $this->entityManager->flush();

        $this->addFlash('success', 'Бронирование удалено');
        return $this->redirectToRoute('app_admin_bookings');
    }
}
