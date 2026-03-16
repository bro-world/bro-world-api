<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateCompanyCommandDto
{
    #[Assert\NotBlank(groups: ['put'])]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 255)]
    public ?string $industry = null;

    #[Assert\Length(max: 255)]
    public ?string $website = null;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $contactEmail = null;

    #[Assert\Length(max: 64)]
    public ?string $phone = null;

    public bool $hasName = false;
    public bool $hasIndustry = false;
    public bool $hasWebsite = false;
    public bool $hasContactEmail = false;
    public bool $hasPhone = false;

    public static function fromPutArray(array $payload): self
    {
        $dto = new self();
        $dto->hasName = true;
        $dto->name = isset($payload['name']) ? (string)$payload['name'] : null;
        $dto->hasIndustry = true;
        $dto->industry = isset($payload['industry']) ? (string)$payload['industry'] : null;
        $dto->hasWebsite = true;
        $dto->website = isset($payload['website']) ? (string)$payload['website'] : null;
        $dto->hasContactEmail = true;
        $dto->contactEmail = isset($payload['contactEmail']) ? (string)$payload['contactEmail'] : null;
        $dto->hasPhone = true;
        $dto->phone = isset($payload['phone']) ? (string)$payload['phone'] : null;

        return $dto;
    }

    public static function fromPatchArray(array $payload): self
    {
        $dto = new self();
        if (array_key_exists('name', $payload)) {
            $dto->hasName = true;
            $dto->name = $payload['name'] !== null ? (string)$payload['name'] : null;
        }
        if (array_key_exists('industry', $payload)) {
            $dto->hasIndustry = true;
            $dto->industry = $payload['industry'] !== null ? (string)$payload['industry'] : null;
        }
        if (array_key_exists('website', $payload)) {
            $dto->hasWebsite = true;
            $dto->website = $payload['website'] !== null ? (string)$payload['website'] : null;
        }
        if (array_key_exists('contactEmail', $payload)) {
            $dto->hasContactEmail = true;
            $dto->contactEmail = $payload['contactEmail'] !== null ? (string)$payload['contactEmail'] : null;
        }
        if (array_key_exists('phone', $payload)) {
            $dto->hasPhone = true;
            $dto->phone = $payload['phone'] !== null ? (string)$payload['phone'] : null;
        }

        return $dto;
    }
}
