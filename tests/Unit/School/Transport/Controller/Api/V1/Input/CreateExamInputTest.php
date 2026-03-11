<?php

declare(strict_types=1);

namespace App\Tests\Unit\School\Transport\Controller\Api\V1\Input;

use App\School\Transport\Controller\Api\V1\Input\CreateExamInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CreateExamInputTest extends TestCase
{
    public function testEnumValidationRejectsUnknownValues(): void
    {
        $input = new CreateExamInput();
        $input->title = 'Exam';
        $input->classId = '550e8400-e29b-41d4-a716-446655440000';
        $input->teacherId = '550e8400-e29b-41d4-a716-446655440001';
        $input->type = 'HOMEWORK';
        $input->status = 'ARCHIVED';
        $input->term = 'TERM_4';

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($input);

        self::assertGreaterThanOrEqual(3, $violations->count());
    }

    public function testEnumValidationAcceptsAllowedValues(): void
    {
        $input = new CreateExamInput();
        $input->title = 'Exam';
        $input->classId = '550e8400-e29b-41d4-a716-446655440000';
        $input->teacherId = '550e8400-e29b-41d4-a716-446655440001';
        $input->type = 'QUIZ';
        $input->status = 'DRAFT';
        $input->term = 'TERM_1';

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        self::assertCount(0, $validator->validate($input));
    }
}
