<?php

declare(strict_types=1);

namespace App\Chat\Domain\Enum;

enum ConversationParticipantRole: string
{
    case OWNER = 'owner';
    case MEMBER = 'member';
    case MODERATOR = 'moderator';
}
