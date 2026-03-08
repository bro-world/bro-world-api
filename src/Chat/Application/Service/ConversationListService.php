<?php

declare(strict_types=1);

namespace App\Chat\Application\Service;

use App\Chat\Domain\Entity\Conversation;
use App\Chat\Domain\Entity\ChatMessage;
use App\Chat\Domain\Entity\ChatMessageReaction;
use App\Chat\Domain\Entity\ConversationParticipant;
use App\Chat\Domain\Repository\Interfaces\ConversationRepositoryInterface;
use App\User\Domain\Entity\User;

use function array_map;

final class ConversationListService
{
    public function __construct(private readonly ConversationRepositoryInterface $conversationRepository)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByUser(User $user): array
    {
        return $this->normalizeConversations($this->conversationRepository->findByUser($user));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByChatId(string $chatId): array
    {
        return $this->normalizeConversations($this->conversationRepository->findByChatId($chatId));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByChatIdAndUser(string $chatId, User $user): array
    {
        return $this->normalizeConversations($this->conversationRepository->findByChatIdAndUser($chatId, $user));
    }

    /**
     * @param array<int, Conversation> $conversations
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeConversations(array $conversations): array
    {
        return array_map(function (Conversation $conversation): array {
            return [
                'id' => $conversation->getId(),
                'chatId' => $conversation->getChat()->getId(),
                'participants' => array_map(static function (ConversationParticipant $participant): array {
                    return [
                        'id' => $participant->getId(),
                        'userId' => $participant?->getUser()?->getId(),
                    ];
                }, $conversation->getParticipants()->toArray()),
                'messages' => array_map(static function (ChatMessage $message): array {
                    return [
                        'id' => $message->getId(),
                        'content' => $message->getContent(),
                        'senderId' => $message?->getSender()?->getId(),
                        'attachments' => $message->getAttachments(),
                        'readAt' => $message->getReadAt()?->format(DATE_ATOM),
                        'createdAt' => $message->getCreatedAt()?->format(DATE_ATOM),
                        'reactions' => array_map(static fn (ChatMessageReaction $reaction): array => [
                            'id' => $reaction->getId(),
                            'userId' => $reaction->getUser()->getId(),
                            'reaction' => $reaction->getReaction(),
                        ], $message->getReactions()->toArray()),
                    ];
                }, $conversation->getMessages()->toArray()),
                'createdAt' => $conversation->getCreatedAt()?->format(DATE_ATOM),
            ];
        }, $conversations);
    }
}
