<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1;

use App\General\Application\Message\EntityCreated;
use App\General\Application\Message\EntityDeleted;
use App\School\Application\Service\ExamListService;
use App\School\Domain\Entity\Exam;
use App\School\Domain\Entity\Grade;
use App\School\Domain\Entity\SchoolClass;
use App\School\Domain\Entity\Student;
use App\School\Domain\Entity\Teacher;
use App\School\Infrastructure\Repository\ExamRepository;
use App\School\Infrastructure\Repository\GradeRepository;
use App\School\Infrastructure\Repository\SchoolClassRepository;
use App\School\Infrastructure\Repository\SchoolRepository;
use App\School\Infrastructure\Repository\StudentRepository;
use App\School\Infrastructure\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
final readonly class SchoolController
{
    public function __construct(
        private SchoolClassRepository $classRepository,
        private StudentRepository $studentRepository,
        private TeacherRepository $teacherRepository,
        private ExamRepository $examRepository,
        private GradeRepository $gradeRepository,
        private SchoolRepository $schoolRepository,
        private ExamListService $examListService,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/v1/school/classes', methods: [Request::METHOD_GET])]
    public function classes(): JsonResponse
    {
        $items = array_map(static fn (SchoolClass $schoolClass): array => ['id' => $schoolClass->getId(), 'name' => $schoolClass->getName(), 'schoolId' => $schoolClass->getSchool()?->getId()], $this->classRepository->findBy([], ['createdAt' => 'DESC'], 200));
        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/school/classes', methods: [Request::METHOD_POST])]
    public function createClass(Request $request): JsonResponse
    {
        $payload = (array) json_decode((string) $request->getContent(), true);
        $schoolClass = new SchoolClass();
        $schoolClass->setName((string) ($payload['name'] ?? ''));
        if (is_string($payload['schoolId'] ?? null)) {
            $schoolClass->setSchool($this->schoolRepository->find($payload['schoolId']));
        }

        $this->entityManager->persist($schoolClass);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('school_class', $schoolClass->getId()));

        return new JsonResponse(['id' => $schoolClass->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/school/classes/{id}', methods: [Request::METHOD_DELETE])]
    public function deleteClass(string $id): JsonResponse
    {
        $schoolClass = $this->classRepository->find($id);
        if (!$schoolClass instanceof SchoolClass) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($schoolClass);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityDeleted('school_class', $id));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/school/students', methods: [Request::METHOD_GET])]
    public function students(): JsonResponse
    {
        $items = array_map(static fn (Student $student): array => ['id' => $student->getId(), 'name' => $student->getName(), 'classId' => $student->getSchoolClass()?->getId()], $this->studentRepository->findBy([], ['createdAt' => 'DESC'], 200));
        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/school/students', methods: [Request::METHOD_POST])]
    public function createStudent(Request $request): JsonResponse
    {
        $payload = (array) json_decode((string) $request->getContent(), true);
        $student = new Student();
        $student->setName((string) ($payload['name'] ?? ''));
        if (is_string($payload['classId'] ?? null)) {
            $student->setSchoolClass($this->classRepository->find($payload['classId']));
        }

        $this->entityManager->persist($student);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('school_student', $student->getId()));

        return new JsonResponse(['id' => $student->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/school/students/{id}', methods: [Request::METHOD_DELETE])]
    public function deleteStudent(string $id): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        if (!$student instanceof Student) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($student);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityDeleted('school_student', $id));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/school/teachers', methods: [Request::METHOD_GET])]
    public function teachers(): JsonResponse
    {
        $items = array_map(static fn (Teacher $teacher): array => ['id' => $teacher->getId(), 'name' => $teacher->getName()], $this->teacherRepository->findBy([], ['createdAt' => 'DESC'], 200));
        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/school/teachers', methods: [Request::METHOD_POST])]
    public function createTeacher(Request $request): JsonResponse
    {
        $payload = (array) json_decode((string) $request->getContent(), true);
        $teacher = new Teacher();
        $teacher->setName((string) ($payload['name'] ?? ''));

        $this->entityManager->persist($teacher);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('school_teacher', $teacher->getId()));

        return new JsonResponse(['id' => $teacher->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/school/teachers/{id}', methods: [Request::METHOD_DELETE])]
    public function deleteTeacher(string $id): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        if (!$teacher instanceof Teacher) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($teacher);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityDeleted('school_teacher', $id));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/school/exams', methods: [Request::METHOD_GET])]
    public function exams(Request $request): JsonResponse
    {
        return new JsonResponse($this->examListService->getList($request));
    }

    #[Route('/v1/school/exams', methods: [Request::METHOD_POST])]
    public function createExam(Request $request): JsonResponse
    {
        $payload = (array) json_decode((string) $request->getContent(), true);
        $exam = new Exam();
        $exam->setTitle((string) ($payload['title'] ?? ''));
        if (is_string($payload['classId'] ?? null)) {
            $exam->setSchoolClass($this->classRepository->find($payload['classId']));
        }
        if (is_string($payload['teacherId'] ?? null)) {
            $exam->setTeacher($this->teacherRepository->find($payload['teacherId']));
        }

        $this->entityManager->persist($exam);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('school_exam', $exam->getId()));

        return new JsonResponse(['id' => $exam->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/school/exams/{id}', methods: [Request::METHOD_DELETE])]
    public function deleteExam(string $id): JsonResponse
    {
        $exam = $this->examRepository->find($id);
        if (!$exam instanceof Exam) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($exam);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityDeleted('school_exam', $id));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/v1/school/grades', methods: [Request::METHOD_GET])]
    public function grades(): JsonResponse
    {
        $items = array_map(static fn (Grade $grade): array => ['id' => $grade->getId(), 'score' => $grade->getScore(), 'studentId' => $grade->getStudent()?->getId(), 'examId' => $grade->getExam()?->getId()], $this->gradeRepository->findBy([], ['createdAt' => 'DESC'], 200));
        return new JsonResponse(['items' => $items]);
    }

    #[Route('/v1/school/grades', methods: [Request::METHOD_POST])]
    public function createGrade(Request $request): JsonResponse
    {
        $payload = (array) json_decode((string) $request->getContent(), true);
        $grade = new Grade();
        $grade->setScore((float) ($payload['score'] ?? 0));
        if (is_string($payload['studentId'] ?? null)) {
            $grade->setStudent($this->studentRepository->find($payload['studentId']));
        }
        if (is_string($payload['examId'] ?? null)) {
            $grade->setExam($this->examRepository->find($payload['examId']));
        }

        $this->entityManager->persist($grade);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityCreated('school_grade', $grade->getId()));

        return new JsonResponse(['id' => $grade->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/v1/school/grades/{id}', methods: [Request::METHOD_DELETE])]
    public function deleteGrade(string $id): JsonResponse
    {
        $grade = $this->gradeRepository->find($id);
        if (!$grade instanceof Grade) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($grade);
        $this->entityManager->flush();
        $this->messageBus->dispatch(new EntityDeleted('school_grade', $id));

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
