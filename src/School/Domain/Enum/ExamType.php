<?php

declare(strict_types=1);

namespace App\School\Domain\Enum;

enum ExamType: string
{
    case QUIZ = 'QUIZ';
    case MIDTERM = 'MIDTERM';
    case FINAL = 'FINAL';
    case ORAL = 'ORAL';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
