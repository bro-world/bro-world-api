<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1;

use App\School\Application\Service\SchoolApplicationScopeResolver;
use App\School\Application\Service\SchoolResourceAccessService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'School')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
final readonly class SchoolApplicationResourceController
{
    public function __construct(
        private SchoolApplicationScopeResolver $scopeResolver,
        private SchoolResourceAccessService $resourceAccessService,
        private SchoolResourceController $schoolResourceController,
    ) {
    }

    #[Route('/v1/school/applications/{applicationSlug}/{resource}/{id}', methods: [Request::METHOD_GET], requirements: ['resource' => 'classes|students|teachers|exams|grades'])]
    public function getOne(string $applicationSlug, string $resource, string $id): JsonResponse
    {
        $school = $this->scopeResolver->resolveOrCreateSchoolByApplicationSlug($applicationSlug);
        $entity = $this->resourceAccessService->find($resource, $id);

        if ($entity === null || !$this->resourceAccessService->belongsToSchool($entity, $school)) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Resource not found in application scope.');
        }

        return $this->schoolResourceController->getOne($resource, $id);
    }

    #[Route('/v1/school/applications/{applicationSlug}/{resource}/{id}', methods: [Request::METHOD_PATCH], requirements: ['resource' => 'classes|students|teachers|exams|grades'])]
    public function patchOne(string $applicationSlug, string $resource, string $id, Request $request): JsonResponse
    {
        $school = $this->scopeResolver->resolveOrCreateSchoolByApplicationSlug($applicationSlug);
        $entity = $this->resourceAccessService->find($resource, $id);

        if ($entity === null || !$this->resourceAccessService->belongsToSchool($entity, $school)) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Resource not found in application scope.');
        }

        return $this->schoolResourceController->patchOne($resource, $id, $request);
    }
}
