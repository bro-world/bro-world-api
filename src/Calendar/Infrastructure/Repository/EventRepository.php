<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Repository;

use App\Calendar\Domain\Entity\Event as Entity;
use App\Calendar\Domain\Repository\Interfaces\EventRepositoryInterface;
use App\General\Infrastructure\Repository\BaseRepository;
use App\User\Domain\Entity\User;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

/**
 * @method Entity|null find(string $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null, ?string $entityManagerName = null)
 * @method Entity[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?string $entityManagerName = null)
 */
class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    protected static string $entityName = Entity::class;

    protected static array $searchColumns = [
        'id',
        'title',
        'description',
        'status',
        'visibility',
    ];

    public function __construct(protected ManagerRegistry $managerRegistry)
    {
    }

    public function findByUser(User $user): array
    {
        return $this->createBaseQueryBuilder()
            ->andWhere('event.user = :user OR calendar.user = :user')
            ->setParameter('user', $user->getId(), UuidBinaryOrderedTimeType::NAME)
            ->orderBy('event.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByApplicationSlug(string $applicationSlug): array
    {
        return $this->createBaseQueryBuilder()
            ->innerJoin('calendar.application', 'application')
            ->andWhere('application.slug = :applicationSlug')
            ->setParameter('applicationSlug', $applicationSlug)
            ->orderBy('event.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByApplicationSlugAndUser(string $applicationSlug, User $user): array
    {
        return $this->createBaseQueryBuilder()
            ->innerJoin('calendar.application', 'application')
            ->andWhere('application.slug = :applicationSlug')
            ->andWhere('event.user = :user OR calendar.user = :user')
            ->setParameter('applicationSlug', $applicationSlug)
            ->setParameter('user', $user->getId(), UuidBinaryOrderedTimeType::NAME)
            ->orderBy('event.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->addSelect('calendar')
            ->leftJoin('event.calendar', 'calendar')
            ->distinct();
    }
}
