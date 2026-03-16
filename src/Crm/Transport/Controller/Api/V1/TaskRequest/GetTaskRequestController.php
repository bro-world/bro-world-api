<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\TaskRequest;

use App\Crm\Application\Service\TaskRequestReadService;
use App\Crm\Domain\Entity\TaskRequest;
use App\Role\Domain\Enum\Role;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class GetTaskRequestController
{
    public function __construct(
        private TaskRequestReadService $taskRequestReadService,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/task-requests/{taskRequest}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, TaskRequest $taskRequest): JsonResponse
    {
        $payload = $this->taskRequestReadService->getDetail($applicationSlug, $taskRequest->getId());
        if ($payload === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Task request not found for this CRM scope.');
        }

        return new JsonResponse($payload);
    }
}
