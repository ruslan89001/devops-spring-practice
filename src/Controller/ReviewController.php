<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    #[Route('/reviews', name: 'app_review_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $spaceId = (int) $request->request->get('space_id');
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            $review = $this->reviewService->createReview($user, $spaceId, $rating, $comment);

            return $this->json([
                'success' => true,
                'message' => 'Отзыв успешно добавлен!',
                'review' => [
                    'id' => $review->getId(),
                    'user_name' => $review->getUser()->getName(),
                    'rating' => $review->getRating(),
                    'comment' => $review->getComment(),
                    'created_at' => $review->getCreatedAt()->format('d.m.Y')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/reviews/{id}', name: 'app_review_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $this->reviewService->deleteReview($user, $id);

            return $this->json([
                'success' => true,
                'message' => 'Отзыв удалён'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
