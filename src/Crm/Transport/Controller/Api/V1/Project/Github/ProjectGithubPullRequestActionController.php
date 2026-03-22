<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Project\Github;

use App\Crm\Application\Service\CrmGithubService;
use App\Crm\Domain\Entity\Project;
use App\Role\Domain\Enum\Role;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_MANAGER->value)]
final readonly class ProjectGithubPullRequestActionController
{
    public function __construct(
        private CrmGithubService $crmGithubService,
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Route('/v1/crm/applications/{applicationSlug}/projects/{project}/github/pull-requests/{number}/action', methods: [Request::METHOD_POST])]
    public function __invoke(string $applicationSlug, Project $project, int $number, Request $request): JsonResponse
    {
        $payload = json_decode((string)$request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $repo = isset($payload['repo']) ? (string)$payload['repo'] : '';
        $action = isset($payload['action']) ? (string)$payload['action'] : '';

        if ($action === 'merge') {
            return new JsonResponse($this->crmGithubService->mergePullRequest(
                $project,
                $repo,
                $number,
                isset($payload['mergeMethod']) ? (string)$payload['mergeMethod'] : 'merge',
            ));
        }

        if ($action === 'close') {
            return new JsonResponse($this->crmGithubService->closePullRequest($project, $repo, $number));
        }

        return new JsonResponse(['message' => 'Unknown action.'], JsonResponse::HTTP_BAD_REQUEST);
    }
}
