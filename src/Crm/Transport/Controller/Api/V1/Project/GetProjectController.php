<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Project;

use App\Crm\Application\Security\CrmPermissions;
use App\Crm\Domain\Entity\Project;
use App\Crm\Domain\Entity\Task;
use App\Role\Domain\Enum\Role;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_MANAGER->value)]
final readonly class GetProjectController
{
    #[Route('/v1/crm/applications/{applicationSlug}/projects/{project}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Project $project): JsonResponse
    {
        return new JsonResponse([
            'id' => $project->getId(),
            'companyId' => $project->getCompany()?->getId(),
            'name' => $project->getName(),
            'code' => $project->getCode(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus()->value,
            'startedAt' => $project->getStartedAt()?->format(DATE_ATOM),
            'dueAt' => $project->getDueAt()?->format(DATE_ATOM),
            'attachments' => $project->getAttachments(),
            'wikiPages' => $project->getWikiPages(),
            'tasks' => array_map(
                static fn (Task $task) => [
                    'id' => $task->getId(),
                    'TITLE' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'status' => $task->getStatus()->value,
                    'dueAt' => $task->getDueAt()?->format(DATE_ATOM),
                ],
                $project->getTasks()->toArray()),
            'assignees' => array_map(
                static fn ($assignee) => [
                    'id' => $assignee->getId(),
                    'email' => $assignee->getEmail(),
                    'firstName' => $assignee->getFirstName(),
                    'lastName' => $assignee->getLastName(),
                    'photo' => $assignee->getPhoto(),
                ],
                $project->getAssignees()->toArray())
        ]);
    }
}
