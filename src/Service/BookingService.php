<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\User;
use App\Repository\BookingRepository;
use App\Repository\SpaceRepository;
use Psr\Log\LoggerInterface;

class BookingService
{
    private BookingRepository $bookingRepository;
    private SpaceRepository $spaceRepository;
    private LoggerInterface $logger;

    public function __construct(
        BookingRepository $bookingRepository,
        SpaceRepository $spaceRepository,
        LoggerInterface $logger
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->spaceRepository = $spaceRepository;
        $this->logger = $logger;
    }

    public function createBooking(User $user, int $spaceId, string $dateString): Booking
    {
        $this->logger->info('Booking attempt', [
            'user_id' => $user->getId(),
            'space_id' => $spaceId,
            'date' => $dateString
        ]);

        $date = $this->validateDate($dateString);

        $space = $this->spaceRepository->find($spaceId);
        if (!$space) {
            $this->logger->warning('Booking failed: space not found', ['space_id' => $spaceId]);
            throw new \RuntimeException('Пространство не найдено');
        }

        if ($space->getStatus() !== 'active') {
            throw new \RuntimeException('Пространство недоступно для бронирования');
        }

        try {
            $booking = $this->bookingRepository->createBookingTransactional($user, $space, $date);

            $this->logger->info('Booking created successfully', [
                'booking_id' => $booking->getId(),
                'user_id' => $user->getId(),
                'space_id' => $spaceId
            ]);

            return $booking;
        } catch (\RuntimeException $e) {
            // Это наше исключение из репозитория
            $this->logger->warning('Booking failed: space already booked', [
                'user_id' => $user->getId(),
                'space_id' => $spaceId
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Booking failed', [
                'user_id' => $user->getId(),
                'space_id' => $spaceId,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException('Не удалось создать бронирование');
        }
    }

    public function cancelBooking(User $user, int $bookingId): void
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            throw new \RuntimeException('Бронирование не найдено');
        }

        if ($booking->getUser()->getId() !== $user->getId()) {
            $this->logger->warning('Unauthorized booking cancellation attempt', [
                'booking_id' => $bookingId,
                'user_id' => $user->getId(),
                'owner_id' => $booking->getUser()->getId()
            ]);
            throw new \RuntimeException('Вы можете отменить только свои бронирования');
        }

        if ($booking->getStatus() !== 'active') {
            throw new \RuntimeException('Бронирование уже отменено');
        }

        $this->bookingRepository->cancelBooking($booking);

        $this->logger->info('Booking cancelled', [
            'booking_id' => $bookingId,
            'user_id' => $user->getId()
        ]);
    }

    public function getUserBookings(User $user): array
    {
        return $this->bookingRepository->findByUser($user);
    }

    private function validateDate(string $dateString): \DateTime
    {
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);

        if (!$date) {
            throw new \InvalidArgumentException('Некорректный формат даты. Используйте YYYY-MM-DD');
        }

        $today = new \DateTime('today');
        if ($date < $today) {
            throw new \InvalidArgumentException('Дата бронирования не может быть в прошлом');
        }

        return $date;
    }
}
