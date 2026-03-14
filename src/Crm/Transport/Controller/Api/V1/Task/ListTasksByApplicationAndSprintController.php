<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Task;

use App\Crm\Application\Service\CrmApiNormalizer;
use App\Crm\Application\Service\CrmApplicationScopeResolver;
use App\Crm\Domain\Entity\Sprint;
use App\Crm\Infrastructure\Repository\TaskRepository;
use App\Role\Domain\Enum\Role;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class ListTasksByApplicationAndSprintController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private CrmApplicationScopeResolver $scopeResolver,
        private CrmApiNormalizer $crmApiNormalizer,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/sprints/{sprint}/tasks', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Sprint $sprint): JsonResponse
    {
        $crm = $this->scopeResolver->resolveOrFail($applicationSlug);
        $tasks = $this->taskRepository->findScopedBySprint($crm->getId(), $sprint->getId());

        return new JsonResponse([
            'items' => array_map(fn ($task): array => $this->crmApiNormalizer->normalizeTask($task), $tasks),
        ]);
    }
}
