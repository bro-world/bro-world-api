<?php

declare(strict_types=1);

namespace App\Tests\Application\Page\Transport\Controller\Api\V1\Public;

use App\General\Domain\Utils\JSON;
use App\Tests\TestCase\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

final class PublicPageControllerTest extends WebTestCase
{
    #[TestDox('Public page endpoint returns expected JSON payload for existing language.')]
    #[DataProvider('providePublicPageRoutes')]
    public function testPublicPageEndpointReturns200(string $route, array $expectedSubset): void
    {
        $client = $this->getTestClient();
        $client->request('GET', self::API_URL_PREFIX . $route . '/fr');

        $response = $client->getResponse();
        $content = $response->getContent();

        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), "Response:\n" . $response);

        $payload = JSON::decode($content, true);
        self::assertIsArray($payload);

        foreach ($expectedSubset as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertSame($value, $payload[$key]);
        }
    }

    #[TestDox('Public page endpoint returns 404 for missing language.')]
    #[DataProvider('providePublicPageRoutes')]
    public function testPublicPageEndpointReturns404WhenLanguageIsMissing(string $route): void
    {
        $client = $this->getTestClient();
        $client->request('GET', self::API_URL_PREFIX . $route . '/xx');

        $response = $client->getResponse();

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode(), "Response:\n" . $response);
    }

    /**
     * @return iterable<string, array{0: string, 1: array<string, mixed>}>
     */
    public static function providePublicPageRoutes(): iterable
    {
        yield 'home' => ['/v1/page/public/home', ['hero' => ['title' => 'Bienvenue sur Bro World', 'subtitle' => 'Une plateforme unifiée pour vos applications.', 'cta' => ['label' => 'Commencer', 'url' => '/signup']]]];
        yield 'about' => ['/v1/page/public/about', ['title' => 'À propos', 'mission' => 'Aider les équipes à livrer plus vite avec une expérience cohérente.']];
        yield 'contact' => ['/v1/page/public/contact', ['title' => 'Contact', 'email' => 'contact@bro-world.dev']];
        yield 'faq' => ['/v1/page/public/faq', ['title' => 'FAQ']];
    }
}
