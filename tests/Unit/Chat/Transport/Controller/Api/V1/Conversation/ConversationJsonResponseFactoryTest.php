<?php

declare(strict_types=1);

namespace App\Tests\Unit\Chat\Transport\Controller\Api\V1\Conversation;

use App\Chat\Transport\Controller\Api\V1\Conversation\ConversationJsonResponseFactory;
use PHPUnit\Framework\TestCase;

final class ConversationJsonResponseFactoryTest extends TestCase
{
    public function testCreateSubstitutesInvalidUtf8Bytes(): void
    {
        $response = ConversationJsonResponseFactory::create([
            [
                'content' => "Broken byte: \xB1",
            ],
        ]);

        $content = $response->getContent();

        self::assertNotFalse($content);
        self::assertStringContainsString('\\ufffd', $content);
        self::assertNotSame(0, $response->getEncodingOptions() & JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
