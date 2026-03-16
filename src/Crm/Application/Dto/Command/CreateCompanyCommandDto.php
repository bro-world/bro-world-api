<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateCompanyCommandDto
{
    #[Assert\NotBlank]
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

    public static function fromPostArray(array $payload): self
    {
        $dto = new self();
        $dto->name = isset($payload['name']) ? (string)$payload['name'] : null;
        $dto->industry = isset($payload['industry']) ? (string)$payload['industry'] : null;
        $dto->website = isset($payload['website']) ? (string)$payload['website'] : null;
        $dto->contactEmail = isset($payload['contactEmail']) ? (string)$payload['contactEmail'] : null;
        $dto->phone = isset($payload['phone']) ? (string)$payload['phone'] : null;

        return $dto;
    }
}
