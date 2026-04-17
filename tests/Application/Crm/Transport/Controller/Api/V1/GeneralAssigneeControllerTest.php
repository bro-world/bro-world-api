<?php

declare(strict_types=1);

namespace App\Tests\Application\Crm\Transport\Controller\Api\V1;

use App\Crm\Infrastructure\Repository\CrmRepository;
use App\General\Domain\Utils\JSON;
use App\Tests\TestCase\WebTestCase;
use App\User\Infrastructure\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

final class GeneralAssigneeControllerTest extends WebTestCase
{
    private const string PRIMARY_APPLICATION_SLUG = 'crm-sales-hub';
    private const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    public function testGeneralAssigneeEndpointsSuccess(): void
    {
        $companyId = $this->createGeneralCompany();
        $projectId = $this->createGeneralProject($companyId);
        $taskId = $this->createGeneralTask($projectId);
        $sprintId = $this->createGeneralSprint($projectId);
        $assigneeId = $this->getAssigneeUserId();

        $managerClient = $this->getTestClient('john-crm_manager', 'password-crm_manager');

        $managerClient->request('PUT', sprintf('%s/v1/crm/general/projects/%s/assignees/%s', self::API_URL_PREFIX, $projectId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());

        $managerClient->request('DELETE', sprintf('%s/v1/crm/general/projects/%s/assignees/%s', self::API_URL_PREFIX, $projectId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());

        $managerClient->request('PUT', sprintf('%s/v1/crm/general/tasks/%s/assignees/%s', self::API_URL_PREFIX, $taskId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());

        $managerClient->request('DELETE', sprintf('%s/v1/crm/general/tasks/%s/assignees/%s', self::API_URL_PREFIX, $taskId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());

        $managerClient->request('PUT', sprintf('%s/v1/crm/general/sprints/%s/assignees/%s', self::API_URL_PREFIX, $sprintId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());

        $managerClient->request('DELETE', sprintf('%s/v1/crm/general/sprints/%s/assignees/%s', self::API_URL_PREFIX, $sprintId, $assigneeId));
        self::assertSame(Response::HTTP_NO_CONTENT, $managerClient->getResponse()->getStatusCode());
    }

    public function testGeneralAssigneeEndpointsForbiddenForViewer(): void
    {
        $companyId = $this->createGeneralCompany();
        $projectId = $this->createGeneralProject($companyId);
        $taskId = $this->createGeneralTask($projectId);
        $sprintId = $this->createGeneralSprint($projectId);
        $assigneeId = $this->getAssigneeUserId();

        $viewerClient = $this->getTestClient('john-crm_viewer', 'password-crm_viewer');

        foreach ([
            sprintf('%s/v1/crm/general/projects/%s/assignees/%s', self::API_URL_PREFIX, $projectId, $assigneeId),
            sprintf('%s/v1/crm/general/tasks/%s/assignees/%s', self::API_URL_PREFIX, $taskId, $assigneeId),
            sprintf('%s/v1/crm/general/sprints/%s/assignees/%s', self::API_URL_PREFIX, $sprintId, $assigneeId),
        ] as $path) {
            $viewerClient->request('PUT', $path);
            self::assertSame(Response::HTTP_FORBIDDEN, $viewerClient->getResponse()->getStatusCode());

            $viewerClient->request('DELETE', $path);
            self::assertSame(Response::HTTP_FORBIDDEN, $viewerClient->getResponse()->getStatusCode());
        }
    }

    public function testGeneralAssigneeEndpointsNotFound(): void
    {
        $companyId = $this->createGeneralCompany();
        $projectId = $this->createGeneralProject($companyId);
        $taskId = $this->createGeneralTask($projectId);
        $sprintId = $this->createGeneralSprint($projectId);

        $managerClient = $this->getTestClient('john-crm_manager', 'password-crm_manager');

        foreach ([
            sprintf('%s/v1/crm/general/projects/%s/assignees/%s', self::API_URL_PREFIX, $projectId, self::UNKNOWN_UUID),
            sprintf('%s/v1/crm/general/tasks/%s/assignees/%s', self::API_URL_PREFIX, $taskId, self::UNKNOWN_UUID),
            sprintf('%s/v1/crm/general/sprints/%s/assignees/%s', self::API_URL_PREFIX, $sprintId, self::UNKNOWN_UUID),
        ] as $path) {
            $managerClient->request('PUT', $path);
            self::assertSame(Response::HTTP_NOT_FOUND, $managerClient->getResponse()->getStatusCode());

            $managerClient->request('DELETE', $path);
            self::assertSame(Response::HTTP_NOT_FOUND, $managerClient->getResponse()->getStatusCode());
        }
    }

    private function createGeneralCompany(): string
    {
        $client = $this->getTestClient('john-crm_manager', 'password-crm_manager');
        $client->request(
            'POST',
            sprintf('%s/v1/crm/general/companies', self::API_URL_PREFIX),
            content: JSON::encode([
                'crmId' => $this->getPrimaryCrmId(),
                'name' => 'General Assignee Company ' . uniqid('', true),
            ])
        );
        self::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $payload = $this->decodeJsonResponse($client->getResponse()->getContent());

        return (string) $payload['id'];
    }

    private function createGeneralProject(string $companyId): string
    {
        $client = $this->getTestClient('john-crm_manager', 'password-crm_manager');
        $client->request(
            'POST',
            sprintf('%s/v1/crm/general/projects', self::API_URL_PREFIX),
            content: JSON::encode([
                'companyId' => $companyId,
                'name' => 'General Assignee Project ' . uniqid('', true),
            ])
        );
        self::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $payload = $this->decodeJsonResponse($client->getResponse()->getContent());

        return (string) $payload['id'];
    }

    private function createGeneralTask(string $projectId): string
    {
        $client = $this->getTestClient('john-crm_manager', 'password-crm_manager');
        $client->request(
            'POST',
            sprintf('%s/v1/crm/general/tasks', self::API_URL_PREFIX),
            content: JSON::encode([
                'projectId' => $projectId,
                'title' => 'General Assignee Task ' . uniqid('', true),
            ])
        );
        self::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $payload = $this->decodeJsonResponse($client->getResponse()->getContent());

        return (string) $payload['id'];
    }

    private function createGeneralSprint(string $projectId): string
    {
        $client = $this->getTestClient('john-crm_manager', 'password-crm_manager');
        $client->request(
            'POST',
            sprintf('%s/v1/crm/general/sprints', self::API_URL_PREFIX),
            content: JSON::encode([
                'projectId' => $projectId,
                'name' => 'General Assignee Sprint ' . uniqid('', true),
            ])
        );
        self::assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $payload = $this->decodeJsonResponse($client->getResponse()->getContent());

        return (string) $payload['id'];
    }

    private function getAssigneeUserId(): string
    {
        static::bootKernel();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $assignee = $userRepository->findOneBy([
            'username' => 'alice',
        ]);
        self::assertNotNull($assignee);

        return $assignee->getId();
    }

    private function getPrimaryCrmId(): string
    {
        static::bootKernel();
        $crmRepository = static::getContainer()->get(CrmRepository::class);
        $crm = $crmRepository->findOneByApplicationSlug(self::PRIMARY_APPLICATION_SLUG);
        self::assertNotNull($crm);

        return $crm->getId();
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJsonResponse(string|false $content): array
    {
        self::assertNotFalse($content);
        $decoded = JSON::decode($content, true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
