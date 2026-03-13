<?php

declare(strict_types=1);

namespace App\Crm\Infrastructure\Repository;

use App\Crm\Domain\Entity\Sprint as Entity;
use App\General\Infrastructure\Repository\BaseRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find(string $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null, ?string $entityManagerName = null)
 * @method Entity[] findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?string $entityManagerName = null)
 */
class SprintRepository extends BaseRepository
{
    protected static string $entityName = Entity::class;

    protected static array $searchColumns = [
        'id',
    ];

    public function __construct(
        protected ManagerRegistry $managerRegistry
    ) {
    }

    public function findOneScopedById(string $id, string $crmId): ?Entity
    {
        $entity = $this->createQueryBuilder('sprint')
            ->leftJoin('sprint.project', 'project')
            ->leftJoin('project.company', 'company')
            ->andWhere('sprint.id = :id')
            ->andWhere('company.crm = :crmId')
            ->setParameter('id', $id)
            ->setParameter('crmId', $crmId)
            ->getQuery()
            ->getOneOrNullResult();

        return $entity instanceof Entity ? $entity : null;
    }

    /** @return list<Entity> */
    public function findScoped(string $crmId, int $limit = 200, int $offset = 0): array
    {
        return $this->createQueryBuilder('sprint')
            ->leftJoin('sprint.project', 'project')
            ->leftJoin('project.company', 'company')
            ->andWhere('company.crm = :crmId')
            ->setParameter('crmId', $crmId)
            ->orderBy('sprint.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

}
