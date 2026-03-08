<?php

declare(strict_types=1);

namespace App\General\Application\Service;

use function json_encode;
use function md5;

class CacheKeyConventionService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function buildApplicationListKey(?string $userId, int $page, int $limit, array $filters): string
    {
        return 'application_list_' . md5((string) json_encode([
            'userId' => $userId,
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildRecruitJobPublicListKey(string $applicationSlug, ?string $userId, int $page, int $limit, array $filters): string
    {
        return 'recruit_job_public_' . md5((string) json_encode([
            'applicationSlug' => $applicationSlug,
            'userId' => $userId,
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));
    }


    /**
     * @param array<string, mixed> $filters
     */
    public function buildShopProductListKey(int $page, int $limit, array $filters): string
    {
        return 'shop_product_list_' . md5((string) json_encode([
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildCrmTaskListKey(int $page, int $limit, array $filters): string
    {
        return 'crm_task_list_' . md5((string) json_encode([
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function buildSchoolExamListKey(int $page, int $limit, array $filters): string
    {
        return 'school_exam_list_' . md5((string) json_encode([
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));
    }

    public function applicationListTag(): string
    {
        return 'cache:application:list';
    }

    public function recruitJobListTag(string $applicationSlug): string
    {
        return 'cache:recruit:job:list:' . $applicationSlug;
    }

    public function shopProductListTag(): string
    {
        return 'cache:shop:product:list';
    }

    public function crmTaskListTag(): string
    {
        return 'cache:crm:task:list';
    }

    public function schoolExamListTag(): string
    {
        return 'cache:school:exam:list';
    }
}
