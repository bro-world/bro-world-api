<?php

declare(strict_types=1);

namespace App\General\Application\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheInvalidationService
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly CacheKeyConventionService $cacheKeyConventionService,
    ) {
    }

    public function invalidateApplicationListCaches(?string $applicationSlug = null): void
    {
        if ($this->cache instanceof TagAwareCacheInterface) {
            $tags = [$this->cacheKeyConventionService->applicationListTag()];
            if ($applicationSlug !== null && $applicationSlug !== '') {
                $tags[] = $this->cacheKeyConventionService->recruitJobListTag($applicationSlug);
            }

            $this->cache->invalidateTags($tags);
        }

        $this->cache->delete($this->cacheKeyConventionService->buildApplicationListKey(null, 1, 20, [
            'title' => '',
            'description' => '',
            'platformName' => '',
            'platformKey' => '',
        ]));

        if ($applicationSlug !== null && $applicationSlug !== '') {
            $this->invalidateRecruitJobListCaches($applicationSlug);
        }
    }

    public function invalidateRecruitJobListCaches(string $applicationSlug): void
    {
        if ($this->cache instanceof TagAwareCacheInterface) {
            $this->cache->invalidateTags([$this->cacheKeyConventionService->recruitJobListTag($applicationSlug)]);
        }

        $this->cache->delete($this->cacheKeyConventionService->buildRecruitJobPublicListKey($applicationSlug, null, 1, 20, [
            'company' => '',
            'salaryMin' => 0,
            'salaryMax' => 0,
            'contractType' => '',
            'workMode' => '',
            'schedule' => '',
            'postedAtLabel' => '',
            'location' => '',
            'q' => '',
        ]));
    }
}
