<?php

declare(strict_types=1);

namespace App\Media\Transport\Controller\Api\V1;

use App\Media\Application\Service\MediaUploaderService;
use App\Media\Application\Service\MediaUploadValidationPolicy;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function array_map;
use function array_values;
use function is_array;

#[AsController]
#[OA\Tag(name: 'Media')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class MediaUploadController
{
    public function __construct(private readonly MediaUploaderService $mediaUploaderService)
    {
    }

    #[Route(path: '/v1/media/upload', methods: [Request::METHOD_POST])]
    #[OA\Post(summary: 'Upload de média générique (single ou multiple).')]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    new OA\Property(property: 'files', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                ],
            ),
        ),
    )]
    #[OA\Response(response: 201, description: 'Fichiers uploadés')]
    #[OA\Response(response: 400, description: 'Aucun fichier ou fichier invalide. Stratégie all-or-nothing: si un fichier est invalide, aucun n\'est enregistré.')]
    #[OA\Response(response: 401, description: 'Authentication required')]
    public function __invoke(Request $request): JsonResponse
    {
        $files = $this->extractFiles($request);
        if ([] === $files) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'No file found. Expected "file" or "files[]".');
        }

        $policy = new MediaUploadValidationPolicy(
            maxSizeInBytes: 10 * 1024 * 1024,
            allowedMimeTypes: [
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/pdf',
            ],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
        );

        $uploaded = $this->mediaUploaderService->upload($request, $files, '/uploads/media', $policy);

        return new JsonResponse([
            'files' => array_map(static fn (array $file): array => [
                'url' => $file['url'],
                'originalName' => $file['originalName'],
                'mimeType' => $file['mimeType'],
                'size' => $file['size'],
            ], $uploaded),
        ], Response::HTTP_CREATED);
    }

    /** @return list<UploadedFile> */
    private function extractFiles(Request $request): array
    {
        $files = [];

        $single = $request->files->get('file');
        if ($single instanceof UploadedFile) {
            $files[] = $single;
        }

        $multiple = $request->files->get('files');
        if (is_array($multiple)) {
            foreach ($multiple as $file) {
                if ($file instanceof UploadedFile) {
                    $files[] = $file;
                }
            }
        }

        return array_values($files);
    }
}
