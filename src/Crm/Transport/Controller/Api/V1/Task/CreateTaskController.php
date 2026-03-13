<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Task;

use App\Crm\Application\Service\CrmApplicationScopeResolver;
use App\Crm\Domain\Entity\Task;
use App\Crm\Domain\Enum\TaskPriority;
use App\Crm\Domain\Enum\TaskStatus;
use App\Crm\Infrastructure\Repository\ProjectRepository;
use App\Crm\Infrastructure\Repository\SprintRepository;
use App\General\Application\Message\EntityCreated;
use App\User\Domain\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
final readonly class CreateTaskController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private SprintRepository $sprintRepository,
        private CrmApplicationScopeResolver $scopeResolver,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/tasks', methods: [Request::METHOD_POST])]
    #[OA\Parameter(name: 'applicationSlug', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Post(summary: 'POST /v1/crm/applications/{applicationSlug}/tasks')]

    #[OA\RequestBody(required: false, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'assigneeIds', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), nullable: true),
        ]
    ))]
    public function __invoke(string $applicationSlug, Request $request): JsonResponse
    {
        $request->attributes->set('applicationSlug', $applicationSlug);
        $crm = $this->scopeResolver->resolveOrFail($applicationSlug);
        $payload = (array)json_decode((string)$request->getContent(), true);
        $task = new Task();
        $task->setTitle((string)($payload['title'] ?? ''))
            ->setDescription(isset($payload['description']) ? (string)$payload['description'] : null)
            ->setStatus(TaskStatus::tryFrom((string)($payload['status'] ?? '')) ?? TaskStatus::TODO)
            ->setPriority(TaskPriority::tryFrom((string)($payload['priority'] ?? '')) ?? TaskPriority::MEDIUM)
            ->setDueAt(isset($payload['dueAt']) ? new DateTimeImmutable((string)$payload['dueAt']) : null)
            ->setEstimatedHours(isset($payload['estimatedHours']) ? (float)$payload['estimatedHours'] : null);

        $project = null;
        if (is_string($payload['projectId'] ?? null)) {
            $project = $this->projectRepository->findOneScopedById($payload['projectId'], $crm->getId());
            if ($project === null) {
                return new JsonResponse(['message' => 'Unknown "projectId" in this CRM scope.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $task->setProject($project);
        }

        if (is_string($payload['sprintId'] ?? null)) {
            $sprint = $this->sprintRepository->findOneScopedById($payload['sprintId'], $crm->getId());
            if ($sprint === null) {
                return new JsonResponse(['message' => 'Unknown "sprintId" in this CRM scope.'], JsonResponse::HTTP_NOT_FOUND);
            }

            if ($project !== null && $sprint->getProject()?->getId() !== $project->getId()) {
                return new JsonResponse(['message' => 'Provided "sprintId" does not belong to the provided "projectId".'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $task->setSprint($sprint);
        }

        if (is_array($payload['assigneeIds'] ?? null)) {
            foreach ($payload['assigneeIds'] as $assigneeId) {
                if (!is_string($assigneeId) || $assigneeId === '') {
                    continue;
                }

                $assignee = $this->entityManager->getRepository(User::class)->find($assigneeId);
                if ($assignee instanceof User) {
                    $task->addAssignee($assignee);
                }
            }
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('crm_task', $task->getId()));

        return new JsonResponse([
            'id' => $task->getId(),
        ], JsonResponse::HTTP_CREATED);
    }
}
