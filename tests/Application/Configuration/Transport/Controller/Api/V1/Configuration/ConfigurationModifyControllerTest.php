<?php

declare(strict_types=1);

namespace App\Tests\Application\Configuration\Transport\Controller\Api\V1\Configuration;

use App\Configuration\Infrastructure\DataFixtures\ORM\LoadConfigurationData;
use App\General\Domain\Utils\JSON;
use App\Tests\TestCase\WebTestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @package App\Tests
 */
class ConfigurationModifyControllerTest extends WebTestCase
{
    private string $baseUrl = self::API_URL_PREFIX . '/v1/configuration';

    /**
     * @throws Throwable
     */
    #[TestDox('Test that view/update/patch/delete configuration actions work as expected.')]
    public function testThatCrudActionsWorkAsExpected(): void
    {
        $configurationId = LoadConfigurationData::getUuidByKey('platform-secrets');

        $adminClient = $this->getTestClient('john-admin', 'password-admin');
        $adminClient->request('GET', $this->baseUrl . '/' . $configurationId);
        $viewResponse = $adminClient->getResponse();
        $viewContent = $viewResponse->getContent();
        self::assertNotFalse($viewContent);
        self::assertSame(Response::HTTP_OK, $viewResponse->getStatusCode(), "Response:\n" . $viewResponse);
        $viewData = JSON::decode($viewContent, true);
        self::assertSame('platform.secrets', $viewData['configurationKey']);
        self::assertIsArray($viewData['configurationValue']);
        self::assertArrayHasKey('apiSecret', $viewData['configurationValue']);

        $rootClient = $this->getTestClient('john-root', 'password-root');
        $updateData = [
            'configurationKey' => 'platform.secrets.updated',
            'configurationValue' => ['apiSecret' => 'updated-secret', 'rotation' => 90],
            'scope' => 'platform',
            'private' => true,
        ];
        $rootClient->request('PUT', $this->baseUrl . '/' . $configurationId, content: JSON::encode($updateData));
        $updateResponse = $rootClient->getResponse();
        $updateContent = $updateResponse->getContent();
        self::assertNotFalse($updateContent);
        self::assertSame(Response::HTTP_OK, $updateResponse->getStatusCode(), "Response:\n" . $updateResponse);
        $updated = JSON::decode($updateContent, true);
        self::assertSame('platform.secrets.updated', $updated['configurationKey']);
        self::assertSame('updated-secret', $updated['configurationValue']['apiSecret']);

        $patchData = ['configurationValue' => ['apiSecret' => 'patched-secret', 'rotation' => 15]];
        $rootClient->request('PATCH', $this->baseUrl . '/' . $configurationId, content: JSON::encode($patchData));
        $patchResponse = $rootClient->getResponse();
        $patchContent = $patchResponse->getContent();
        self::assertNotFalse($patchContent);
        self::assertSame(Response::HTTP_OK, $patchResponse->getStatusCode(), "Response:\n" . $patchResponse);
        $patched = JSON::decode($patchContent, true);
        self::assertSame('patched-secret', $patched['configurationValue']['apiSecret']);

        $rootClient->request('DELETE', $this->baseUrl . '/' . $configurationId);
        $deleteResponse = $rootClient->getResponse();
        $deleteContent = $deleteResponse->getContent();
        self::assertNotFalse($deleteContent);
        self::assertSame(Response::HTTP_NO_CONTENT, $deleteResponse->getStatusCode(), "Response:\n" . $deleteResponse);
    }
}
