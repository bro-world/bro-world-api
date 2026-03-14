<?php

declare(strict_types=1);

namespace App\Crm\Application\Message;

use App\General\Domain\Message\Interfaces\MessageHighInterface;

final readonly class CreateBillingCommand implements MessageHighInterface
{
    public function __construct(
        public string $id,
        public string $companyId,
        public string $label,
        public float $amount,
        public string $currency,
        public string $status,
        public ?string $dueAt,
        public string $applicationSlug,
        public string $crmId,
    ) {
    }
}
