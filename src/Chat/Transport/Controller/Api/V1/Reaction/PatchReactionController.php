<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Reaction;

use App\Chat\Domain\Entity\ChatMessageReaction;
use App\Chat\Domain\Enum\ChatReactionType;
use App\Chat\Infrastructure\Repository\ChatMessageReactionRepository;
use App\General\Application\Service\CacheInvalidationService;
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
#[OA\Tag(name: 'Chat Message Reaction')]
#[OA\Patch(path: '/v1/chat/private/reactions/{reactionId}', operationId: 'chat_reaction_patch', summary: 'Modifier une réaction', tags: ['Chat Message Reaction'], parameters: [new OA\Parameter(name: 'reactionId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [new OA\Property(property: 'reaction', type: 'string', enum: ChatReactionType::VALUES, example: 'love')], example: [
    'reaction' => 'love',
])), responses: [new OA\Response(response: 200, description: 'Réaction modifiée', content: new OA\JsonContent(example: [
    'id' => '8f210e56-6550-4b61-b7f3-8994f5f6dc41',
])), new OA\Response(response: 404, description: 'Réaction introuvable')])]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class PatchReactionController
{
    public function __construct(
        private readonly ChatMessageReactionRepository $reactionRepository,
        private readonly CacheInvalidationService $cacheInvalidationService,
    ) {
    }

    #[Route(path: '/v1/chat/private/reactions/{reactionId}', methods: [Request::METHOD_PATCH])]
    public function __invoke(string $reactionId, Request $request, User $loggedInUser): JsonResponse
    {
        $reaction = $this->findOwnReaction($reactionId, $loggedInUser);
        $payload = $request->toArray();

        if (array_key_exists('reaction', $payload)) {
            $reaction->setReaction($this->parseReactionType($payload['reaction']));
            $this->reactionRepository->save($reaction);
            $this->cacheInvalidationService->invalidateConversationCaches($reaction->getMessage()->getConversation()->getChat()->getId(), $loggedInUser->getId());
        }

        return new JsonResponse([
            'id' => $reaction->getId(),
        ]);
    }

    private function parseReactionType(mixed $reaction): ChatReactionType
    {
        if (!is_string($reaction) || $reaction === '') {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "reaction" must be a non-empty string.');
        }

        $reactionType = ChatReactionType::tryFrom($reaction);
        if (!$reactionType instanceof ChatReactionType) {
            throw new HttpException(
                JsonResponse::HTTP_BAD_REQUEST,
                sprintf('Invalid reaction "%s". Allowed values: %s.', $reaction, implode(', ', ChatReactionType::VALUES)),
            );
        }

        return $reactionType;
    }

    private function findOwnReaction(string $reactionId, User $loggedInUser): ChatMessageReaction
    {
        $reaction = $this->reactionRepository->find($reactionId);
        if (!$reaction instanceof ChatMessageReaction || $reaction->getUser()->getId() !== $loggedInUser->getId()) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Reaction not found.');
        }

        return $reaction;
    }
}
