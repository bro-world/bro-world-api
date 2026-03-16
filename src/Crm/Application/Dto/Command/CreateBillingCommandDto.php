<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateBillingCommandDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $label = null;

    #[Assert\NotNull]
    #[Assert\Type(type: 'numeric')]
    public null|int|float|string $amount = null;

    #[Assert\Length(min: 3, max: 3)]
    public ?string $currency = null;

    #[Assert\Length(max: 30)]
    public ?string $status = null;

    #[Assert\DateTime]
    public ?string $dueAt = null;

    #[Assert\NotBlank(groups: ['put'])]
    #[Assert\Uuid]
    public ?string $companyId = null;

    public static function fromPostArray(array $payload): self
    {
        $dto = new self();
        $dto->label = isset($payload['label']) ? (string) $payload['label'] : null;
        $dto->amount = $payload['amount'] ?? null;
        $dto->currency = isset($payload['currency']) ? (string) $payload['currency'] : null;
        $dto->status = isset($payload['status']) ? (string) $payload['status'] : null;
        $dto->dueAt = isset($payload['dueAt']) ? (string) $payload['dueAt'] : null;
        $dto->companyId = isset($payload['companyId']) ? (string) $payload['companyId'] : null;

        return $dto;
    }

    public static function fromPutArray(array $payload): self
    {
        return self::fromPostArray($payload);
    }
}
