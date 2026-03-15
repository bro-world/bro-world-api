<?php

declare(strict_types=1);

namespace App\Crm\Transport\Controller\Api\V1\Contact;

use App\Crm\Domain\Entity\Contact;
use App\Role\Domain\Enum\Role;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Crm')]
#[IsGranted(Role::CRM_VIEWER->value)]
final readonly class GetContactController
{
    #[Route('/v1/crm/applications/{applicationSlug}/contacts/{contact}', methods: [Request::METHOD_GET])]
    public function __invoke(string $applicationSlug, Contact $contact): JsonResponse
    {
        return new JsonResponse([
            'id' => $contact->getId(),
            'companyId' => $contact->getCompany()?->getId(),
            'firstName' => $contact->getFirstName(),
            'lastName' => $contact->getLastName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'jobTitle' => $contact->getJobTitle(),
            'city' => $contact->getCity(),
            'score' => $contact->getScore(),
        ]);
    }
}
