<?php

declare(strict_types=1);

namespace App\Crm\Application\MessageHandler;

use App\Crm\Application\Message\ProvisionProjectGithubResources;
use App\Crm\Application\Service\ProjectGithubProvisioningService;
use App\Crm\Infrastructure\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProvisionProjectGithubResourcesHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectGithubProvisioningService $projectGithubProvisioningService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ProvisionProjectGithubResources $message): void
    {
        $project = $this->projectRepository->find($message->projectId);
        if ($project === null) {
            return;
        }

        $repositoryName = $project->getCode() !== null && $project->getCode() !== '' ? $project->getCode() : $project->getName();
        $this->projectGithubProvisioningService->provision($project, $repositoryName);
        $this->entityManager->flush();
    }
}
