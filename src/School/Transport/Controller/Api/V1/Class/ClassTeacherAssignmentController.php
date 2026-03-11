<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1\Class;

use App\School\Domain\Entity\SchoolClass;
use App\School\Domain\Entity\Teacher;
use App\School\Infrastructure\Repository\SchoolClassRepository;
use App\School\Infrastructure\Repository\TeacherRepository;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
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
final readonly class ClassTeacherAssignmentController
{
    public function __construct(
        private SchoolClassRepository $classRepository,
        private TeacherRepository $teacherRepository,
    ) {
    }

    #[Route('/v1/school/classes/{id}/teachers/{teacherId}', methods: [Request::METHOD_POST])]
    public function assign(string $id, string $teacherId): JsonResponse
    {
        [$class, $teacher] = $this->resolve($id, $teacherId);

        if (!$teacher->getClasses()->contains($class)) {
            $teacher->getClasses()->add($class);
            $this->teacherRepository->save($teacher);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/school/classes/{id}/teachers/{teacherId}', methods: [Request::METHOD_DELETE])]
    public function unassign(string $id, string $teacherId): JsonResponse
    {
        [$class, $teacher] = $this->resolve($id, $teacherId);

        if ($teacher->getClasses()->contains($class)) {
            $teacher->getClasses()->removeElement($class);
            $this->teacherRepository->save($teacher);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /** @return array{0:SchoolClass,1:Teacher} */
    private function resolve(string $id, string $teacherId): array
    {
        if (!Uuid::isValid($id) || !Uuid::isValid($teacherId)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid identifier format.');
        }

        $class = $this->classRepository->find($id);
        if (!$class instanceof SchoolClass) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Class not found.');
        }

        $teacher = $this->teacherRepository->find($teacherId);
        if (!$teacher instanceof Teacher) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Teacher not found.');
        }

        return [$class, $teacher];
    }
}
