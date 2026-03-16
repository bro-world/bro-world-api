<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Sprint;

use App\Crm\Application\Service\CrmApplicationScopeResolver;
use App\Crm\Infrastructure\Repository\SprintRepository;
use App\Crm\Infrastructure\Repository\TaskRepository;
use App\Crm\Transport\Request\CrmApiErrorResponseFactory;
use App\Role\Domain\Enum\Role;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_MANAGER->value)]
final readonly class DetachTaskFromSprintController
{
    public function __construct(
        private CrmApplicationScopeResolver $scopeResolver,
        private SprintRepository $sprintRepository,
        private TaskRepository $taskRepository,
        private CrmApiErrorResponseFactory $errorResponseFactory,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/v1/crm/applications/{applicationSlug}/sprints/{sprintId}/tasks/{taskId}', methods: [Request::METHOD_DELETE])]
    public function __invoke(string $applicationSlug, string $sprintId, string $taskId): JsonResponse
    {
        $crm = $this->scopeResolver->resolveOrFail($applicationSlug);
        $sprint = $this->sprintRepository->findOneScopedById($sprintId, $crm->getId());
        if ($sprint === null) {
            return $this->errorResponseFactory->notFoundReference('sprintId');
        }

        $task = $this->taskRepository->findOneScopedById($taskId, $crm->getId());
        if ($task === null) {
            return $this->errorResponseFactory->notFoundReference('taskId');
        }

        if ($task->getSprint()?->getId() === $sprint->getId()) {
            $task->setSprint(null);
            $this->taskRepository->save($task);
        }

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
