<?php

declare(strict_types=1);

namespace App\Blog\Application\Service;

use App\Blog\Domain\Enum\BlogReactionType;
use App\Media\Application\Service\MediaUploadValidationPolicy;
use App\Media\Application\Service\MediaUploaderService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function sprintf;

final readonly class BlogMutationRequestService
{
    public function __construct(
        private MediaUploaderService $mediaUploaderService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function extractPayload(Request $request): array
    {
        $payload = (array) json_decode((string) $request->getContent(), true);

        if ($payload === []) {
            $payload = $request->request->all();
        }

        return $payload;
    }

    public function parseReactionType(string $reactionType): BlogReactionType
    {
        $parsed = BlogReactionType::tryFrom($reactionType);

        if ($parsed instanceof BlogReactionType) {
            return $parsed;
        }

        throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, sprintf('Unsupported reaction type "%s".', $reactionType));
    }

    public function resolveUploadedFileUrl(Request $request, string $fallbackUrl): string
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return $fallbackUrl;
        }

        $uploaded = $this->mediaUploaderService->upload(
            $request,
            [$file],
            '/uploads/blog',
            new MediaUploadValidationPolicy(
                maxSizeInBytes: 10 * 1024 * 1024,
                allowedMimeTypes: [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ],
                allowedExtensions: ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
            ),
        );

        return (string) ($uploaded[0]['url'] ?? $fallbackUrl);
    }
}
