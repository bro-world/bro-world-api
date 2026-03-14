<?php

declare(strict_types=1);

namespace App\Recruit\Domain\Enum;

enum InterviewMode: string
{
    case VISIO = 'visio';
    case ON_SITE = 'on-site';
}
