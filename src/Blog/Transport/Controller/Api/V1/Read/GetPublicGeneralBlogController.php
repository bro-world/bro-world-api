<?php

declare(strict_types=1);

namespace App\Blog\Transport\Controller\Api\V1\Read;

use App\Blog\Application\Service\BlogReadService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Blog')]
final readonly class GetPublicGeneralBlogController
{
    public function __construct(
        private BlogReadService $blogReadService
    ) {
    }

    #[Route('/v1/blogs/general/public', methods: [Request::METHOD_GET])]
    #[OA\Get(security: [])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->blogReadService->getGeneralBlogWithTree());
    }
}
