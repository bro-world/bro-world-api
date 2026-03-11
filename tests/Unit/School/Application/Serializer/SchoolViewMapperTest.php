<?php

declare(strict_types=1);

namespace App\Tests\Unit\School\Application\Serializer;

use App\School\Application\Serializer\SchoolViewMapper;
use App\School\Domain\Entity\Exam;
use App\School\Domain\Entity\SchoolClass;
use App\School\Domain\Entity\Teacher;
use App\School\Domain\Enum\ExamStatus;
use App\School\Domain\Enum\ExamType;
use App\School\Domain\Enum\Term;
use PHPUnit\Framework\TestCase;

final class SchoolViewMapperTest extends TestCase
{
    public function testMapExamCollectionIncludesEnumValues(): void
    {
        $schoolClass = (new SchoolClass())->setName('Classe A - Sciences');
        $teacher = (new Teacher())->setName('Mme Martin');
        $exam = (new Exam())
            ->setTitle('Examen Mathematiques - Trimestre 1')
            ->setSchoolClass($schoolClass)
            ->setTeacher($teacher)
            ->setType(ExamType::FINAL)
            ->setStatus(ExamStatus::PUBLISHED)
            ->setTerm(Term::TERM_2);

        $result = (new SchoolViewMapper())->mapExamCollection([$exam]);

        self::assertSame('FINAL', $result[0]['type']);
        self::assertSame('PUBLISHED', $result[0]['status']);
        self::assertSame('TERM_2', $result[0]['term']);
    }
}
