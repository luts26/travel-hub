<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Trip;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, TripRepository $tripRepository, ReviewRepository $reviewRepository): Response
    {
        $searchQuery = $request->query->get('q');

        $trips = $tripRepository->findByFilters($searchQuery);

        $tripStats = $reviewRepository->getStatsForTrips($trips);

        return $this->render('home/index.html.twig', [
            'trips' => $trips,
            'trip_stats' => $tripStats,
            'current_search' => $searchQuery,
        ]);
    }

    #[Route('/trip/{id<\d+>}', name: 'trip_show')]
    public function show(Request $request, Trip $trip, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->setTrip($trip);

            $entityManager->persist($review);
            $entityManager->flush();

            $reviewRepository->clearTripCache($trip);

            $this->addFlash('success', 'Ваш відгук успішно додано!');

            return $this->redirectToRoute('trip_show', ['id' => $trip->getId()]);
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $reviews = $reviewRepository->getPaginationPaginated($trip, $offset, $limit);
        $tripStats = $reviewRepository->getTripStats($trip);
        $maxPages = ceil($tripStats['reviewCount'] / $limit);

        return $this->render('/home/show.html.twig', [
           'trip' => $trip,
           'trip_rating' => $tripStats['avgRating'],
           'reviews' => $reviews,
            'total_reviews' => $tripStats['reviewCount'],
            'current_page' => $page,
            'max_pages' => $maxPages,
            'review_form' => $form->createView(),
        ]);
    }
}
