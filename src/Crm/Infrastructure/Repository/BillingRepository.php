<?php

declare(strict_types=1);

namespace App\Crm\Infrastructure\Repository;

use App\Crm\Domain\Entity\Billing as Entity;
use App\General\Infrastructure\Repository\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

class BillingRepository extends BaseRepository
{
    protected static string $entityName = Entity::class;
    protected static array $searchColumns = ['id'];

    public function __construct(protected ManagerRegistry $managerRegistry) {}

    /** @return list<Entity> */
    public function findByCrm(string $crmId, int $limit = 200, int $offset = 0): array
    {
        return $this->createQueryBuilder('billing')
            ->innerJoin('billing.company', 'company')
            ->innerJoin('company.crm', 'crm')
            ->andWhere('crm.id = :crmId')
            ->setParameter('crmId', $crmId, UuidBinaryOrderedTimeType::NAME)
            ->orderBy('billing.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->getResult();
    }

    public function countByCrm(string $crmId): int
    {
        return (int)$this->createQueryBuilder('billing')
            ->select('COUNT(billing.id)')
            ->innerJoin('billing.company', 'company')
            ->innerJoin('company.crm', 'crm')
            ->andWhere('crm.id = :crmId')
            ->setParameter('crmId', $crmId, UuidBinaryOrderedTimeType::NAME)
            ->getQuery()->getSingleScalarResult();
    }
}
