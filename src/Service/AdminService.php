<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\SpaceRepository;
use App\Repository\ReviewRepository;
use App\Repository\BookingRepository;

class AdminService
{
    private UserRepository $userRepository;
    private SpaceRepository $spaceRepository;
    private ReviewRepository $reviewRepository;
    private BookingRepository $bookingRepository;

    public function __construct(
        UserRepository $userRepository,
        SpaceRepository $spaceRepository,
        ReviewRepository $reviewRepository,
        BookingRepository $bookingRepository
    ) {
        $this->userRepository = $userRepository;
        $this->spaceRepository = $spaceRepository;
        $this->reviewRepository = $reviewRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function getDashboardStats(): array
    {
        return [
            'total_users' => $this->userRepository->countUsers(),
            'total_spaces' => $this->spaceRepository->countSpaces(),
            'active_spaces' => $this->spaceRepository->countActiveSpaces(),
            'total_reviews' => $this->reviewRepository->countReviews(),
            'total_bookings' => $this->bookingRepository->count([]),
            'active_bookings' => $this->bookingRepository->count(['status' => 'active']),
        ];
    }
}
