<?php

declare(strict_types=1);

namespace App\General\Application\MessageHandler;

use App\General\Application\Message\EntityCreated;
use App\General\Application\Message\EntityDeleted;
use App\General\Application\Message\EntityMutationMessage;
use App\General\Application\Message\EntityPatched;
use App\General\Application\Service\CacheInvalidationService;
use App\General\Application\Service\CriticalViewWarmer;
use App\General\Application\Service\MessageIdempotenceGuard;
use App\General\Domain\Service\Interfaces\ElasticsearchServiceInterface;
use App\Platform\Application\Projection\ApplicationProjection;
use App\Platform\Infrastructure\Repository\ApplicationRepository;
use App\Recruit\Application\Projection\RecruitJobProjection;
use App\Recruit\Infrastructure\Repository\JobRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function array_map;

#[AsMessageHandler]
final readonly class EntityProjectionHandler
{
    private const string PLATFORM_APPLICATION = 'platform_application';
    private const string RECRUIT_JOB = 'recruit_job';

    public function __construct(
        private ApplicationRepository $applicationRepository,
        private JobRepository $jobRepository,
        private CacheInvalidationService $cacheInvalidationService,
        private CriticalViewWarmer $criticalViewWarmer,
        private ElasticsearchServiceInterface $elasticsearchService,
        private MessageIdempotenceGuard $messageIdempotenceGuard,
    ) {
    }

    public function __invoke(EntityCreated|EntityPatched|EntityDeleted $message): void
    {
        if ($this->messageIdempotenceGuard->shouldProcess($message->eventId) === false) {
            return;
        }

        if ($message->entityType === self::PLATFORM_APPLICATION) {
            $this->projectPlatformApplication($message);

            return;
        }

        if ($message->entityType === self::RECRUIT_JOB) {
            $this->projectRecruitJob($message);
        }
    }

    private function projectPlatformApplication(EntityMutationMessage $message): void
    {
        if ($message instanceof EntityDeleted) {
            $this->elasticsearchService->delete(ApplicationProjection::INDEX_NAME, $message->entityId);
            $this->cacheInvalidationService->invalidateApplicationListCaches();
            $this->criticalViewWarmer->warmApplicationList();

            return;
        }

        $application = $this->applicationRepository->find($message->entityId);
        if ($application === null) {
            return;
        }

        $this->elasticsearchService->index(ApplicationProjection::INDEX_NAME, $application->getId(), [
            'id' => $application->getId(),
            'title' => $application->getTitle(),
            'description' => $application->getDescription(),
            'slug' => $application->getSlug(),
            'platformName' => $application->getPlatform()?->getName() ?? '',
            'platformKey' => $application->getPlatform()?->getPlatformKeyValue() ?? '',
            'status' => $application->getStatus()->value,
            'private' => $application->isPrivate(),
            'updatedAt' => $application->getUpdatedAt()?->format(DATE_ATOM),
        ]);

        $this->cacheInvalidationService->invalidateApplicationListCaches();
        $this->criticalViewWarmer->warmApplicationList();
    }

    private function projectRecruitJob(EntityMutationMessage $message): void
    {
        $applicationSlug = (string) ($message->context['applicationSlug'] ?? '');

        if ($message instanceof EntityDeleted) {
            $this->elasticsearchService->delete(RecruitJobProjection::INDEX_NAME, $message->entityId);
            if ($applicationSlug !== '') {
                $this->cacheInvalidationService->invalidateRecruitJobListCaches($applicationSlug);
                $this->criticalViewWarmer->warmRecruitJobList($applicationSlug);
            }

            return;
        }

        $job = $this->jobRepository->find($message->entityId);
        if ($job === null) {
            return;
        }

        $applicationSlug = $job->getRecruit()?->getApplication()?->getSlug() ?? $applicationSlug;

        $this->elasticsearchService->index(RecruitJobProjection::INDEX_NAME, $job->getId(), [
            'id' => $job->getId(),
            'slug' => $job->getSlug(),
            'title' => $job->getTitle(),
            'summary' => $job->getSummary(),
            'location' => $job->getLocation(),
            'contractType' => $job->getContractTypeValue(),
            'workMode' => $job->getWorkModeValue(),
            'schedule' => $job->getScheduleValue(),
            'tags' => array_map(static fn ($tag): string => $tag->getLabel(), $job->getTags()->toArray()),
            'applicationSlug' => $applicationSlug,
            'updatedAt' => $job->getUpdatedAt()?->format(DATE_ATOM),
        ]);

        if ($applicationSlug !== '') {
            $this->cacheInvalidationService->invalidateRecruitJobListCaches($applicationSlug);
            $this->criticalViewWarmer->warmRecruitJobList($applicationSlug);
        }
    }
}
