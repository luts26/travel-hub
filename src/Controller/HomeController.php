<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Trip;
use App\Form\ReviewType;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function show(Request $request, Trip $trip, EntityManagerInterface $entityManager): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->setTrip($trip);

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
        }

        return $this->render('/home/show.html.twig', [
           'trip' => $trip,
           'reviews' => $trip->getReviews(),
            'review_form' => $form->createView(),
        ]);
    }
}
