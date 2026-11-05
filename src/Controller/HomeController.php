<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TripRepository $tripRepository): Response
    {
        $trips = $tripRepository->findAll();
        return $this->render('home/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/trip/{id<\d+>}', name: 'trip_show')]
    public function show(Trip $trip): Response
    {
        return $this->render('/home/show.html.twig', [
           'trip' => $trip,
           'reviews' => $trip->getReviews(),
        ]);
    }
}
