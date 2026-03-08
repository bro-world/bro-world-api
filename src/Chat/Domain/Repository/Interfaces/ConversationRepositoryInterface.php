<?php

declare(strict_types=1);

namespace App\Chat\Domain\Repository\Interfaces;

use App\Chat\Domain\Entity\Chat;
use App\Chat\Domain\Entity\Conversation;
use App\User\Domain\Entity\User;

interface ConversationRepositoryInterface
{
    public function findOneByChatAndApplicationSlug(Chat $chat, string $applicationSlug): ?Conversation;

    /**
     * @return array<int, Conversation>
     */
    public function findByUser(User $user): array;

    /**
     * @return array<int, Conversation>
     */
    public function findByApplicationSlug(string $applicationSlug): array;

    /**
     * @return array<int, Conversation>
     */
    public function findByApplicationSlugAndUser(string $applicationSlug, User $user): array;
}
