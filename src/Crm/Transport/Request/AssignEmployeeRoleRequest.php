<?php

declare(strict_types=1);

namespace App\Crm\Transport\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AssignEmployeeRoleRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public ?string $role = null;

    public static function fromArray(array $payload): self
    {
        $r = new self();
        $r->role = isset($payload['role']) ? (string)$payload['role'] : null;

        return $r;
    }
}
