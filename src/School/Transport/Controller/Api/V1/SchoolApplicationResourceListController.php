<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1;

use App\School\Application\Serializer\SchoolApiResponseSerializer;
use App\School\Application\Serializer\SchoolViewMapper;
use App\School\Application\Service\SchoolApplicationScopeResolver;
use App\School\Infrastructure\Repository\ExamRepository;
use App\School\Infrastructure\Repository\GradeRepository;
use App\School\Infrastructure\Repository\StudentRepository;
use App\School\Infrastructure\Repository\TeacherRepository;
use Doctrine\ORM\QueryBuilder;
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
final readonly class SchoolApplicationResourceListController
{
    public function __construct(
        private SchoolApplicationScopeResolver $scopeResolver,
        private StudentRepository $studentRepository,
        private TeacherRepository $teacherRepository,
        private ExamRepository $examRepository,
        private GradeRepository $gradeRepository,
        private SchoolViewMapper $viewMapper,
        private SchoolApiResponseSerializer $responseSerializer,
    ) {
    }

    #[Route('/v1/school/applications/{applicationSlug}/{resource}', methods: [Request::METHOD_GET], requirements: ['resource' => 'students|teachers|exams|grades'])]
    public function __invoke(string $applicationSlug, string $resource): JsonResponse
    {
        $school = $this->scopeResolver->resolveOrCreateSchoolByApplicationSlug($applicationSlug);

        $items = match ($resource) {
            'students' => $this->viewMapper->mapStudentCollection($this->studentsQueryBuilder($school->getId())->getQuery()->getResult()),
            'teachers' => $this->viewMapper->mapTeacherCollection($this->teachersQueryBuilder($school->getId())->getQuery()->getResult()),
            'exams' => $this->viewMapper->mapExamCollection($this->examsQueryBuilder($school->getId())->getQuery()->getResult()),
            'grades' => $this->viewMapper->mapGradeCollection($this->gradesQueryBuilder($school->getId())->getQuery()->getResult()),
        };

        return new JsonResponse($this->responseSerializer->list($items, null, [
            'applicationSlug' => $applicationSlug,
            'schoolId' => $school->getId(),
        ]));
    }

    private function studentsQueryBuilder(string $schoolId): QueryBuilder
    {
        return $this->studentRepository->createQueryBuilder('student')
            ->innerJoin('student.schoolClass', 'class')
            ->innerJoin('class.school', 'school')
            ->andWhere('school.id = :schoolId')
            ->setParameter('schoolId', $schoolId)
            ->orderBy('student.createdAt', 'DESC')
            ->setMaxResults(200);
    }

    private function teachersQueryBuilder(string $schoolId): QueryBuilder
    {
        return $this->teacherRepository->createQueryBuilder('teacher')
            ->innerJoin('teacher.classes', 'class')
            ->innerJoin('class.school', 'school')
            ->andWhere('school.id = :schoolId')
            ->setParameter('schoolId', $schoolId)
            ->orderBy('teacher.createdAt', 'DESC')
            ->distinct()
            ->setMaxResults(200);
    }

    private function examsQueryBuilder(string $schoolId): QueryBuilder
    {
        return $this->examRepository->createQueryBuilder('exam')
            ->innerJoin('exam.schoolClass', 'class')
            ->innerJoin('class.school', 'school')
            ->andWhere('school.id = :schoolId')
            ->setParameter('schoolId', $schoolId)
            ->orderBy('exam.createdAt', 'DESC')
            ->setMaxResults(200);
    }

    private function gradesQueryBuilder(string $schoolId): QueryBuilder
    {
        return $this->gradeRepository->createQueryBuilder('grade')
            ->innerJoin('grade.exam', 'exam')
            ->innerJoin('exam.schoolClass', 'class')
            ->innerJoin('class.school', 'school')
            ->andWhere('school.id = :schoolId')
            ->setParameter('schoolId', $schoolId)
            ->orderBy('grade.createdAt', 'DESC')
            ->setMaxResults(200);
    }
}
