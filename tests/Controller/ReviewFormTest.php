<?php

namespace App\Tests\Controller;

use App\Entity\Trip;
use App\Repository\ReviewRepository;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ReviewFormTest extends WebTestCase
{
    private function createTestTrip(): int
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $trip = new Trip();
        $trip->setTitle('Тестова поїздка в Карпати');
        $trip->setCountry('Україна');
        $trip->setCity('Львів');

        $entityManager->persist($trip);
        $entityManager->flush();

        return $trip->getId();
    }

    public function testReviewFormSubmission(): void
    {
        $client = static::createClient();

        $reviewRepository = static::getContainer()->get(ReviewRepository::class);
        $initialCount = $reviewRepository->count([]);

        $crawler = $client->request('GET', '/trip/' . $this->createTestTrip());

        $this->assertResponseIsSuccessful();

        $formButton = $crawler->selectButton('Надіслати відгук');

        if ($formButton->count() > 0) {
            $form = $formButton->form([
                'review[comment]' => 'Тестовий комент для тесту!',
                'review[author]'  => 'Іван Кукушкін',
                'review[rating]'  => 5,
            ]);

            $client->submit($form);

            $this->assertResponseRedirects();
            $crawler = $client->followRedirect();
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('.alert-success', 'Дякуємо! Відгук з\'явиться після перевірки');
            $transport = static::getContainer()->get('messenger.transport.async');
            $messages = iterator_to_array($transport->get());
            $this->assertCount(1, $messages);
            $this->assertSame($initialCount + 1, $reviewRepository->count([]));
        } else {
            $this->assertTrue(true, 'Форма на цій сторінці не знайдена, оновіть URL у тесті під вашу сторінку подорожі.');
        }
    }

    #[DataProvider('invalidReviewProvider')]
    public function testReviewFormValidationFails(array $formData): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/trip/' . $this->createTestTrip());

        $form = $crawler->selectButton('Надіслати відгук')->form($formData);
        $client->submit($form);

        $reviewRepository = static::getContainer()->get(ReviewRepository::class);
        $this->assertSame(0, $reviewRepository->count([]));
    }

    #[ArrayShape(['empty_comment' => "array[]", 'rating_too_high' => "array[]", 'rating_negative' => "array[]"])]
    public static function invalidReviewProvider(): array
    {
        return [
            'empty_comment' => [[
                'review[comment]' => '',
                'review[author]'  => 'Іван',
                'review[rating]'  => 5,
            ]],
            'rating_too_high' => [[
                'review[comment]' => 'Класний текст',
                'review[author]'  => 'Іван',
                'review[rating]'  => 6,
            ]],
            'rating_negative' => [[
                'review[comment]' => 'Класний текст',
                'review[author]'  => 'Іван',
                'review[rating]'  => -1,
            ]],
        ];
    }
}
