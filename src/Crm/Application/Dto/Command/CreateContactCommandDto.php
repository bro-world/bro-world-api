<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Command;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateContactCommandDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
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

    public static function fromPostArray(array $payload): self
    {
        $dto = new self();
        $dto->firstName = isset($payload['firstName']) ? (string)$payload['firstName'] : null;
        $dto->lastName = isset($payload['lastName']) ? (string)$payload['lastName'] : null;
        $dto->email = isset($payload['email']) ? (string)$payload['email'] : null;
        $dto->phone = isset($payload['phone']) ? (string)$payload['phone'] : null;
        $dto->jobTitle = isset($payload['jobTitle']) ? (string)$payload['jobTitle'] : null;
        $dto->city = isset($payload['city']) ? (string)$payload['city'] : null;
        $dto->score = isset($payload['score']) ? (int)$payload['score'] : null;
        $dto->companyId = isset($payload['companyId']) ? (string)$payload['companyId'] : null;

        return $dto;
    }

    public static function fromPutArray(array $payload): self
    {
        return self::fromPostArray($payload);
    }
}
