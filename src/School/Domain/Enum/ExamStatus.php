<?php

declare(strict_types=1);

namespace App\School\Domain\Enum;

enum ExamStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case CLOSED = 'CLOSED';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
