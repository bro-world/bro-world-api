<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Company;

use App\Crm\Domain\Entity\Company;
use App\Crm\Domain\Entity\Project;
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
final readonly class GetCompanyController
{
    #[Route('/v1/crm/applications/{applicationSlug}/companies/{id}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Company $company): JsonResponse
    {
        return new JsonResponse([
            'id' => $company->getId(),
            'name' => $company->getName(),
            'industry' => $company->getIndustry(),
            'website' => $company->getWebsite(),
            'contactEmail' => $company->getContactEmail(),
            'phone' => $company->getPhone(),
            'projects' => array_map(
                static fn (Project $project) =>
                [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                ],
                $company->getProjects()->toArray())
        ]);
    }
}
