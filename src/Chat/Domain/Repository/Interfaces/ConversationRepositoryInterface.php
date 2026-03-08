<?php

declare(strict_types=1);

namespace App\Chat\Domain\Repository\Interfaces;

use App\Chat\Domain\Entity\Chat;
use App\Chat\Domain\Entity\Conversation;
use App\User\Domain\Entity\User;

interface ConversationRepositoryInterface
{
    public function findOneByChat(Chat $chat): ?Conversation;

    /**
     * @return array<int, Conversation>
     */
    public function findByUser(User $user): array;

    /**
     * @return array<int, Conversation>
     */
    public function findByChatId(string $chatId): array;

    /**
     * @return array<int, Conversation>
     */
    public function findByChatIdAndUser(string $chatId, User $user): array;
}
