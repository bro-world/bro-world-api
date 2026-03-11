<?php

declare(strict_types=1);

namespace App\Chat\Domain\Enum;

enum ConversationType: string
{
    case DIRECT = 'direct';
    case GROUP = 'group';
}
