<?php

declare(strict_types=1);

namespace App\Recruit\Domain\Enum;

enum InterviewStatus: string
{
    case PLANNED = 'planned';
    case DONE = 'done';
    case CANCELED = 'canceled';
}
