<?php

declare(strict_types=1);

namespace App\Tests\Application\Configuration\Transport\Controller\Api\V1\Configuration;

use App\General\Domain\Utils\JSON;
use App\Tests\TestCase\WebTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @package App\Tests
 */
class ConfigurationControllerTest extends WebTestCase
{
    protected static string $baseUrl = self::API_URL_PREFIX . '/v1/configuration';

    /**
     * @throws Throwable
     */
    #[TestDox('Test that `GET /api/v1/configuration` request returns `401` for non-logged user.')]
    public function testThatGetBaseRouteReturn401(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', static::$baseUrl);
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), "Response:\n" . $response);
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('dataProviderGetActions')]
    #[TestDox('Test that `$method $action` returns forbidden error for non-admin user.')]
    public function testThatGetActionsForbiddenForNonAdminUser(string $method, string $action): void
    {
        $client = $this->getTestClient('john-user', 'password-user');

        $client->request($method, $action);
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), "Response:\n" . $response);
    }

    /**
     * @throws Throwable
     */
    #[TestDox('Test that `GET /api/v1/configuration/count` for admin returns success response.')]
    public function testThatCountActionForAdminUserReturnsSuccessResponse(): void
    {
        $client = $this->getTestClient('john-admin', 'password-admin');

        $client->request(method: 'GET', uri: static::$baseUrl . '/count');
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), "Response:\n" . $response);
        $responseData = JSON::decode($content, true);
        self::assertArrayHasKey('count', $responseData);
        self::assertGreaterThan(0, $responseData['count']);
    }

    /**
     * @throws Throwable
     */
    #[TestDox('Test that `GET /api/v1/configuration` for admin returns success response.')]
    public function testThatFindActionForAdminUserReturnsSuccessResponse(): void
    {
        $client = $this->getTestClient('john-admin', 'password-admin');

        $client->request('GET', static::$baseUrl);
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), "Response:\n" . $response);

        $responseData = JSON::decode($content, true);
        self::assertIsArray($responseData);
        self::assertGreaterThanOrEqual(5, count($responseData));
        self::assertArrayHasKey('id', $responseData[0]);
        self::assertArrayHasKey('configurationKey', $responseData[0]);
        self::assertArrayHasKey('configurationValue', $responseData[0]);
        self::assertArrayHasKey('scope', $responseData[0]);
        self::assertArrayHasKey('private', $responseData[0]);
    }

    /**
     * @throws Throwable
     */
    #[TestDox('Test that `GET /api/v1/configuration/ids` for admin returns success response.')]
    public function testThatIdsActionForAdminUserReturnsSuccessResponse(): void
    {
        $client = $this->getTestClient('john-admin', 'password-admin');

        $client->request('GET', static::$baseUrl . '/ids');
        $response = $client->getResponse();
        $content = $response->getContent();
        self::assertNotFalse($content);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), "Response:\n" . $response);
        $responseData = JSON::decode($content, true);
        self::assertIsArray($responseData);
        self::assertGreaterThan(0, count($responseData));
        self::assertIsString($responseData[0]);
    }

    /**
     * @return Generator<array{0: string, 1: string}>
     */
    public static function dataProviderGetActions(): Generator
    {
        yield ['GET', static::$baseUrl . '/count'];
        yield ['GET', static::$baseUrl];
        yield ['GET', static::$baseUrl . '/ids'];
    }
}
