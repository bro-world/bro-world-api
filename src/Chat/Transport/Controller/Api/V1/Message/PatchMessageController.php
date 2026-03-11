<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Message;

use App\Chat\Application\Message\PatchMessageCommand;
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

#[AsController]
#[OA\Tag(name: 'Chat Message')]
#[OA\Patch(
    path: '/v1/chat/private/messages/{messageId}',
    operationId: 'chat_message_patch',
    summary: 'Modifier son message (update)',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'content', type: 'string', minLength: 1, example: 'Bonjour, finalement mercredi 10h ?'),
                new OA\Property(property: 'read', type: 'boolean', example: true),
            ],
            example: [
                'content' => 'Bonjour, finalement mercredi 10h ?',
                'read' => true,
            ]
        )
    ),
    tags: ['Chat Message'],
    parameters: [
        new OA\Parameter(name: 'messageId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')),
    ],
    responses: [
        new OA\Response(response: 202, description: 'Commande acceptée'),
        new OA\Response(response: 404, description: 'Message introuvable'),
    ]
)]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class PatchMessageController
{
    public function __construct(
        private readonly MessageServiceInterface $messageService,
    ) {
    }

    #[Route(path: '/v1/chat/private/messages/{messageId}', methods: [Request::METHOD_PATCH])]
    public function __invoke(string $messageId, Request $request, User $loggedInUser): JsonResponse
    {
        $payload = $request->toArray();

        $content = null;
        if (isset($payload['content'])) {
            if (!is_string($payload['content']) || $payload['content'] === '') {
                throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "content" must be a non-empty string when provided.');
            }

            $content = $payload['content'];
        }

        $read = null;
        if (array_key_exists('read', $payload)) {
            if (!is_bool($payload['read'])) {
                throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "read" must be a boolean when provided.');
            }

            $read = $payload['read'];
        }

        $operationId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->messageService->sendMessage(new PatchMessageCommand(
            operationId: $operationId,
            actorUserId: $loggedInUser->getId(),
            messageId: $messageId,
            content: $content,
            read: $read,
        ));

        return new JsonResponse([
            'operationId' => $operationId,
            'id' => $messageId,
        ], JsonResponse::HTTP_ACCEPTED);
    }
}
