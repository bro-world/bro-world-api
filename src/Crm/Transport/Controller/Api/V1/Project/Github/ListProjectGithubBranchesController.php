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
use OpenApi\Attributes as OA;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class ListProjectGithubBranchesController
{
    public function __construct(
        private CrmGithubService $crmGithubService,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/projects/{project}/github/branches', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Project $project, Request $request): JsonResponse
    {
        $repo = (string)$request->query->get('repo', '');

        return new JsonResponse($this->crmGithubService->listBranches(
            $project,
            $repo,
            max(1, $request->query->getInt('page', 1)),
            max(1, min(100, $request->query->getInt('limit', 30))),
            trim((string)$request->query->get('q', '')),
        ));
    }
}
