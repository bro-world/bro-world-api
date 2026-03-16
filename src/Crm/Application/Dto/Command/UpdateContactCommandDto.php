<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateContactCommandDto
{
    #[Assert\Length(max: 120)]
    public ?string $firstName = null;

    #[Assert\Length(max: 120)]
    public ?string $lastName = null;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 60)]
    public ?string $phone = null;

    #[Assert\Length(max: 120)]
    public ?string $jobTitle = null;

    #[Assert\Length(max: 120)]
    public ?string $city = null;

    #[Assert\Type(type: 'integer')]
    public ?int $score = null;

    #[Assert\Uuid]
    public ?string $companyId = null;

    public bool $hasFirstName = false;
    public bool $hasLastName = false;
    public bool $hasEmail = false;
    public bool $hasPhone = false;
    public bool $hasJobTitle = false;
    public bool $hasCity = false;
    public bool $hasScore = false;
    public bool $hasCompanyId = false;

    public static function fromPatchArray(array $payload): self
    {
        $dto = new self();

        foreach (['firstName', 'lastName', 'email', 'phone', 'jobTitle', 'city', 'score', 'companyId'] as $field) {
            $flag = 'has' . ucfirst($field);
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $dto->{$flag} = true;
            $value = $payload[$field];
            $dto->{$field} = $value !== null ? (is_int($value) ? $value : (string) $value) : null;
        }

        if ($dto->hasScore && $payload['score'] !== null) {
            $dto->score = (int) $payload['score'];
        }

        return $dto;
    }
}
