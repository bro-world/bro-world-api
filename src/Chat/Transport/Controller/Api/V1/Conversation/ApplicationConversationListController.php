<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Conversation;

use App\Chat\Application\Service\ConversationListService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Chat Conversation')]
class ApplicationConversationListController
{
    public function __construct(private readonly ConversationListService $conversationListService)
    {
    }

    #[Route(path: '/v1/chat/chats/{chatId}/conversations', methods: [Request::METHOD_GET])]
    public function __invoke(string $chatId): JsonResponse
    {
        return ConversationJsonResponseFactory::create(
            $this->conversationListService->getByChatId($chatId)
        );
    }
}
