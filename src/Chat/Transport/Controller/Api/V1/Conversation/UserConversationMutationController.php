<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Conversation;

use App\Chat\Domain\Entity\Conversation;
use App\Chat\Domain\Entity\ConversationParticipant;
use App\Chat\Infrastructure\Repository\ChatRepository;
use App\Chat\Infrastructure\Repository\ConversationParticipantRepository;
use App\Chat\Infrastructure\Repository\ConversationRepository;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Chat Conversation')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class UserConversationMutationController
{
    public function __construct(
        private readonly ChatRepository $chatRepository,
        private readonly UserRepository $userRepository,
        private readonly ConversationRepository $conversationRepository,
        private readonly ConversationParticipantRepository $participantRepository,
    ) {
    }

    #[Route(path: '/v1/chat/private/chats/{chatId}/conversations', methods: [Request::METHOD_POST])]
    public function create(string $chatId, Request $request, User $loggedInUser): JsonResponse
    {
        $chat = $this->chatRepository->find($chatId);
        if ($chat === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Chat not found.');
        }

        $payload = $request->toArray();
        $targetUserId = $payload['userId'] ?? null;
        if (!is_string($targetUserId) || $targetUserId === '') {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "userId" is required.');
        }

        $targetUser = $this->userRepository->find($targetUserId);
        if (!$targetUser instanceof User) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Unknown userId.');
        }

        $conversation = (new Conversation())->setChat($chat);
        $this->conversationRepository->save($conversation, false);

        $this->participantRepository->save((new ConversationParticipant())->setConversation($conversation)->setUser($loggedInUser), false);
        if ($targetUser->getId() !== $loggedInUser->getId()) {
            $this->participantRepository->save((new ConversationParticipant())->setConversation($conversation)->setUser($targetUser), false);
        }
        $this->conversationRepository->getEntityManager()->flush();

        return new JsonResponse(['id' => $conversation->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route(path: '/v1/chat/private/conversations/{conversationId}', methods: [Request::METHOD_PATCH])]
    public function patch(string $conversationId, Request $request, User $loggedInUser): JsonResponse
    {
        $conversation = $this->findParticipantConversation($conversationId, $loggedInUser);
        $payload = $request->toArray();

        $targetUserId = $payload['userId'] ?? null;
        if (!is_string($targetUserId) || $targetUserId === '') {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "userId" is required.');
        }

        $targetUser = $this->userRepository->find($targetUserId);
        if (!$targetUser instanceof User) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Unknown userId.');
        }

        $alreadyParticipant = $this->participantRepository->findOneByConversationAndUser($conversation, $targetUser);
        if (!$alreadyParticipant instanceof ConversationParticipant) {
            $this->participantRepository->save(
                (new ConversationParticipant())->setConversation($conversation)->setUser($targetUser)
            );
        }

        return new JsonResponse(['id' => $conversation->getId()]);
    }

    #[Route(path: '/v1/chat/private/conversations/{conversationId}', methods: [Request::METHOD_DELETE])]
    public function delete(string $conversationId, User $loggedInUser): JsonResponse
    {
        $conversation = $this->findParticipantConversation($conversationId, $loggedInUser);
        $this->conversationRepository->remove($conversation);

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
}
