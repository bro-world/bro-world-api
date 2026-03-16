<?php

declare(strict_types=1);

namespace App\Tests\Unit\Crm\Application\Service;

use App\Crm\Application\Service\CrmApiNormalizer;
use App\Crm\Application\Service\CrmApplicationScopeResolver;
use App\Crm\Application\Service\TaskListService;
use App\Crm\Domain\Entity\Crm;
use App\Crm\Domain\Entity\Task;
use App\Crm\Infrastructure\Repository\TaskRepository;
use App\General\Application\Service\CacheKeyConventionService;
use App\General\Domain\Service\Interfaces\ElasticsearchServiceInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class TaskListServiceTest extends TestCase
{
    public function testSearchIdsFromElasticPaginatesWhenMoreThanTwoHundredHits(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $cache = $this->createMock(CacheInterface::class);
        $elastic = $this->createMock(ElasticsearchServiceInterface::class);
        $scopeResolver = $this->createMock(CrmApplicationScopeResolver::class);
        $normalizer = $this->createMock(CrmApiNormalizer::class);
        $logger = $this->createMock(LoggerInterface::class);

        $firstHits = [];
        for ($i = 1; $i <= 200; ++$i) {
            $firstHits[] = [
                '_source' => ['id' => sprintf('00000000-0000-0000-0000-%012d', $i)],
                'sort' => [$i],
            ];
        }

        $secondHits = [];
        for ($i = 201; $i <= 250; ++$i) {
            $secondHits[] = [
                '_source' => ['id' => sprintf('00000000-0000-0000-0000-%012d', $i)],
                'sort' => [$i],
            ];
        }

        $elastic
            ->expects(self::exactly(2))
            ->method('search')
            ->with(
                self::anything(),
                self::callback(static function (array $body): bool {
                    return isset($body['sort'], $body['track_total_hits'])
                        && $body['sort'] === [['_doc' => 'asc']]
                        && $body['track_total_hits'] === true;
                }),
                0,
                200,
            )
            ->willReturnOnConsecutiveCalls(
                ['hits' => ['hits' => $firstHits]],
                ['hits' => ['hits' => $secondHits]],
            );

        $service = new TaskListService(
            $taskRepository,
            $cache,
            $elastic,
            new CacheKeyConventionService(),
            $scopeResolver,
            $normalizer,
            $logger,
        );

        /** @var list<string> $ids */
        $ids = $this->invokePrivate($service, 'searchIdsFromElastic', [['q' => 'important', 'title' => '', 'status' => '', 'priority' => '']]);

        self::assertCount(250, $ids);
    }

    public function testGetListReturnsEmptyPaginationWhenElasticReturnsNoHit(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $cache = $this->createMock(CacheInterface::class);
        $elastic = $this->createMock(ElasticsearchServiceInterface::class);
        $scopeResolver = $this->createMock(CrmApplicationScopeResolver::class);
        $normalizer = $this->createMock(CrmApiNormalizer::class);
        $logger = $this->createMock(LoggerInterface::class);

        $crm = $this->createMock(Crm::class);
        $crm->method('getId')->willReturn('00000000-0000-0000-0000-000000000111');

        $scopeResolver->method('resolveOrFail')->willReturn($crm);
        $elastic->method('search')->willReturn(['hits' => ['hits' => []]]);

        $cache->method('get')->willReturnCallback(static function (string $key, callable $callback): array {
            $item = new class() implements ItemInterface {
                public function getKey(): string { return 'key'; }
                public function get(): mixed { return null; }
                public function isHit(): bool { return false; }
                public function set(mixed $value): static { return $this; }
                public function expiresAt(?\DateTimeInterface $expiration): static { return $this; }
                public function expiresAfter(\DateInterval|int|null $time): static { return $this; }
                public function tag(string|iterable $tags): static { return $this; }
                public function getMetadata(): array { return []; }
            };

            return $callback($item);
        });

        $service = new TaskListService(
            $taskRepository,
            $cache,
            $elastic,
            new CacheKeyConventionService(),
            $scopeResolver,
            $normalizer,
            $logger,
        );

        $request = new Request(['q' => 'x', 'page' => 1, 'limit' => 20], [], ['applicationSlug' => 'crm-app']);
        $result = $service->getList($request);

        self::assertSame([], $result['items']);
        self::assertSame(0, $result['pagination']['totalItems']);
    }

    public function testGetListKeepsItemsAndTotalItemsConsistentWithCombinedFiltersAndQ(): void
    {
        $taskRepository = $this->createMock(TaskRepository::class);
        $cache = $this->createMock(CacheInterface::class);
        $elastic = $this->createMock(ElasticsearchServiceInterface::class);
        $scopeResolver = $this->createMock(CrmApplicationScopeResolver::class);
        $normalizer = $this->createMock(CrmApiNormalizer::class);
        $logger = $this->createMock(LoggerInterface::class);

        $crm = $this->createMock(Crm::class);
        $crm->method('getId')->willReturn('00000000-0000-0000-0000-000000000222');
        $scopeResolver->method('resolveOrFail')->willReturn($crm);

        $elastic->method('search')->willReturn([
            'hits' => [
                'hits' => [
                    ['_source' => ['id' => '2eea8e20-8f35-11ee-b9d1-0242ac120002'], 'sort' => [1]],
                    ['_source' => ['id' => '3ddbf7e0-8f35-11ee-b9d1-0242ac120002'], 'sort' => [2]],
                ],
            ],
        ]);

        $idsQb = $this->mockQueryBuilderForIds([
            ['id' => '2eea8e20-8f35-11ee-b9d1-0242ac120002'],
            ['id' => '3ddbf7e0-8f35-11ee-b9d1-0242ac120002'],
        ]);
        $tasksQb = $this->mockQueryBuilderForTasks();
        $countQb = $this->mockQueryBuilderForCount(2);

        $taskRepository->expects(self::exactly(3))
            ->method('createQueryBuilder')
            ->with('task')
            ->willReturnOnConsecutiveCalls($idsQb, $tasksQb, $countQb);

        $taskA = $this->createMock(Task::class);
        $taskA->method('getId')->willReturn('2eea8e20-8f35-11ee-b9d1-0242ac120002');
        $taskB = $this->createMock(Task::class);
        $taskB->method('getId')->willReturn('3ddbf7e0-8f35-11ee-b9d1-0242ac120002');

        $tasksQuery = $this->createMock(AbstractQuery::class);
        $tasksQuery->method('getResult')->willReturn([$taskA, $taskB]);
        $tasksQb->method('getQuery')->willReturn($tasksQuery);

        $normalizer->method('normalizeTask')->willReturnCallback(static fn (Task $task): array => ['id' => $task->getId()]);

        $cache->method('get')->willReturnCallback(static function (string $key, callable $callback): array {
            $item = new class() implements ItemInterface {
                public function getKey(): string { return 'key'; }
                public function get(): mixed { return null; }
                public function isHit(): bool { return false; }
                public function set(mixed $value): static { return $this; }
                public function expiresAt(?\DateTimeInterface $expiration): static { return $this; }
                public function expiresAfter(\DateInterval|int|null $time): static { return $this; }
                public function tag(string|iterable $tags): static { return $this; }
                public function getMetadata(): array { return []; }
            };

            return $callback($item);
        });

        $service = new TaskListService(
            $taskRepository,
            $cache,
            $elastic,
            new CacheKeyConventionService(),
            $scopeResolver,
            $normalizer,
            $logger,
        );

        $request = new Request(
            ['q' => 'deploy', 'title' => 'Sprint', 'status' => 'open', 'priority' => 'high', 'page' => 1, 'limit' => 20],
            [],
            ['applicationSlug' => 'crm-app']
        );

        $result = $service->getList($request);

        self::assertCount(2, $result['items']);
        self::assertSame(2, $result['pagination']['totalItems']);
        self::assertSame(1, $result['pagination']['totalPages']);
    }

    /**
     * @param list<array{id:string}> $rows
     */
    private function mockQueryBuilderForIds(array $rows): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getArrayResult')->willReturn($rows);

        $qb->method('select')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('setFirstResult')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }

    private function mockQueryBuilderForTasks(): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('distinct')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('addSelect')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        return $qb;
    }

    private function mockQueryBuilderForCount(int $count): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);
        $query->method('getSingleScalarResult')->willReturn((string) $count);

        $qb->method('select')->willReturnSelf();
        $qb->method('leftJoin')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function invokePrivate(object $service, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionClass($service);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($service, $arguments);
    }
}
