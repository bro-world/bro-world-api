<?php

declare(strict_types=1);

namespace App\Recruit\Domain\Enum;

enum ApplicationStatus: string
{
    case WAITING = 'WAITING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DISCUSSION = 'DISCUSSION';
    case INVITE_TO_INTERVIEW = 'INVITE_TO_INTERVIEW';
    case INTERVIEW = 'INTERVIEW';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
}
