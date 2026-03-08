<?php

declare(strict_types=1);

namespace App\Chat\Transport\Controller\Api\V1\Conversation;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ConversationJsonResponseFactory
{
    /**
     * @param array<int, array<string, mixed>> $payload
     */
    public static function create(array $payload): JsonResponse
    {
        $response = new JsonResponse($payload);
        $response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_INVALID_UTF8_SUBSTITUTE);

        return $response;
    }
}
