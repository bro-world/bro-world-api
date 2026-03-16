<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Contact;

use App\Crm\Application\Service\ContactReadService;
use App\Crm\Domain\Entity\Contact;
use App\Role\Domain\Enum\Role;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class GetContactController
{
    public function __construct(
        private ContactReadService $contactReadService,
    ) {
    }

    #[Route('/v1/crm/applications/{applicationSlug}/contacts/{contact}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Contact $contact): JsonResponse
    {
        $payload = $this->contactReadService->getDetail($applicationSlug, $contact->getId());
        if ($payload === null) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Contact not found for this CRM scope.');
        }

        return new JsonResponse($payload);
    }
}
