<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Project\Github;

use App\Crm\Application\Service\CrmGithubService;
use App\Crm\Domain\Entity\Project;
use App\Role\Domain\Enum\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class ListProjectGithubPullRequestsController
{
    public function __construct(
        private CrmGithubService $crmGithubService,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/projects/{project}/github/pull-requests', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Project $project, Request $request): JsonResponse
    {
        return new JsonResponse($this->crmGithubService->listPullRequests(
            $project,
            (string)$request->query->get('repo', ''),
            (string)$request->query->get('state', 'open'),
            $request->query->get('author') !== null ? (string)$request->query->get('author') : null,
            trim((string)$request->query->get('q', '')),
            max(1, $request->query->getInt('page', 1)),
            max(1, min(100, $request->query->getInt('limit', 30))),
        ));
    }
}
