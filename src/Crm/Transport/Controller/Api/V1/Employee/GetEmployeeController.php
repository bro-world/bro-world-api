<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Employee;

use App\Crm\Application\Service\EmployeeReadService;
use App\Crm\Domain\Entity\Employee;
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
final readonly class GetEmployeeController
{
    public function __construct(
        private EmployeeReadService $employeeReadService,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/employees/{employee}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Employee $employee): JsonResponse
    {
        $payload = $this->employeeReadService->getDetail($applicationSlug, $employee->getId());
        if ($payload === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Employee not found for this CRM scope.');
        }

        return new JsonResponse($payload);
    }
}
