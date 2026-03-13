<?php

declare(strict_types=1);

namespace App\Recruit\Application\Service;

use App\Recruit\Domain\Entity\Application;
use App\Recruit\Domain\Enum\ApplicationStatus;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function array_key_exists;
use function in_array;
use function is_string;
use function strtoupper;

readonly class ApplicationStatusTransitionService
{
    public function __construct(
        private ApplicationDiscussionBootstrapService $applicationDiscussionBootstrapService,
    ) {
    }

    public function applyStatusTransition(Application $application, mixed $status): void
    {
        if (!is_string($status)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "status" must be provided as a string.');
        }

        $newStatus = ApplicationStatus::tryFrom(strtoupper($status));
        if ($newStatus === null) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Field "status" must be one of: WAITING, IN_PROGRESS, DISCUSSION, INVITE_TO_INTERVIEW, INTERVIEW, ACCEPTED, REJECTED.');
        }

        $currentStatus = $application->getStatus();
        if ($newStatus !== $currentStatus && !$this->isAllowedTransition($currentStatus, $newStatus)) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Status transition is not allowed for this application.');
        }

        if ($newStatus === ApplicationStatus::DISCUSSION && $currentStatus !== ApplicationStatus::DISCUSSION) {
            try {
                $this->applicationDiscussionBootstrapService->bootstrap($application);
            } catch (DomainException $exception) {
                throw new HttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage(), $exception);
            }
        }

        $application->setStatus($newStatus);
    }

    private function isAllowedTransition(ApplicationStatus $from, ApplicationStatus $to): bool
    {
        $allowedTransitions = [
            ApplicationStatus::WAITING->value => [ApplicationStatus::IN_PROGRESS->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::IN_PROGRESS->value => [ApplicationStatus::DISCUSSION->value, ApplicationStatus::INVITE_TO_INTERVIEW->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::DISCUSSION->value => [ApplicationStatus::INVITE_TO_INTERVIEW->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::INVITE_TO_INTERVIEW->value => [ApplicationStatus::INTERVIEW->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::INTERVIEW->value => [ApplicationStatus::ACCEPTED->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::ACCEPTED->value => [],
            ApplicationStatus::REJECTED->value => [],
        ];

        if (!array_key_exists($from->value, $allowedTransitions)) {
            return false;
        }

        return in_array($to->value, $allowedTransitions[$from->value], true);
    }
}
