<?php

declare(strict_types=1);

namespace App\Tests\Unit\General\Infrastructure\Rest;

use App\General\Domain\Rest\UuidHelper;
use App\General\Infrastructure\Rest\RepositoryHelper;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class RepositoryHelperTest extends TestCase
{
    public function testInOperatorUsesBoundArrayParameter(): void
    {
        RepositoryHelper::resetParameterCount();

        $expr = new Expr();
        $queryBuilder = $this->createQueryBuilderMock($expr, $capturedParameters);

        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        $expression = RepositoryHelper::getExpression(
            $queryBuilder,
            $expr->andX(),
            [
                ['entity.id', 'in', [$uuid]],
            ]
        );

        self::assertStringContainsString('entity.id IN(?1)', (string)$expression);
        self::assertStringNotContainsString($uuid, (string)$expression);

        self::assertCount(1, $capturedParameters);
        self::assertSame(1, $capturedParameters[0]['key']);
        self::assertSame([UuidHelper::getBytes($uuid)], $capturedParameters[0]['value']);
        self::assertNull($capturedParameters[0]['type']);
    }

    public function testBetweenKeepsParameterCounterConsistency(): void
    {
        RepositoryHelper::resetParameterCount();

        $expr = new Expr();
        $queryBuilder = $this->createQueryBuilderMock($expr, $capturedParameters);

        $expression = RepositoryHelper::getExpression(
            $queryBuilder,
            $expr->andX(),
            [
                ['entity.createdAt', 'between', ['2024-01-01', '2024-12-31']],
                ['entity.status', 'eq', 'active'],
            ]
        );

        self::assertStringContainsString('entity.createdAt BETWEEN ?1 AND ?2', (string)$expression);
        self::assertStringContainsString('entity.status = ?3', (string)$expression);

        self::assertSame(1, $capturedParameters[0]['key']);
        self::assertSame(2, $capturedParameters[1]['key']);
        self::assertSame(3, $capturedParameters[2]['key']);
    }

    /**
     * @param array<int, array{key: int, value: mixed, type: mixed}> $capturedParameters
     */
    private function createQueryBuilderMock(Expr $expr, array &$capturedParameters): QueryBuilder
    {
        $capturedParameters = [];

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['expr', 'setParameter'])
            ->getMock();

        $queryBuilder->method('expr')->willReturn($expr);
        $queryBuilder->method('setParameter')->willReturnCallback(
            function (int $key, mixed $value, mixed $type = null) use (&$capturedParameters, $queryBuilder): QueryBuilder {
                $capturedParameters[] = [
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                ];

                return $queryBuilder;
            }
        );

        return $queryBuilder;
    }
}
