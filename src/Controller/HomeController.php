<?php

namespace App\Controller;

use App\Service\SpaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private SpaceService $spaceService;

    public function __construct(SpaceService $spaceService)
    {
        $this->spaceService = $spaceService;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $spaces = $this->spaceService->getActiveSpacesLimit(6);

        return $this->render('home/index.html.twig', [
            'spaces' => $spaces
        ]);
    }
}
