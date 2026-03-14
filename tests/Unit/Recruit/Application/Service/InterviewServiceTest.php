<?php

declare(strict_types=1);

namespace App\Tests\Unit\Recruit\Application\Service;

use App\Recruit\Application\Service\InterviewInvitationService;
use App\Recruit\Application\Service\InterviewService;
use App\Recruit\Domain\Entity\Applicant;
use App\Recruit\Domain\Entity\Application;
use App\Recruit\Domain\Entity\Interview;
use App\Recruit\Domain\Entity\Job;
use App\Recruit\Domain\Enum\ApplicationStatus;
use App\Recruit\Infrastructure\Repository\ApplicationRepository;
use App\Recruit\Infrastructure\Repository\InterviewRepository;
use App\User\Domain\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InterviewServiceTest extends TestCase
{
    public function testCreateRejectsClosedApplication(): void
    {
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn('u1');

        $application = $this->buildApplication($owner, ApplicationStatus::REJECTED);

        $applicationRepository = $this->createMock(ApplicationRepository::class);
        $applicationRepository->method('find')->willReturn($application);

        $interviewRepository = $this->createMock(InterviewRepository::class);
        $interviewRepository->expects(self::never())->method('save');

        $service = new InterviewService($applicationRepository, $interviewRepository, $this->createMock(InterviewInvitationService::class));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Cannot schedule or update interviews for applications with status REJECTED or HIRED.');

        $service->create('app-id', [
            'scheduledAt' => (new DateTimeImmutable('+1 day'))->format(DATE_ATOM),
            'durationMinutes' => 45,
            'mode' => 'visio',
            'locationOrUrl' => 'https://meet',
            'interviewerIds' => [],
        ], $owner);
    }

    public function testCreateRejectsPastDate(): void
    {
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn('u1');

        $application = $this->buildApplication($owner, ApplicationStatus::WAITING);

        $applicationRepository = $this->createMock(ApplicationRepository::class);
        $applicationRepository->method('find')->willReturn($application);

        $interviewRepository = $this->createMock(InterviewRepository::class);

        $service = new InterviewService($applicationRepository, $interviewRepository, $this->createMock(InterviewInvitationService::class));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Field "scheduledAt" must be in the future.');

        $service->create('app-id', [
            'scheduledAt' => (new DateTimeImmutable('-1 hour'))->format(DATE_ATOM),
            'durationMinutes' => 45,
            'mode' => 'visio',
            'locationOrUrl' => 'https://meet',
            'interviewerIds' => [],
        ], $owner);
    }

    public function testCreateRejectsInvalidDuration(): void
    {
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn('u1');

        $application = $this->buildApplication($owner, ApplicationStatus::WAITING);

        $applicationRepository = $this->createMock(ApplicationRepository::class);
        $applicationRepository->method('find')->willReturn($application);

        $interviewRepository = $this->createMock(InterviewRepository::class);

        $service = new InterviewService($applicationRepository, $interviewRepository, $this->createMock(InterviewInvitationService::class));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Field "durationMinutes" must be between 15 and 240.');

        $service->create('app-id', [
            'scheduledAt' => (new DateTimeImmutable('+1 day'))->format(DATE_ATOM),
            'durationMinutes' => 5,
            'mode' => 'visio',
            'locationOrUrl' => 'https://meet',
            'interviewerIds' => [],
        ], $owner);
    }

    public function testCreatePersistsInterview(): void
    {
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn('u1');

        $application = $this->buildApplication($owner, ApplicationStatus::WAITING);

        $applicationRepository = $this->createMock(ApplicationRepository::class);
        $applicationRepository->method('find')->willReturn($application);

        $interviewRepository = $this->createMock(InterviewRepository::class);
        $interviewRepository->expects(self::once())->method('save')->with(self::isInstanceOf(Interview::class));

        $invitationService = $this->createMock(InterviewInvitationService::class);
        $invitationService->expects(self::once())->method('sendInvitation')->with(self::isInstanceOf(Interview::class), false);

        $service = new InterviewService($applicationRepository, $interviewRepository, $invitationService);

        $interview = $service->create('app-id', [
            'scheduledAt' => (new DateTimeImmutable('+1 day'))->format(DATE_ATOM),
            'durationMinutes' => 30,
            'mode' => 'on-site',
            'locationOrUrl' => 'Paris HQ',
            'interviewerIds' => ['u-2'],
            'notes' => 'focus culture',
        ], $owner);

        self::assertSame(30, $interview->getDurationMinutes());
        self::assertSame('on-site', $interview->getMode()->value);
    }

    private function buildApplication(User $owner, ApplicationStatus $status): Application
    {
        $job = (new Job())->setOwner($owner)->setTitle('X')->ensureGeneratedSlug();

        return (new Application())
            ->setApplicant(new Applicant())
            ->setJob($job)
            ->setStatus($status);
    }
}
