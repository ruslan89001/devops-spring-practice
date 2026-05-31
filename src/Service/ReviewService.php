<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Repository\SpaceRepository;
use Psr\Log\LoggerInterface;

class ReviewService
{
    private ReviewRepository $reviewRepository;
    private SpaceRepository $spaceRepository;
    private LoggerInterface $logger;

    public function __construct(
        ReviewRepository $reviewRepository,
        SpaceRepository $spaceRepository,
        LoggerInterface $logger
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->spaceRepository = $spaceRepository;
        $this->logger = $logger;
    }

    public function createReview(User $user, int $spaceId, int $rating, ?string $comment = null): Review
    {
        $space = $this->spaceRepository->find($spaceId);
        if (!$space) {
            throw new \RuntimeException('Пространство не найдено');
        }

        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Рейтинг должен быть от 1 до 5');
        }

        if ($comment !== null) {
            $commentLength = mb_strlen(trim($comment));
            if ($commentLength > 0 && ($commentLength < 10 || $commentLength > 1000)) {
                throw new \InvalidArgumentException('Комментарий должен быть от 10 до 1000 символов');
            }
        }

        $existingReview = $this->reviewRepository->findByUserAndSpace($user, $space);
        if ($existingReview) {
            throw new \RuntimeException('Вы уже оставили отзыв на это пространство');
        }

        $review = new Review();
        $review->setUser($user);
        $review->setSpace($space);
        $review->setRating($rating);
        $review->setComment($comment);

        $this->reviewRepository->save($review);

        $this->logger->info('Review created', [
            'review_id' => $review->getId(),
            'user_id' => $user->getId(),
            'space_id' => $spaceId,
            'rating' => $rating
        ]);

        return $review;
    }

    public function deleteReview(User $user, int $reviewId, bool $isAdmin = false): void
    {
        $review = $this->reviewRepository->find($reviewId);

        if (!$review) {
            throw new \RuntimeException('Отзыв не найден');
        }

        if (!$isAdmin && $review->getUser()->getId() !== $user->getId()) {
            $this->logger->warning('Unauthorized review deletion attempt', [
                'review_id' => $reviewId,
                'user_id' => $user->getId()
            ]);
            throw new \RuntimeException('Вы можете удалить только свой отзыв');
        }

        $this->reviewRepository->remove($review);

        $this->logger->info('Review deleted', [
            'review_id' => $reviewId,
            'deleted_by' => $user->getId(),
            'is_admin' => $isAdmin
        ]);
    }

    public function getReviewsForSpace(int $spaceId): array
    {
        $space = $this->spaceRepository->find($spaceId);
        if (!$space) {
            return [];
        }

        return $this->reviewRepository->findBySpace($space);
    }

    public function getAverageRating(int $spaceId): ?float
    {
        $space = $this->spaceRepository->find($spaceId);
        if (!$space) {
            return null;
        }

        return $this->reviewRepository->getAverageRatingForSpace($space);
    }

    public function getAllWithDetails(): array
    {
        return $this->reviewRepository->findAllWithDetails();
    }
}
