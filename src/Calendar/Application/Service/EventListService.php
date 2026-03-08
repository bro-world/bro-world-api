<?php

declare(strict_types=1);

namespace App\Calendar\Application\Service;

use App\Calendar\Domain\Entity\Event;
use App\Calendar\Domain\Repository\Interfaces\EventRepositoryInterface;
use App\User\Domain\Entity\User;

use function array_map;

final class EventListService
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByUser(User $user): array
    {
        return $this->normalizeEvents($this->eventRepository->findByUser($user));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByApplicationSlug(string $applicationSlug): array
    {
        return $this->normalizeEvents($this->eventRepository->findByApplicationSlug($applicationSlug));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByApplicationSlugAndUser(string $applicationSlug, User $user): array
    {
        return $this->normalizeEvents($this->eventRepository->findByApplicationSlugAndUser($applicationSlug, $user));
    }

    /**
     * @param array<int, Event> $events
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeEvents(array $events): array
    {
        return array_map(static function (Event $event): array {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'startAt' => $event->getStartAt()->format(DATE_ATOM),
                'endAt' => $event->getEndAt()->format(DATE_ATOM),
                'status' => $event->getStatusValue(),
                'visibility' => $event->getVisibilityValue(),
                'location' => $event->getLocation(),
                'isAllDay' => $event->isAllDay(),
                'timezone' => $event->getTimezone(),
                'isCancelled' => $event->isCancelled(),
                'url' => $event->getUrl(),
                'color' => $event->getColor(),
                'backgroundColor' => $event->getBackgroundColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
                'organizerName' => $event->getOrganizerName(),
                'organizerEmail' => $event->getOrganizerEmail(),
                'attendees' => $event->getAttendees(),
                'rrule' => $event->getRrule(),
                'recurrenceExceptions' => $event->getRecurrenceExceptions(),
                'recurrenceEndAt' => $event->getRecurrenceEndAt()?->format(DATE_ATOM),
                'recurrenceCount' => $event->getRecurrenceCount(),
                'reminders' => $event->getReminders(),
                'metadata' => $event->getMetadata(),
                'calendarId' => $event->getCalendar()?->getId(),
                'applicationSlug' => $event->getCalendar()?->getApplication()?->getSlug(),
                'userId' => $event->getUser()?->getId(),
            ];
        }, $events);
    }
}
