<?php

declare(strict_types=1);

namespace App\Crm\Infrastructure\Repository;

use App\Crm\Domain\Entity\Contact as Entity;
use App\General\Infrastructure\Repository\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

class ContactRepository extends BaseRepository
{
    protected static string $entityName = Entity::class;
    protected static array $searchColumns = ['id'];

    public function __construct(protected ManagerRegistry $managerRegistry) {}

    /** @return list<Entity> */
    public function findScoped(string $crmId, int $limit = 200, int $offset = 0): array
    {
        return $this->createQueryBuilder('contact')
            ->andWhere('contact.crm = :crmId')
            ->setParameter('crmId', $crmId, UuidBinaryOrderedTimeType::NAME)
            ->orderBy('contact.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->getResult();
    }

    public function countByCrm(string $crmId): int
    {
        return (int)$this->createQueryBuilder('contact')
            ->select('COUNT(contact.id)')
            ->andWhere('contact.crm = :crmId')
            ->setParameter('crmId', $crmId, UuidBinaryOrderedTimeType::NAME)
            ->getQuery()->getSingleScalarResult();
    }
}
