<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    #[Route('/bookings', name: 'app_bookings', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $bookings = $this->bookingService->getUserBookings($user);

        return $this->render('booking/index.html.twig', [
            'bookings' => $bookings
        ]);
    }

    #[Route('/bookings/create', name: 'app_booking_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $spaceId = (int) $request->request->get('space_id');
            $date = $request->request->get('booking_date');

            $booking = $this->bookingService->createBooking($user, $spaceId, $date);

            return $this->json([
                'success' => true,
                'message' => 'Бронирование успешно создано!',
                'booking_id' => $booking->getId()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/bookings/{id}/cancel', name: 'app_booking_cancel', methods: ['POST'])]
    public function cancel(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $this->bookingService->cancelBooking($user, $id);

            return $this->json([
                'success' => true,
                'message' => 'Бронирование отменено'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
