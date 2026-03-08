<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Repository\Interfaces;

use App\Calendar\Domain\Entity\Event;
use App\User\Domain\Entity\User;

interface EventRepositoryInterface
{
    /**
     * @return array<int, Event>
     */
    public function findByUser(User $user): array;

    /**
     * @return array<int, Event>
     */
    public function findByApplicationSlug(string $applicationSlug): array;

    /**
     * @return array<int, Event>
     */
    public function findByApplicationSlugAndUser(string $applicationSlug, User $user): array;
}
