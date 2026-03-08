<?php

declare(strict_types=1);

namespace App\General\Application\Service;

use App\Platform\Application\Service\ApplicationListService;
use App\Recruit\Application\Service\JobPublicListService;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class CriticalViewWarmer
{
    public function __construct(
        private readonly ApplicationListService $applicationListService,
        private readonly JobPublicListService $jobPublicListService,
    ) {
    }

    public function warmApplicationList(): void
    {
        try {
            $this->applicationListService->getPublicList(new Request());
        } catch (Throwable) {
        }
    }

    public function warmRecruitJobList(string $applicationSlug): void
    {
        try {
            $this->jobPublicListService->getList(new Request(), $applicationSlug);
        } catch (Throwable) {
        }
    }
}
