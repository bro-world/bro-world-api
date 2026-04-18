<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Conversation;

use App\Chat\Application\Message\FindOrCreateConversationWithUserCommand;
use App\Chat\Domain\Entity\Chat;
use App\Chat\Domain\Entity\Conversation;
use App\Chat\Domain\Entity\ConversationParticipant;
use App\Chat\Domain\Enum\ConversationParticipantRole;
use App\Chat\Domain\Enum\ConversationType;
use App\Chat\Infrastructure\Repository\ChatRepository;
use App\Chat\Infrastructure\Repository\ConversationParticipantRepository;
use App\Chat\Infrastructure\Repository\ConversationRepository;
use App\General\Application\Service\OperationIdGeneratorService;
use App\General\Domain\Service\Interfaces\MessageServiceInterface;
use App\User\Domain\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[AsController]
#[OA\Tag(name: 'Chat Conversation')]
#[OA\Post(path: '/v1/chat/private/conversation/{user}/user', operationId: 'chat_conversation_find_or_create_with_user', summary: 'Trouver ou créer une conversation directe avec un utilisateur', tags: ['Chat Conversation'])]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
readonly class FindOrCreateConversationWithUserController
{
    public function __construct(
        private MessageServiceInterface $messageService,
        private OperationIdGeneratorService $operationIdGeneratorService,
        private ConversationRepository $conversationRepository,
        private ChatRepository $chatRepository,
        private ConversationParticipantRepository $participantRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route(path: '/v1/chat/private/conversation/{user}/user', methods: [Request::METHOD_POST])]
    public function __invoke(User $user, User $loggedInUser): JsonResponse
    {
        $existingConversation = $this->conversationRepository->findDirectConversationBetweenUsers($loggedInUser, $user);

        if ($existingConversation instanceof Conversation) {
            return new JsonResponse([
                'chatId' => $existingConversation->getChat()->getId(),
                'conversationId' => $existingConversation->getId(),
                'actorUserId' => $loggedInUser->getId(),
                'targetUserId' => $user->getId(),
                'created' => false,
            ], JsonResponse::HTTP_ACCEPTED);
        }

        $chat = $this->chatRepository->findChatForDirectConversation($loggedInUser, $user);
        if (!$chat instanceof Chat) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'No chat available for these users in a shared application scope.');
        }

        $conversation = (new Conversation())
            ->setChat($chat)
            ->setType(ConversationType::DIRECT);
        $this->conversationRepository->save($conversation, false);
        $this->participantRepository->save((new ConversationParticipant())
            ->setConversation($conversation)
            ->setUser($user)
            ->setRole(ConversationParticipantRole::OWNER), false);
        if ($user->getId() !== $loggedInUser->getId()) {
            $this->participantRepository->save((new ConversationParticipant())
                ->setConversation($conversation)
                ->setUser($loggedInUser)
                ->setRole(ConversationParticipantRole::MEMBER), false);
        }

        $this->conversationRepository->getEntityManager()->flush();

        return new JsonResponse([
            'chatId' => $chat->getId(),
            'conversationId' => $conversation->getId(),
            'actorUserId' => $loggedInUser->getId(),
            'targetUserId' => $user->getId(),
            'created' => true,
        ], JsonResponse::HTTP_ACCEPTED);
    }
}
