<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateBillingCommandDto
{
    #[Assert\Length(max: 255)]
    public ?string $label = null;

    #[Assert\Type(type: 'numeric')]
    public null|int|float|string $amount = null;

    #[Assert\Length(min: 3, max: 3)]
    public ?string $currency = null;

    #[Assert\Length(max: 30)]
    public ?string $status = null;

    #[Assert\DateTime]
    public ?string $dueAt = null;

    #[Assert\DateTime]
    public ?string $paidAt = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public ?string $companyId = null;

    public bool $hasLabel = false;
    public bool $hasAmount = false;
    public bool $hasCurrency = false;
    public bool $hasStatus = false;
    public bool $hasDueAt = false;
    public bool $hasPaidAt = false;
    public bool $hasCompanyId = false;

    public static function fromPatchArray(array $payload): self
    {
        $dto = new self();
        foreach (['label', 'amount', 'currency', 'status', 'dueAt', 'paidAt', 'companyId'] as $field) {
            $flag = 'has' . ucfirst($field);
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $dto->{$flag} = true;
            $value = $payload[$field];
            if ($field === 'amount') {
                $dto->amount = $value;

                continue;
            }
            $dto->{$field} = $value !== null ? (string)$value : null;
        }

        return $dto;
    }
}
