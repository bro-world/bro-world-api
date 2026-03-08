<?php

declare(strict_types=1);

namespace App\Calendar\Transport\Controller\Api\V1\Event;

use App\Calendar\Domain\Entity\Event;
use App\Calendar\Domain\Enum\EventStatus;
use App\Calendar\Infrastructure\Repository\EventRepository;
use App\User\Domain\Entity\User;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[OA\Tag(name: 'Calendar Event')]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
class UserEventMutationController
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    #[Route(path: '/v1/calendar/private/events', methods: [Request::METHOD_POST])]
    public function create(Request $request, User $loggedInUser): JsonResponse
    {
        $payload = $request->toArray();

        $event = (new Event())
            ->setTitle($this->requireString($payload, 'title'))
            ->setDescription((string) ($payload['description'] ?? ''))
            ->setStartAt($this->requireDate($payload, 'startAt'))
            ->setEndAt($this->requireDate($payload, 'endAt'))
            ->setStatus((string) ($payload['status'] ?? EventStatus::CONFIRMED->value))
            ->setUser($loggedInUser);

        if (isset($payload['location']) && is_string($payload['location'])) {
            $event->setLocation($payload['location']);
        }

        $this->eventRepository->save($event);

        return new JsonResponse(['id' => $event->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route(path: '/v1/calendar/private/events/{eventId}', methods: [Request::METHOD_PATCH])]
    public function patch(string $eventId, Request $request, User $loggedInUser): JsonResponse
    {
        $event = $this->findOwnedEvent($eventId, $loggedInUser);
        $payload = $request->toArray();

        if (isset($payload['title']) && is_string($payload['title'])) {
            $event->setTitle($payload['title']);
        }
        if (isset($payload['description']) && is_string($payload['description'])) {
            $event->setDescription($payload['description']);
        }
        if (isset($payload['startAt']) && is_string($payload['startAt'])) {
            $event->setStartAt($this->parseDate($payload['startAt'], 'startAt'));
        }
        if (isset($payload['endAt']) && is_string($payload['endAt'])) {
            $event->setEndAt($this->parseDate($payload['endAt'], 'endAt'));
        }

        $this->eventRepository->save($event);

        return new JsonResponse(['id' => $event->getId()]);
    }

    #[Route(path: '/v1/calendar/private/events/{eventId}', methods: [Request::METHOD_DELETE])]
    public function delete(string $eventId, User $loggedInUser): JsonResponse
    {
        $event = $this->findOwnedEvent($eventId, $loggedInUser);
        $this->eventRepository->remove($event);

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route(path: '/v1/calendar/private/events/{eventId}/cancel', methods: [Request::METHOD_POST])]
    public function cancel(string $eventId, User $loggedInUser): JsonResponse
    {
        $event = $this->findOwnedEvent($eventId, $loggedInUser);
        $event->setIsCancelled(true)->setStatus(EventStatus::CANCELLED);
        $this->eventRepository->save($event);

        return new JsonResponse(['id' => $event->getId(), 'status' => $event->getStatusValue(), 'isCancelled' => $event->isCancelled()]);
    }

    private function findOwnedEvent(string $eventId, User $loggedInUser): Event
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event instanceof Event || $event->getUser()?->getId() !== $loggedInUser->getId()) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Event not found.');
        }

        return $event;
    }

    /** @param array<string, mixed> $payload */
    private function requireString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;
        if (!is_string($value) || $value === '') {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "' . $field . '" is required.');
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function requireDate(array $payload, string $field): DateTimeImmutable
    {
        $value = $payload[$field] ?? null;
        if (!is_string($value)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "' . $field . '" must be a valid date string.');
        }

        return $this->parseDate($value, $field);
    }

    private function parseDate(string $value, string $field): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Throwable) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "' . $field . '" must be a valid date string.');
        }
    }
}
