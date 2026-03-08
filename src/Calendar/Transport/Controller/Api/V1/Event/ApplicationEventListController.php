<?php

declare(strict_types=1);

namespace App\Calendar\Transport\Controller\Api\V1\Event;

use App\Calendar\Application\Service\EventListService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Calendar Event')]
class ApplicationEventListController
{
    public function __construct(private readonly EventListService $eventListService)
    {
    }

    #[Route(path: '/v1/calendar/applications/{applicationSlug}/events', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug): JsonResponse
    {
        return new JsonResponse($this->eventListService->getByApplicationSlug($applicationSlug));
    }
}
