<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Response;

final readonly class EntityIdResponseDto
{
    /** @param array<string, scalar|null> $extra */
    public function __construct(
        public string $id,
        public array $extra = [],
    ) {
    }

    /** @return array<string, scalar|null> */
    public function toArray(): array
    {
        return ['id' => $this->id, ...$this->extra];
    }
}
