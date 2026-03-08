<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Message;

use App\Chat\Domain\Entity\ChatMessage;
use App\Chat\Domain\Entity\Conversation;
use App\Chat\Domain\Entity\ConversationParticipant;
use App\Chat\Infrastructure\Repository\ChatMessageRepository;
use App\Chat\Infrastructure\Repository\ConversationParticipantRepository;
use App\Chat\Infrastructure\Repository\ConversationRepository;
use App\User\Domain\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Chat Message')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class UserMessageMutationController
{
    public function __construct(
        private readonly ConversationRepository $conversationRepository,
        private readonly ConversationParticipantRepository $participantRepository,
        private readonly ChatMessageRepository $messageRepository,
    ) {
    }

    #[Route(path: '/v1/chat/private/conversations/{conversationId}/messages', methods: [Request::METHOD_POST])]
    public function create(string $conversationId, Request $request, User $loggedInUser): JsonResponse
    {
        $conversation = $this->findParticipantConversation($conversationId, $loggedInUser);
        $payload = $request->toArray();

        $content = $payload['content'] ?? null;
        if (!is_string($content) || $content === '') {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "content" is required.');
        }

        $message = (new ChatMessage())
            ->setConversation($conversation)
            ->setSender($loggedInUser)
            ->setContent($content)
            ->setAttachments([]);

        $this->messageRepository->save($message);

        return new JsonResponse(['id' => $message->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route(path: '/v1/chat/private/messages/{messageId}', methods: [Request::METHOD_PATCH])]
    public function patch(string $messageId, Request $request, User $loggedInUser): JsonResponse
    {
        $message = $this->findOwnMessage($messageId, $loggedInUser);
        $payload = $request->toArray();

        if (isset($payload['content']) && is_string($payload['content']) && $payload['content'] !== '') {
            $message->setContent($payload['content']);
            $this->messageRepository->save($message);
        }

        return new JsonResponse(['id' => $message->getId()]);
    }

    #[Route(path: '/v1/chat/private/messages/{messageId}', methods: [Request::METHOD_DELETE])]
    public function delete(string $messageId, User $loggedInUser): JsonResponse
    {
        $message = $this->findOwnMessage($messageId, $loggedInUser);
        $this->messageRepository->remove($message);

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    private function findParticipantConversation(string $conversationId, User $loggedInUser): Conversation
    {
        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation instanceof Conversation) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Conversation not found.');
        }

        $participant = $this->participantRepository->findOneByConversationAndUser($conversation, $loggedInUser);
        if (!$participant instanceof ConversationParticipant) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Conversation not found.');
        }

        return $conversation;
    }

    private function findOwnMessage(string $messageId, User $loggedInUser): ChatMessage
    {
        $message = $this->messageRepository->find($messageId);
        if (!$message instanceof ChatMessage || $message->getSender()->getId() !== $loggedInUser->getId()) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Message not found.');
        }

        return $message;
    }
}
