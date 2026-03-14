<?php

declare(strict_types=1);

namespace App\Crm\Transport\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateBillingRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $label = null;

    #[Assert\NotNull]
    public ?float $amount = null;

    #[Assert\Length(min: 3, max: 3)]
    public ?string $currency = null;

    #[Assert\Length(max: 30)]
    public ?string $status = null;

    public ?string $dueAt = null;

    public ?string $companyId = null;

    public static function fromArray(array $payload): self
    {
        $r = new self();
        $r->label = isset($payload['label']) ? (string)$payload['label'] : null;
        $r->amount = isset($payload['amount']) ? (float)$payload['amount'] : null;
        $r->currency = isset($payload['currency']) ? (string)$payload['currency'] : null;
        $r->status = isset($payload['status']) ? (string)$payload['status'] : null;
        $r->dueAt = isset($payload['dueAt']) ? (string)$payload['dueAt'] : null;
        $r->companyId = isset($payload['companyId']) ? (string)$payload['companyId'] : null;

        return $r;
    }
}
