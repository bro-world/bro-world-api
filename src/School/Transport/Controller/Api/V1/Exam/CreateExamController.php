<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1\Exam;

use App\School\Application\Service\CreateExamService;
use App\School\Transport\Controller\Api\V1\Input\CreateExamInput;
use App\School\Transport\Controller\Api\V1\Input\SchoolInputValidator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'School')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
final readonly class CreateExamController
{
    public function __construct(
        private CreateExamService $createExamService,
        private SchoolInputValidator $inputValidator,
    ) {
    }

    #[Route('/v1/school/exams', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->toArray();

        $input = new CreateExamInput();
        $input->title = (string)($payload['title'] ?? '');
        $input->classId = is_string($payload['classId'] ?? null) ? $payload['classId'] : '';
        $input->teacherId = is_string($payload['teacherId'] ?? null) ? $payload['teacherId'] : '';

        $validationResponse = $this->inputValidator->validate($input);
        if ($validationResponse instanceof JsonResponse) {
            return $validationResponse;
        }

        $exam = $this->createExamService->create($input->title, $input->classId, $input->teacherId);

        return new JsonResponse(['id' => $exam->getId()], JsonResponse::HTTP_CREATED);
    }
}
