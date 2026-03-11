<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1;

use App\General\Application\Message\EntityPatched;
use App\School\Application\Serializer\SchoolViewMapper;
use App\School\Application\Service\SchoolResourceAccessService;
use App\School\Domain\Entity\Exam;
use App\School\Domain\Entity\Grade;
use App\School\Domain\Entity\SchoolClass;
use App\School\Domain\Entity\Student;
use App\School\Domain\Entity\Teacher;
use App\School\Domain\Enum\ExamStatus;
use App\School\Domain\Enum\ExamType;
use App\School\Domain\Enum\Term;
use App\School\Infrastructure\Repository\ExamRepository;
use App\School\Infrastructure\Repository\GradeRepository;
use App\School\Infrastructure\Repository\SchoolClassRepository;
use App\School\Infrastructure\Repository\StudentRepository;
use App\School\Infrastructure\Repository\TeacherRepository;
use OpenApi\Attributes as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'School')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
final readonly class SchoolResourceController
{
    public function __construct(
        private SchoolResourceAccessService $resourceAccessService,
        private SchoolViewMapper $viewMapper,
        private SchoolClassRepository $classRepository,
        private StudentRepository $studentRepository,
        private TeacherRepository $teacherRepository,
        private ExamRepository $examRepository,
        private GradeRepository $gradeRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/v1/school/{resource}/{id}', methods: [Request::METHOD_GET], requirements: ['resource' => 'classes|students|teachers|exams|grades'])]
    public function getOne(string $resource, string $id): JsonResponse
    {
        $entity = $this->findOr404($resource, $id);

        return new JsonResponse($this->mapResource($entity));
    }

    #[Route('/v1/school/{resource}/{id}', methods: [Request::METHOD_PATCH], requirements: ['resource' => 'classes|students|teachers|exams|grades'])]
    public function patchOne(string $resource, string $id, Request $request): JsonResponse
    {
        $entity = $this->findOr404($resource, $id);
        $payload = $request->toArray();

        match ($resource) {
            'classes' => $this->patchClass($entity, $payload),
            'students' => $this->patchStudent($entity, $payload),
            'teachers' => $this->patchTeacher($entity, $payload),
            'exams' => $this->patchExam($entity, $payload),
            'grades' => $this->patchGrade($entity, $payload),
            default => throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource.'),
        };

        $this->messageBus->dispatch(new EntityPatched('school_' . substr($resource, 0, -1), $id));

        return new JsonResponse($this->mapResource($entity));
    }

    private function findOr404(string $resource, string $id): SchoolClass|Student|Teacher|Exam|Grade
    {
        if (!Uuid::isValid($id)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid identifier format.');
        }

        $entity = $this->resourceAccessService->find($resource, $id);
        if ($entity === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Resource not found.');
        }

        return $entity;
    }

    /** @param array<string,mixed> $payload */
    private function patchClass(SchoolClass|Student|Teacher|Exam|Grade $entity, array $payload): void
    {
        if (!$entity instanceof SchoolClass) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource payload.');
        }

        if (array_key_exists('name', $payload)) {
            $name = trim((string)$payload['name']);
            if ($name === '') {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "name" cannot be blank.');
            }
            $entity->setName($name);
        }

        $this->classRepository->save($entity);
    }

    /** @param array<string,mixed> $payload */
    private function patchStudent(SchoolClass|Student|Teacher|Exam|Grade $entity, array $payload): void
    {
        if (!$entity instanceof Student) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource payload.');
        }

        if (array_key_exists('name', $payload)) {
            $name = trim((string)$payload['name']);
            if ($name === '') {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "name" cannot be blank.');
            }
            $entity->setName($name);
        }

        if (array_key_exists('classId', $payload)) {
            $classId = (string)$payload['classId'];
            if (!Uuid::isValid($classId)) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "classId" must be a valid UUID.');
            }
            $schoolClass = $this->classRepository->find($classId);
            if (!$schoolClass instanceof SchoolClass) {
                throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Class not found.');
            }
            $entity->setSchoolClass($schoolClass);
        }

        $this->studentRepository->save($entity);
    }

    /** @param array<string,mixed> $payload */
    private function patchTeacher(SchoolClass|Student|Teacher|Exam|Grade $entity, array $payload): void
    {
        if (!$entity instanceof Teacher) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource payload.');
        }

        if (array_key_exists('name', $payload)) {
            $name = trim((string)$payload['name']);
            if ($name === '') {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "name" cannot be blank.');
            }
            $entity->setName($name);
        }

        $this->teacherRepository->save($entity);
    }

    /** @param array<string,mixed> $payload */
    private function patchExam(SchoolClass|Student|Teacher|Exam|Grade $entity, array $payload): void
    {
        if (!$entity instanceof Exam) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource payload.');
        }

        if (array_key_exists('title', $payload)) {
            $title = trim((string)$payload['title']);
            if ($title === '') {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "title" cannot be blank.');
            }
            $entity->setTitle($title);
        }

        if (array_key_exists('classId', $payload)) {
            $classId = (string)$payload['classId'];
            if (!Uuid::isValid($classId)) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "classId" must be a valid UUID.');
            }
            $class = $this->classRepository->find($classId);
            if (!$class instanceof SchoolClass) {
                throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Class not found.');
            }
            $entity->setSchoolClass($class);
        }

        if (array_key_exists('teacherId', $payload)) {
            $teacherId = (string)$payload['teacherId'];
            if (!Uuid::isValid($teacherId)) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "teacherId" must be a valid UUID.');
            }
            $teacher = $this->teacherRepository->find($teacherId);
            if (!$teacher instanceof Teacher) {
                throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Teacher not found.');
            }
            $entity->setTeacher($teacher);
        }

        if (array_key_exists('type', $payload)) {
            $type = ExamType::tryFrom((string)$payload['type']);
            if ($type === null) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Invalid exam type.');
            }
            $entity->setType($type);
        }

        if (array_key_exists('status', $payload)) {
            $status = ExamStatus::tryFrom((string)$payload['status']);
            if ($status === null) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Invalid exam status.');
            }
            $entity->setStatus($status);
        }

        if (array_key_exists('term', $payload)) {
            $term = Term::tryFrom((string)$payload['term']);
            if ($term === null) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Invalid exam term.');
            }
            $entity->setTerm($term);
        }

        $this->examRepository->save($entity);
    }

    /** @param array<string,mixed> $payload */
    private function patchGrade(SchoolClass|Student|Teacher|Exam|Grade $entity, array $payload): void
    {
        if (!$entity instanceof Grade) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid resource payload.');
        }

        if (array_key_exists('score', $payload)) {
            if (!is_numeric($payload['score'])) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, 'Field "score" must be numeric.');
            }
            $entity->setScore((float)$payload['score']);
        }

        $this->gradeRepository->save($entity);
    }

    /** @return array<string,mixed> */
    private function mapResource(SchoolClass|Student|Teacher|Exam|Grade $entity): array
    {
        return match (true) {
            $entity instanceof SchoolClass => $this->viewMapper->mapClass($entity),
            $entity instanceof Student => $this->viewMapper->mapStudent($entity),
            $entity instanceof Teacher => $this->viewMapper->mapTeacher($entity),
            $entity instanceof Exam => $this->viewMapper->mapExam($entity),
            $entity instanceof Grade => $this->viewMapper->mapGrade($entity),
        };
    }
}
