<?php

declare(strict_types=1);

namespace App\Crm\Transport\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateContactRequest
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

    public ?int $score = null;

    public ?string $companyId = null;

    public static function fromArray(array $payload): self
    {
        $r = new self();
        $r->firstName = isset($payload['firstName']) ? (string)$payload['firstName'] : null;
        $r->lastName = isset($payload['lastName']) ? (string)$payload['lastName'] : null;
        $r->email = isset($payload['email']) ? (string)$payload['email'] : null;
        $r->phone = isset($payload['phone']) ? (string)$payload['phone'] : null;
        $r->jobTitle = isset($payload['jobTitle']) ? (string)$payload['jobTitle'] : null;
        $r->city = isset($payload['city']) ? (string)$payload['city'] : null;
        $r->score = isset($payload['score']) ? (int)$payload['score'] : null;
        $r->companyId = isset($payload['companyId']) ? (string)$payload['companyId'] : null;

        return $r;
    }
}
