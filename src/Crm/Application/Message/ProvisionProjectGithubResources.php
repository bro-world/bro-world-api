<?php

declare(strict_types=1);

namespace App\Crm\Application\Message;

use App\General\Domain\Message\Interfaces\MessageLowInterface;

final readonly class ProvisionProjectGithubResources implements MessageLowInterface
{
    public function __construct(
        public string $projectId,
    ) {
    }
}
