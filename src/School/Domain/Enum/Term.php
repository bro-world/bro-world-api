<?php

declare(strict_types=1);

namespace App\School\Domain\Enum;

enum Term: string
{
    case TERM_1 = 'TERM_1';
    case TERM_2 = 'TERM_2';
    case TERM_3 = 'TERM_3';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
