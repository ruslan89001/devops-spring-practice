<?php

namespace App\Controller;

use App\Service\SpaceService;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaceController extends AbstractController
{
    private SpaceService $spaceService;
    private ReviewService $reviewService;

    public function __construct(SpaceService $spaceService, ReviewService $reviewService)
    {
        $this->spaceService = $spaceService;
        $this->reviewService = $reviewService;
    }

    #[Route('/spaces', name: 'app_spaces')]
    public function index(): Response
    {
        $spaces = $this->spaceService->getActiveSpaces();

        return $this->render('space/index.html.twig', [
            'spaces' => $spaces
        ]);
    }

    #[Route('/spaces/{id}', name: 'app_space_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $space = $this->spaceService->getSpaceById($id);

        if (!$space || $space->getStatus() !== 'active') {
            throw $this->createNotFoundException('Пространство не найдено');
        }

        $reviews = $this->reviewService->getReviewsForSpace($id);
        $averageRating = $this->reviewService->getAverageRating($id);

        return $this->render('space/show.html.twig', [
            'space' => $space,
            'reviews' => $reviews,
            'average_rating' => $averageRating
        ]);
    }
}
