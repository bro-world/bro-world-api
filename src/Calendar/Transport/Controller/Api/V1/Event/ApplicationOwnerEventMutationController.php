<?php

declare(strict_types=1);

namespace App\Calendar\Transport\Controller\Api\V1\Event;

use App\Calendar\Domain\Entity\Event;
use App\Calendar\Domain\Enum\EventStatus;
use App\Calendar\Infrastructure\Repository\CalendarRepository;
use App\Calendar\Infrastructure\Repository\EventRepository;
use App\Platform\Domain\Entity\Application;
use App\Platform\Infrastructure\Repository\ApplicationRepository;
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
class ApplicationOwnerEventMutationController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ApplicationRepository $applicationRepository,
        private readonly CalendarRepository $calendarRepository,
    ) {
    }

    #[Route(path: '/v1/calendar/private/applications/{applicationSlug}/events', methods: [Request::METHOD_POST])]
    public function create(string $applicationSlug, Request $request, User $loggedInUser): JsonResponse
    {
        $application = $this->findOwnedApplication($applicationSlug, $loggedInUser);
        $calendar = $this->calendarRepository->findOneByApplication($application);
        if ($calendar === null) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Application has no calendar.');
        }

        $payload = $request->toArray();
        $event = (new Event())
            ->setTitle($this->requireString($payload, 'title'))
            ->setDescription((string) ($payload['description'] ?? ''))
            ->setStartAt($this->requireDate($payload, 'startAt'))
            ->setEndAt($this->requireDate($payload, 'endAt'))
            ->setStatus((string) ($payload['status'] ?? EventStatus::CONFIRMED->value))
            ->setCalendar($calendar)
            ->setUser($loggedInUser);

        $this->eventRepository->save($event);

        return new JsonResponse(['id' => $event->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route(path: '/v1/calendar/private/applications/{applicationSlug}/events/{eventId}', methods: [Request::METHOD_PATCH])]
    public function patch(string $applicationSlug, string $eventId, Request $request, User $loggedInUser): JsonResponse
    {
        $application = $this->findOwnedApplication($applicationSlug, $loggedInUser);
        $event = $this->findOwnedApplicationEvent($eventId, $application, $loggedInUser);
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

    #[Route(path: '/v1/calendar/private/applications/{applicationSlug}/events/{eventId}', methods: [Request::METHOD_DELETE])]
    public function delete(string $applicationSlug, string $eventId, User $loggedInUser): JsonResponse
    {
        $application = $this->findOwnedApplication($applicationSlug, $loggedInUser);
        $event = $this->findOwnedApplicationEvent($eventId, $application, $loggedInUser);
        $this->eventRepository->remove($event);

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route(path: '/v1/calendar/private/applications/{applicationSlug}/events/{eventId}/cancel', methods: [Request::METHOD_POST])]
    public function cancel(string $applicationSlug, string $eventId, User $loggedInUser): JsonResponse
    {
        $application = $this->findOwnedApplication($applicationSlug, $loggedInUser);
        $event = $this->findOwnedApplicationEvent($eventId, $application, $loggedInUser);
        $event->setIsCancelled(true)->setStatus(EventStatus::CANCELLED);
        $this->eventRepository->save($event);

        return new JsonResponse(['id' => $event->getId(), 'status' => $event->getStatusValue(), 'isCancelled' => $event->isCancelled()]);
    }

    private function findOwnedApplication(string $slug, User $loggedInUser): Application
    {
        $application = $this->applicationRepository->findOneBy(['slug' => $slug]);
        if (!$application instanceof Application || $application->getUser()?->getId() !== $loggedInUser->getId()) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Application not found.');
        }

        return $application;
    }

    private function findOwnedApplicationEvent(string $eventId, Application $application, User $loggedInUser): Event
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event instanceof Event) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, 'Event not found.');
        }

        $calendar = $event->getCalendar();
        if ($calendar?->getApplication()?->getId() !== $application->getId() || $event->getUser()?->getId() !== $loggedInUser->getId()) {
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
